/**
 * Kreiskarte Generator
 *
 * 1. Search for a Landkreis via Nominatim
 * 2. Fetch Gemeinde boundaries via Overpass API
 * 3. Convert geo coordinates to SVG polygons
 * 4. Preview the map and save to theme
 */
(function () {
    'use strict';

    const NOMINATIM = 'https://nominatim.openstreetmap.org/search';
    const OVERPASS  = 'https://overpass-api.de/api/interpreter';
    const SVG_WIDTH = 400;
    const SVG_PADDING = 10;

    let generatedData = null;
    let existingOVs = [];   // fetched from WP
    let ovMappings = {};    // gemeinde slug → { type, ovSlug }

    // Municipality types for the mapping UI
    const MUNI_TYPES = {
        ov:         { label: 'Ortsverband',  desc: 'Vollständiger Ortsverband mit eigener Seite' },
        ortsgruppe: { label: 'Ortsgruppe',   desc: 'Aktive Mitglieder vor Ort, kein eigener Verband' },
        werbung:    { label: 'Werbeseite',   desc: 'Einladung zum Mitmachen und Gründen' },
        keine:      { label: 'Keine',        desc: 'Kein Eintrag, nur auf der Karte sichtbar' },
    };

    /**
     * Fetch from Overpass API with retry and error handling.
     * The API sometimes returns XML error pages (rate limit, timeout).
     */
    async function fetchOverpass(query, retries = 2) {
        for (let attempt = 0; attempt <= retries; attempt++) {
            if (attempt > 0) {
                const wait = attempt * 5000;
                console.log(`Overpass: retry ${attempt} after ${wait}ms...`);
                await new Promise(r => setTimeout(r, wait));
            }

            const resp = await fetch(OVERPASS, {
                method: 'POST',
                body: 'data=' + encodeURIComponent(query),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            });

            if (!resp.ok) {
                const text = await resp.text();
                if (resp.status === 429 || resp.status === 504) {
                    console.warn(`Overpass ${resp.status}, retrying...`);
                    continue;
                }
                throw new Error(`Overpass API Fehler ${resp.status}: ${text.slice(0, 200)}`);
            }

            const contentType = resp.headers.get('content-type') || '';
            if (!contentType.includes('json')) {
                const text = await resp.text();
                if (attempt < retries) {
                    console.warn('Overpass returned non-JSON, retrying...');
                    continue;
                }
                throw new Error('Overpass API hat kein JSON zurückgegeben. Bitte in 30 Sekunden erneut versuchen.');
            }

            return await resp.json();
        }
        throw new Error('Overpass API nicht erreichbar nach mehreren Versuchen. Bitte später erneut versuchen.');
    }

    document.addEventListener('DOMContentLoaded', init);

    function init() {
        document.getElementById('gk-search-btn').addEventListener('click', doSearch);
        document.getElementById('gk-search-input').addEventListener('keydown', e => {
            if (e.key === 'Enter') { e.preventDefault(); doSearch(); }
        });
        document.getElementById('gk-save-btn').addEventListener('click', doSave);
    }

    // ── Step 1: Search for Landkreis ────────────────────────────────────────

    async function doSearch() {
        const q = document.getElementById('gk-search-input').value.trim();
        if (!q) return;

        const results = document.getElementById('gk-search-results');
        results.innerHTML = '<p>Suche...</p>';

        try {
            const url = `${NOMINATIM}?q=${encodeURIComponent(q)}&format=json&countrycodes=de&limit=8&featuretype=settlement&accept-language=de`;
            const resp = await fetch(url, {
                headers: { 'User-Agent': 'GrueneKreisverbandTheme/1.0' }
            });
            const data = await resp.json();

            // Filter for admin boundaries (Landkreis = admin_level 6, kreisfreie Stadt = 6)
            const kreise = data.filter(r =>
                r.osm_type === 'relation' &&
                r.type && (r.type === 'administrative' || r.type === 'boundary')
            );

            if (kreise.length === 0) {
                // Show all results as fallback
                renderSearchResults(data);
            } else {
                renderSearchResults(kreise);
            }
        } catch (err) {
            results.innerHTML = `<p class="notice notice-error">Fehler: ${err.message}</p>`;
        }
    }

    function renderSearchResults(items) {
        const el = document.getElementById('gk-search-results');
        if (items.length === 0) {
            el.innerHTML = '<p>Keine Ergebnisse. Versuche z.B. "Landkreis Starnberg".</p>';
            return;
        }

        el.innerHTML = '<ul class="gk-result-list">' +
            items.map(r => `
                <li>
                    <button class="button gk-select-kreis"
                            data-osm-id="${r.osm_id}"
                            data-osm-type="${r.osm_type}"
                            data-name="${escHtml(r.display_name)}">
                        ${escHtml(r.display_name)}
                    </button>
                </li>
            `).join('') +
            '</ul>';

        el.querySelectorAll('.gk-select-kreis').forEach(btn => {
            btn.addEventListener('click', () => {
                selectKreis(btn.dataset.osmId, btn.dataset.osmType, btn.dataset.name);
            });
        });
    }

    // ── Step 2: Fetch Gemeinde boundaries ───────────────────────────────────

    async function selectKreis(osmId, osmType, displayName) {
        document.getElementById('gk-step-search').style.display = 'none';
        document.getElementById('gk-step-loading').style.display = 'block';

        const statusEl = document.getElementById('gk-loading-status');
        const progressBar = document.querySelector('.gk-progress-bar');

        try {
            statusEl.textContent = 'Lade Gemeindegrenzen und Kreisgrenzen...';
            progressBar.style.width = '20%';

            // Single Overpass query: Landkreis boundary + all Gemeinden inside
            const mainQuery = `
                [out:json][timeout:90];
                rel(${osmId});
                out body;
                >;
                out skel qt;
                rel(${osmId});
                map_to_area -> .kreis;
                (
                    rel(area.kreis)["boundary"="administrative"]["admin_level"="8"];
                );
                out body;
                >;
                out skel qt;
            `;

            const mainResp = await fetchOverpass(mainQuery);

            progressBar.style.width = '80%';
            statusEl.textContent = 'Generiere SVG-Karte...';

            // All data is in one response — nodes/ways are shared
            const result = processOverpassData(mainResp, parseInt(osmId), displayName);

            progressBar.style.width = '100%';

            if (result.municipalities && Object.keys(result.municipalities).length > 0) {
                generatedData = result;
                showPreview(result);
            } else {
                statusEl.textContent = 'Keine Gemeinden gefunden. Anderes Ergebnis wählen?';
                document.getElementById('gk-step-search').style.display = 'block';
            }
        } catch (err) {
            statusEl.textContent = `Fehler: ${err.message}`;
            console.error(err);
            document.getElementById('gk-step-search').style.display = 'block';
        }
    }

    // ── Process Overpass data → SVG ─────────────────────────────────────────

    function processOverpassData(data, kreisOsmId, displayName) {
        // Build node lookup
        const nodes = {};
        data.elements
            .filter(e => e.type === 'node')
            .forEach(n => { nodes[n.id] = [n.lat, n.lon]; });

        // Build way lookup
        const ways = {};
        data.elements
            .filter(e => e.type === 'way')
            .forEach(w => {
                ways[w.id] = (w.nodes || []).map(nid => nodes[nid]).filter(Boolean);
            });

        // Separate admin_level=8 relations into real Gemeinden vs gemeindefreie Gebiete.
        // Gemeindefreie Gebiete (lakes, forests) are unincorporated areas that appear
        // as admin_level=8 but should NOT become Ortsverbände.
        // Detection uses the official "Amtlicher Gemeindeschlüssel" (AGS):
        // real Gemeinden have last 3 digits 001–400, gemeindefreie Gebiete 401–999.
        // (e.g. Starnberger See = 09188451, Starnberg = 09188139)
        // Also checks the OSM tag "name:prefix" = "Gemeindefreies Gebiet".
        // Their polygons are already perfectly clipped to the Kreis boundary and
        // fill exactly the gaps between Gemeinde polygons — no separate water query needed.
        const allLevel8 = data.elements
            .filter(e => e.type === 'relation' && e.tags && e.tags.name
                && e.tags['admin_level'] === '8');

        const waterRels = [];
        const gemeindeRels = [];

        for (const rel of allLevel8) {
            const tags = rel.tags;
            const ags = tags['de:amtlicher_gemeindeschluessel'] || '';
            const agsLast3 = ags.length >= 3 ? parseInt(ags.slice(-3), 10) : 0;
            const isGemeindefrei = agsLast3 >= 401 ||
                tags.natural === 'water' ||
                (tags['name:prefix'] || '').toLowerCase().includes('gemeindefrei');

            if (isGemeindefrei) {
                waterRels.push(rel);
            } else {
                gemeindeRels.push(rel);
            }
        }

        const gemeinden = gemeindeRels
            .map(rel => {
                const coords = extractRelationCoords(rel, ways);
                return {
                    name: rel.tags.name,
                    coords: coords,
                };
            })
            .filter(g => g.coords.length > 2);

        // Extract Kreis boundary (the relation matching the searched Landkreis)
        const kreisRel = data.elements.find(e => e.type === 'relation' && e.id === kreisOsmId);
        let kreisCoords = [];
        if (kreisRel) {
            kreisCoords = extractRelationCoords(kreisRel, ways);
        }

        if (gemeinden.length === 0) {
            return { municipalities: {} };
        }

        // Calculate bounding box of ALL coordinates
        let minLat = Infinity, maxLat = -Infinity, minLon = Infinity, maxLon = -Infinity;
        const allCoords = [...gemeinden.flatMap(g => g.coords), ...kreisCoords];
        for (const [lat, lon] of allCoords) {
            if (lat < minLat) minLat = lat;
            if (lat > maxLat) maxLat = lat;
            if (lon < minLon) minLon = lon;
            if (lon > maxLon) maxLon = lon;
        }

        // Projection: lat/lon → SVG coordinates
        const latRange = maxLat - minLat;
        const lonRange = maxLon - minLon;
        // Correct for latitude (rough Mercator at ~48°N)
        const latCos = Math.cos((minLat + maxLat) / 2 * Math.PI / 180);
        const correctedLonRange = lonRange * latCos;
        const aspect = latRange / correctedLonRange;
        const svgW = SVG_WIDTH;
        const svgH = svgW * aspect;
        const usableW = svgW - 2 * SVG_PADDING;
        const usableH = svgH - 2 * SVG_PADDING;

        function project([lat, lon]) {
            const x = SVG_PADDING + ((lon - minLon) / lonRange) * usableW;
            const y = SVG_PADDING + ((maxLat - lat) / latRange) * usableH; // flip Y
            return [Math.round(x * 1000) / 1000, Math.round(y * 1000) / 1000];
        }

        // Build municipality data
        const municipalities = {};
        for (const g of gemeinden) {
            const slug = 'ov-' + slugify(g.name);
            const projected = g.coords.map(project);
            const simplified = simplifyPolygon(projected, 0.5);

            if (simplified.length < 3) continue;

            const centroid = polygonCentroid(simplified);
            const pointsStr = simplified.map(p => p.join(' ')).join(' ');

            municipalities[slug] = {
                name: g.name,
                polygon: pointsStr,
                arrow: `translate(${centroid[0] - 5} ${centroid[1] - 5}) scale(.287)`,
                label: {
                    text: g.name,
                    transform: `translate(${centroid[0] - 15} ${centroid[1] - 15})`
                }
            };
        }

        // Build district outline
        const district = {};
        if (kreisCoords.length > 2) {
            const projKreis = kreisCoords.map(project);
            const simpKreis = simplifyPolygon(projKreis, 0.8);
            const kreisPath = polygonToSvgPath(simpKreis);
            district.shadow = kreisPath;
            district.fill = kreisPath;
        }

        // Build water body paths from gemeindefreie Gebiete.
        // These polygons already represent the exact portion of lakes/forests
        // within the Kreis that is NOT part of any Gemeinde — no clipping needed.
        const water = {};
        for (const rel of waterRels) {
            const coords = extractRelationCoords(rel, ways);
            if (coords.length < 3) continue;

            const waterSlug = slugify(rel.tags.name);
            const projWater = coords.map(project);
            const simpWater = simplifyPolygon(projWater, 0.4);
            if (simpWater.length < 3) continue;

            water[waterSlug] = polygonToSvgPath(simpWater);
        }

        const viewBox = `0 0 ${Math.round(svgW)} ${Math.round(svgH)}`;

        return {
            _meta: {
                title: displayName.split(',')[0],
                description: `Generiert aus OpenStreetMap-Daten`,
                viewBox: viewBox,
                source: 'openstreetmap',
                generated: new Date().toISOString().split('T')[0],
            },
            municipalities: municipalities,
            district: district,
            water: water,
        };
    }

    /**
     * Extract outer ring coordinates from an OSM relation.
     */
    function extractRelationCoords(rel, ways) {
        const outerWayIds = (rel.members || [])
            .filter(m => m.type === 'way' && (m.role === 'outer' || m.role === ''))
            .map(m => m.ref);

        // Collect all outer way segments
        const segments = outerWayIds
            .map(id => ways[id])
            .filter(Boolean);

        if (segments.length === 0) return [];

        // Try to join segments into a ring
        return joinSegments(segments);
    }

    /**
     * Join way segments into a continuous ring.
     */
    function joinSegments(segments) {
        if (segments.length === 0) return [];
        if (segments.length === 1) return segments[0];

        const result = [...segments[0]];
        const used = new Set([0]);

        for (let iter = 0; iter < segments.length * 2; iter++) {
            const lastPt = result[result.length - 1];
            let found = false;

            for (let i = 0; i < segments.length; i++) {
                if (used.has(i)) continue;
                const seg = segments[i];
                if (!seg || seg.length === 0) continue;

                const first = seg[0];
                const last = seg[seg.length - 1];

                if (coordsClose(lastPt, first)) {
                    result.push(...seg.slice(1));
                    used.add(i);
                    found = true;
                    break;
                } else if (coordsClose(lastPt, last)) {
                    result.push(...[...seg].reverse().slice(1));
                    used.add(i);
                    found = true;
                    break;
                }
            }

            if (!found) break;
            if (used.size === segments.length) break;
        }

        return result;
    }

    function coordsClose(a, b) {
        if (!a || !b) return false;
        return Math.abs(a[0] - b[0]) < 0.0001 && Math.abs(a[1] - b[1]) < 0.0001;
    }

    // ── Geometry helpers ────────────────────────────────────────────────────

    /**
     * Ramer-Douglas-Peucker polygon simplification.
     */
    function simplifyPolygon(points, epsilon) {
        if (points.length < 3) return points;

        let maxDist = 0;
        let maxIdx = 0;
        const first = points[0];
        const last = points[points.length - 1];

        for (let i = 1; i < points.length - 1; i++) {
            const d = perpDist(points[i], first, last);
            if (d > maxDist) {
                maxDist = d;
                maxIdx = i;
            }
        }

        if (maxDist > epsilon) {
            const left = simplifyPolygon(points.slice(0, maxIdx + 1), epsilon);
            const right = simplifyPolygon(points.slice(maxIdx), epsilon);
            return [...left.slice(0, -1), ...right];
        }

        return [first, last];
    }

    function perpDist(pt, lineStart, lineEnd) {
        const dx = lineEnd[0] - lineStart[0];
        const dy = lineEnd[1] - lineStart[1];
        const len = Math.sqrt(dx * dx + dy * dy);
        if (len === 0) return Math.sqrt((pt[0] - lineStart[0]) ** 2 + (pt[1] - lineStart[1]) ** 2);
        return Math.abs(dy * pt[0] - dx * pt[1] + lineEnd[0] * lineStart[1] - lineEnd[1] * lineStart[0]) / len;
    }

    function polygonCentroid(pts) {
        let cx = 0, cy = 0;
        for (const [x, y] of pts) { cx += x; cy += y; }
        return [Math.round(cx / pts.length * 100) / 100, Math.round(cy / pts.length * 100) / 100];
    }

    function polygonToSvgPath(pts) {
        if (pts.length === 0) return '';
        return 'M' + pts.map(p => p.join(',')).join('L') + 'Z';
    }

    function slugify(str) {
        return str
            .toLowerCase()
            .replace(/ä/g, 'ae').replace(/ö/g, 'oe').replace(/ü/g, 'ue').replace(/ß/g, 'ss')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-|-$/g, '');
    }

    function escHtml(s) {
        const el = document.createElement('span');
        el.textContent = s;
        return el.innerHTML;
    }

    // ── Step 3: Preview + OV Mapping ──────────────────────────────────────

    async function showPreview(data) {
        document.getElementById('gk-step-loading').style.display = 'none';
        document.getElementById('gk-step-preview').style.display = 'block';

        const munis = Object.values(data.municipalities);
        const waterCount = data.water ? Object.keys(data.water).length : 0;
        const waterNames = data.water ? Object.keys(data.water).map(s =>
            s.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
        ) : [];
        document.getElementById('gk-preview-info').innerHTML = `
            <p><strong>${escHtml(data._meta.title)}</strong> — ${munis.length} Gemeinden gefunden</p>
            ${waterCount > 0 ? `<p>${waterCount} Gewässer: ${waterNames.map(n => escHtml(n)).join(', ')}</p>` : ''}
        `;

        // Render SVG preview
        const mapEl = document.getElementById('gk-preview-map');
        mapEl.innerHTML = renderSvgPreview(data);

        // Fetch existing Ortsverbände and build mapping UI
        await loadExistingOVs();
        renderMappingTable(data);
    }

    /**
     * Fetch all existing Ortsverband zuordnung terms from WordPress.
     */
    async function loadExistingOVs() {
        try {
            const resp = await fetch(gkKreiskarte.ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'gk_get_ortsverbaende',
                    nonce: gkKreiskarte.nonce,
                }),
            });
            const result = await resp.json();
            if (result.success) {
                existingOVs = result.data;
            }
        } catch (e) {
            console.warn('Could not load existing OVs:', e);
            existingOVs = [];
        }
    }

    /**
     * Render the mapping table: each Gemeinde gets a type selector
     * and (for OV type) a dropdown to pick an existing Ortsverband.
     */
    function renderMappingTable(data) {
        const el = document.getElementById('gk-mapping-table');
        const entries = Object.entries(data.municipalities);

        // Reset mappings
        ovMappings = {};

        // Build the type <option> list once
        const typeOptions = Object.entries(MUNI_TYPES)
            .map(([val, t]) => `<option value="${val}">${escHtml(t.label)}</option>`)
            .join('');

        // Build the existing OV <option> list once
        const ovOptions = existingOVs.map(ov => {
            const status = ov.status !== 'publish' ? ` (${ov.status})` : '';
            return `<option value="${escHtml(ov.slug)}">${escHtml(ov.title)}${status}</option>`;
        }).join('');

        let html = '<table class="widefat gk-mapping-table"><thead><tr>' +
            '<th>Gemeinde</th>' +
            '<th>Typ</th>' +
            '<th>Zuordnung</th>' +
            '<th></th>' +
            '</tr></thead><tbody>';

        for (const [genSlug, muni] of entries) {
            // Try to auto-match to an existing OV
            const autoMatch = findBestMatch(muni.name, genSlug);

            // Default: if auto-matched → type "ov" + linked, otherwise → type "ov" + new
            ovMappings[genSlug] = {
                type: 'ov',
                ovSlug: autoMatch ? autoMatch.slug : '',
            };

            const badge = autoMatch
                ? '<span class="gk-match-auto">automatisch</span>'
                : '<span class="gk-match-new">neu erstellen</span>';

            html += `<tr data-gen-slug="${escHtml(genSlug)}">` +
                `<td><strong>${escHtml(muni.name)}</strong></td>` +
                `<td><select class="gk-type-select">${typeOptions}</select></td>` +
                `<td><select class="gk-ov-select">` +
                    `<option value="">— Neu erstellen —</option>` +
                    ovOptions +
                `</select></td>` +
                `<td class="gk-badge-cell">${badge}</td>` +
                `</tr>`;
        }

        html += '</tbody></table>';
        el.innerHTML = html;

        // Set auto-matched values and bind events
        el.querySelectorAll('tr[data-gen-slug]').forEach(row => {
            const genSlug = row.dataset.genSlug;
            const typeSelect = row.querySelector('.gk-type-select');
            const ovSelect = row.querySelector('.gk-ov-select');
            const mapping = ovMappings[genSlug];

            // Pre-select auto-matched OV
            if (mapping.ovSlug) {
                ovSelect.value = mapping.ovSlug;
            }

            typeSelect.addEventListener('change', () => {
                mapping.type = typeSelect.value;
                updateRowState(row);
            });

            ovSelect.addEventListener('change', () => {
                mapping.ovSlug = ovSelect.value;
                updateRowState(row);
            });

            // Initial state
            updateRowState(row);
        });
    }

    /**
     * Update a mapping row's visual state based on current selections.
     */
    function updateRowState(row) {
        const genSlug = row.dataset.genSlug;
        const mapping = ovMappings[genSlug];
        const ovSelect = row.querySelector('.gk-ov-select');
        const badge = row.querySelector('.gk-badge-cell');

        // Show OV dropdown only for types that link to a post
        const needsOv = mapping.type === 'ov';
        ovSelect.style.display = needsOv ? '' : 'none';

        // Badge
        if (mapping.type === 'keine') {
            badge.innerHTML = '<span class="gk-match-none">nur Karte</span>';
        } else if (mapping.type === 'ov' && mapping.ovSlug) {
            badge.innerHTML = '<span class="gk-match-auto">zugeordnet</span>';
        } else {
            badge.innerHTML = '<span class="gk-match-new">neu erstellen</span>';
        }
    }

    /**
     * Try to find the best matching existing OV for a Gemeinde name.
     * Matches on: exact slug, slug contains name, title contains name, or vice versa.
     */
    function findBestMatch(gemeindeName, genSlug) {
        const nameLower = gemeindeName.toLowerCase();
        const nameSlug = slugify(gemeindeName);

        // 1. Exact slug match (ov-starnberg → ov-starnberg)
        let match = existingOVs.find(ov => ov.slug === genSlug);
        if (match) return match;

        // 2. Slug without ov- prefix (ov-starnberg → starnberg)
        const bareSlug = genSlug.replace(/^ov-/, '');
        match = existingOVs.find(ov => ov.slug === bareSlug);
        if (match) return match;

        // 3. Existing slug contains the bare name slug
        match = existingOVs.find(ov => ov.slug.includes(bareSlug) || bareSlug.includes(ov.slug));
        if (match) return match;

        // 4. Title match (case-insensitive, ignoring "Ortsverband " prefix)
        match = existingOVs.find(ov => {
            const ovName = ov.title.replace(/^Ortsverband\s+/i, '').toLowerCase();
            return ovName === nameLower || nameLower.includes(ovName) || ovName.includes(nameLower);
        });
        if (match) return match;

        return null;
    }

    function renderSvgPreview(data) {
        const viewBox = data._meta.viewBox;
        let svg = `<svg viewBox="${viewBox}" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:auto;">`;

        svg += `<style>
            .gk-preview polygon { fill:#fff; stroke:#e6007e; stroke-width:0.5; transition: fill .2s; cursor:pointer; }
            .gk-preview polygon:hover { fill:#e6007e; }
            .gk-preview .district { fill:#e6007e; opacity:.15; }
            .gk-preview-water path { fill:#3CB4E4; stroke:#0981B1; stroke-width:0.3; opacity:.7; }
            .gk-preview text { font-family:sans-serif; font-size:7px; pointer-events:none; fill:#333; }
        </style>`;

        // District outline
        if (data.district && data.district.fill) {
            svg += `<path class="district" d="${data.district.fill}"/>`;
        }

        // Municipality polygons
        svg += '<g class="gk-preview">';
        for (const [slug, muni] of Object.entries(data.municipalities)) {
            svg += `<polygon points="${muni.polygon}"><title>${escHtml(muni.name)}</title></polygon>`;
        }
        svg += '</g>';

        // Water bodies (blue)
        if (data.water && Object.keys(data.water).length > 0) {
            svg += '<g class="gk-preview-water">';
            for (const [id, path] of Object.entries(data.water)) {
                svg += `<path d="${path}"><title>${escHtml(id)}</title></path>`;
            }
            svg += '</g>';
        }

        // Labels
        for (const muni of Object.values(data.municipalities)) {
            svg += `<text transform="${muni.label.transform}"><tspan x="0" y="0">${escHtml(muni.label.text)}</tspan></text>`;
        }

        svg += '</svg>';
        return svg;
    }

    // ── Step 4: Save ────────────────────────────────────────────────────────

    async function doSave() {
        if (!generatedData) return;

        const statusEl = document.getElementById('gk-save-status');
        const btn = document.getElementById('gk-save-btn');
        btn.disabled = true;
        statusEl.innerHTML = '<p>Speichere Kreiskarte...</p>';

        try {
            // Apply mappings: set types and remap slugs
            const remappedData = applyMappings(generatedData);

            // Save kreiskarte.json
            const saveResp = await fetch(gkKreiskarte.ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'gk_save_kreiskarte',
                    nonce: gkKreiskarte.nonce,
                    kreiskarte_data: JSON.stringify(remappedData),
                }),
            });
            const saveResult = await saveResp.json();

            if (!saveResult.success) {
                throw new Error(saveResult.data || 'Speichern fehlgeschlagen');
            }

            // Summarise
            const counts = { ov: 0, ortsgruppe: 0, werbung: 0, keine: 0, mapped: 0 };
            for (const m of Object.values(ovMappings)) {
                counts[m.type]++;
                if (m.type === 'ov' && m.ovSlug) counts.mapped++;
            }
            statusEl.innerHTML = '<p>✓ Kreiskarte gespeichert.</p>';

            // Create posts for municipalities that need them (not "keine", not already mapped)
            if (document.getElementById('gk-create-ovs').checked) {
                const toCreate = Object.entries(remappedData.municipalities)
                    .filter(([slug, m]) => {
                        if (m.type === 'keine') return false;
                        // Already mapped to existing OV — skip
                        const orig = Object.entries(ovMappings)
                            .find(([gen, val]) => {
                                const finalSlug = (val.type === 'ov' && val.ovSlug) ? val.ovSlug : gen;
                                return finalSlug === slug;
                            });
                        if (orig && orig[1].type === 'ov' && orig[1].ovSlug) return false;
                        return true;
                    })
                    .map(([slug, m]) => ({ slug, name: m.name, type: m.type }));

                if (toCreate.length > 0) {
                    statusEl.innerHTML += '<p>Erstelle Einträge...</p>';

                    const ovResp = await fetch(gkKreiskarte.ajaxUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            action: 'gk_create_ortsverbaende',
                            nonce: gkKreiskarte.nonce,
                            municipalities: JSON.stringify(toCreate),
                        }),
                    });
                    const ovResult = await ovResp.json();

                    if (ovResult.success) {
                        const d = ovResult.data;
                        statusEl.innerHTML += `<p>✓ ${d.created} Einträge erstellt, ${d.skipped} bereits vorhanden.</p>`;
                        if (d.errors.length > 0) {
                            statusEl.innerHTML += `<p class="notice notice-warning">Fehler: ${d.errors.join(', ')}</p>`;
                        }
                    }
                } else {
                    statusEl.innerHTML += '<p>Alle Gemeinden bereits zugeordnet.</p>';
                }
            }

            const summary = [];
            if (counts.mapped) summary.push(`${counts.mapped} zugeordnet`);
            if (counts.ov - counts.mapped > 0) summary.push(`${counts.ov - counts.mapped} neue OVs`);
            if (counts.ortsgruppe) summary.push(`${counts.ortsgruppe} Ortsgruppen`);
            if (counts.werbung) summary.push(`${counts.werbung} Werbeseiten`);
            if (counts.keine) summary.push(`${counts.keine} ohne Eintrag`);

            statusEl.innerHTML += `<p><strong>Fertig!</strong> ${summary.join(', ')}. ` +
                'Verwende den Shortcode <code>[kreiskarte]</code> um die Karte einzubinden.</p>';

        } catch (err) {
            statusEl.innerHTML = `<p class="notice notice-error">Fehler: ${err.message}</p>`;
        }

        btn.disabled = false;
    }

    /**
     * Apply the user's mappings: set type on each municipality and
     * re-key to existing OV slug where mapped.
     */
    function applyMappings(data) {
        const remapped = JSON.parse(JSON.stringify(data)); // deep clone
        const newMunicipalities = {};

        for (const [genSlug, muniData] of Object.entries(remapped.municipalities)) {
            const mapping = ovMappings[genSlug] || { type: 'ov', ovSlug: '' };
            const finalSlug = (mapping.type === 'ov' && mapping.ovSlug) ? mapping.ovSlug : genSlug;

            muniData.type = mapping.type;
            newMunicipalities[finalSlug] = muniData;
        }

        remapped.municipalities = newMunicipalities;
        return remapped;
    }

})();
