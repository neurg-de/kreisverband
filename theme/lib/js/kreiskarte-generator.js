/**
 * Kreiskarte Admin Tool
 *
 * Two phases:
 *   Phase 1 — Load map: search → loading → preview → confirm & save
 *   Phase 2 — Configure: map + editable Gemeinde table (permanent workspace)
 */
(function () {
    'use strict';

    const NOMINATIM = 'https://nominatim.openstreetmap.org/search';
    const OVERPASS  = 'https://overpass-api.de/api/interpreter';
    const SVG_WIDTH = 400;
    const SVG_PADDING = 10;

    let pendingData = null;   // generated map data waiting to be saved
    let currentData = null;   // the saved kreiskarte data
    let existingOVs = [];
    let mappings = {};

    const MUNI_TYPES = {
        ov:         { label: 'Ortsverband',   desc: 'Seite + Zuordnungs-Term. Personen, Termine und Beiträge können zugeordnet werden. Grün auf der Karte.' },
        ortsgruppe: { label: 'Ortsgruppe',    desc: 'Wie Ortsverband, aber als Ortsgruppe benannt. Eigene Seite und Zuordnung. Hellgrün auf der Karte.' },
        werbung:    { label: 'Werbeseite',    desc: 'Nur Zuordnungs-Term, keine eigene Seite. Für Gemeinden ohne aktiven Verband. Neutral mit gestricheltem Rand.' },
        link:       { label: 'Externer Link', desc: 'Verlinkt auf eine beliebige URL. Keine WordPress-Inhalte. Weiß auf der Karte.' },
        keine:      { label: 'Nur Karte',     desc: 'Wird auf der Karte angezeigt, aber nicht verlinkt. Keine WordPress-Inhalte. Grau und ausgegraut.' },
    };


    // ── Init ────────────────────────────────────────────────────────────────

    document.addEventListener('DOMContentLoaded', () => {
        console.log('[Kreiskarte] Init — existingMap=%s', !!window.gkExistingKreiskarte);

        // Phase 1 controls
        const searchBtn = document.getElementById('gk-search-btn');
        const searchInput = document.getElementById('gk-search-input');
        const confirmBtn = document.getElementById('gk-confirm-btn');
        const backBtn = document.getElementById('gk-back-btn');

        searchBtn.addEventListener('click', () => doSearch('gk-search-results'));
        searchInput.addEventListener('keydown', e => {
            if (e.key === 'Enter') { e.preventDefault(); doSearch('gk-search-results'); }
        });
        confirmBtn.addEventListener('click', doConfirm);
        backBtn.addEventListener('click', backToSearch);

        // Phase 2 controls
        document.getElementById('gk-save-btn').addEventListener('click', doSaveMappings);
        document.getElementById('gk-create-btn').addEventListener('click', doCreateSelected);

        // Reload controls (in Phase 2)
        const reloadBtn = document.getElementById('gk-reload-btn');
        const reloadInput = document.getElementById('gk-reload-input');
        if (reloadBtn) {
            reloadBtn.addEventListener('click', () => doSearch('gk-reload-results', true));
            reloadInput.addEventListener('keydown', e => {
                if (e.key === 'Enter') { e.preventDefault(); doSearch('gk-reload-results', true); }
            });
        }

        // If we already have a saved map, go straight to Phase 2
        if (window.gkExistingKreiskarte) {
            currentData = window.gkExistingKreiskarte;
            showConfigPhase(currentData);
        }
    });


    // ════════════════════════════════════════════════════════════════════════
    //  PHASE 1 — Load Map
    // ════════════════════════════════════════════════════════════════════════

    function show(id) { document.getElementById(id).style.display = ''; }
    function hide(id) { document.getElementById(id).style.display = 'none'; }

    async function doSearch(resultsId, isReload = false) {
        const inputId = isReload ? 'gk-reload-input' : 'gk-search-input';
        const q = document.getElementById(inputId).value.trim();
        if (!q) {
            console.log('[Kreiskarte] Suche abgebrochen — Eingabefeld ist leer');
            return;
        }

        const resultsEl = document.getElementById(resultsId);
        resultsEl.innerHTML = '<p>Suche...</p>';
        console.log('[Kreiskarte] Nominatim-Suche: "%s"', q);

        try {
            const url = `${NOMINATIM}?q=${encodeURIComponent(q)}&format=json&countrycodes=de&limit=8&featuretype=settlement&accept-language=de`;
            const resp = await fetch(url, {
                headers: { 'User-Agent': 'GrueneKreisverbandTheme/1.0' }
            });
            if (!resp.ok) throw new Error(`Nominatim HTTP ${resp.status}`);
            const data = await resp.json();
            console.log('[Kreiskarte] Nominatim: %d Treffer', data.length);

            const kreise = data.filter(r =>
                r.osm_type === 'relation' &&
                r.type && (r.type === 'administrative' || r.type === 'boundary')
            );
            console.log('[Kreiskarte] Davon Kreise/Relationen: %d', kreise.length);

            renderSearchResults(kreise.length > 0 ? kreise : data, resultsId, isReload);
        } catch (err) {
            console.error('[Kreiskarte] Suche fehlgeschlagen:', err);
            resultsEl.innerHTML = `<div class="notice notice-error inline"><p>Fehler bei der Suche: ${esc(err.message)}</p></div>`;
        }
    }

    function renderSearchResults(items, resultsId, isReload) {
        const el = document.getElementById(resultsId);
        if (items.length === 0) {
            el.innerHTML = '<p>Keine Ergebnisse gefunden.</p>';
            return;
        }

        el.innerHTML = '<ul class="gk-result-list">' +
            items.map(r => `
                <li>
                    <button type="button" class="button gk-select-kreis"
                            data-osm-id="${r.osm_id}"
                            data-name="${esc(r.display_name)}"
                            data-reload="${isReload ? '1' : '0'}">
                        ${esc(r.display_name)}
                    </button>
                </li>
            `).join('') +
            '</ul>';

        el.querySelectorAll('.gk-select-kreis').forEach(btn => {
            btn.addEventListener('click', () => {
                loadKreis(btn.dataset.osmId, btn.dataset.name, btn.dataset.reload === '1');
            });
        });
    }

    async function loadKreis(osmId, displayName, isReload) {
        console.log('[Kreiskarte] Lade Kreis: OSM-ID=%s, Name="%s", reload=%s', osmId, displayName, isReload);

        const loadingId = isReload ? 'gk-reload-loading' : 'gk-loading';
        const statusId = isReload ? 'gk-reload-status' : 'gk-loading-status';
        const progressSel = isReload ? '#gk-reload-loading .gk-progress-bar' : '.gk-progress-bar';

        if (!isReload) {
            hide('gk-search');
        }
        show(loadingId);

        const statusEl = document.getElementById(statusId);
        const progressBar = document.querySelector(progressSel);

        const setStatus = (text, pct) => {
            statusEl.textContent = text;
            if (pct !== undefined) progressBar.style.width = pct + '%';
            console.log('[Kreiskarte] %s (%d%%)', text, pct ?? '');
        };

        try {
            setStatus('Lade Gemeindegrenzen von Overpass API...', 10);

            const query = `
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

            const resp = await fetchOverpass(query, statusEl);
            setStatus('Verarbeite Geodaten...', 70);

            const result = processOverpassData(resp, parseInt(osmId), displayName);
            const muniCount = Object.keys(result.municipalities || {}).length;
            console.log('[Kreiskarte] Verarbeitung abgeschlossen: %d Gemeinden', muniCount);
            setStatus('Generiere SVG-Karte...', 90);

            if (muniCount === 0) {
                setStatus('Keine Gemeinden gefunden. Bitte ein anderes Ergebnis wählen.', 0);
                console.warn('[Kreiskarte] Keine Gemeinden in Overpass-Antwort');
                if (!isReload) show('gk-search');
                return;
            }

            progressBar.style.width = '100%';

            if (isReload) {
                setStatus('Speichere Karte...', 100);
                await saveKreiskarte(result);
                hide(loadingId);
                currentData = result;
                showConfigPhase(currentData);
                document.getElementById('gk-config-status').innerHTML =
                    `<div class="notice notice-success inline"><p>Karte neu geladen — ${muniCount} Gemeinden.</p></div>`;
                console.log('[Kreiskarte] Reload abgeschlossen');
            } else {
                pendingData = result;
                hide(loadingId);
                showPreview(result);
                console.log('[Kreiskarte] Vorschau angezeigt');
            }

        } catch (err) {
            console.error('[Kreiskarte] Laden fehlgeschlagen:', err);
            setStatus('', 0);
            statusEl.innerHTML = `<div class="notice notice-error inline"><p>Fehler: ${esc(err.message)}</p></div>`;
            if (!isReload) show('gk-search');
        }
    }

    function showPreview(data) {
        const munis = Object.keys(data.municipalities).length;
        const title = data._meta?.title || 'Landkreis';

        document.getElementById('gk-preview-map').innerHTML = renderSvgPreview(data);
        document.getElementById('gk-preview-info').innerHTML =
            `<p class="gk-preview-summary"><strong>${esc(title)}</strong> — ${munis} Gemeinden gefunden</p>`;

        show('gk-preview');
    }

    function backToSearch() {
        hide('gk-preview');
        show('gk-search');
        pendingData = null;
    }

    async function doConfirm() {
        if (!pendingData) return;

        const btn = document.getElementById('gk-confirm-btn');
        const statusEl = document.getElementById('gk-confirm-status');
        btn.disabled = true;
        btn.textContent = 'Speichere...';

        try {
            await saveKreiskarte(pendingData);
            currentData = pendingData;
            pendingData = null;

            // Transition to Phase 2
            hide('gk-phase-load');
            showConfigPhase(currentData);
        } catch (err) {
            statusEl.innerHTML = `<div class="notice notice-error inline"><p>Fehler beim Speichern: ${esc(err.message)}</p></div>`;
        }

        btn.disabled = false;
        btn.textContent = 'Karte übernehmen';
    }

    async function saveKreiskarte(data) {
        console.log('[Kreiskarte] Speichere Karte (%d Gemeinden)...', Object.keys(data.municipalities || {}).length);
        const resp = await fetch(gkKreiskarte.ajaxUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'gk_save_kreiskarte',
                nonce: gkKreiskarte.nonce,
                kreiskarte_data: JSON.stringify(data),
            }),
        });
        const result = await resp.json();
        if (!result.success) {
            throw new Error(result.data || 'Speichern fehlgeschlagen');
        }
        console.log('[Kreiskarte] Gespeichert (%d Bytes)', result.data?.bytes ?? 0);
        return result;
    }


    // ════════════════════════════════════════════════════════════════════════
    //  PHASE 2 — Configure Gemeinden
    // ════════════════════════════════════════════════════════════════════════

    async function showConfigPhase(data) {
        show('gk-phase-config');
        document.getElementById('gk-config-map').innerHTML = renderSvgPreview(data);

        await loadExistingOVs();
        renderConfigTable(data);
    }

    function renderConfigTable(data) {
        const el = document.getElementById('gk-config-table');
        const entries = Object.entries(data.municipalities);

        mappings = {};

        const typeOpts = Object.entries(MUNI_TYPES)
            .map(([val, t]) => `<option value="${val}">${esc(t.label)}</option>`)
            .join('');

        const ovOpts = existingOVs.map(ov =>
            `<option value="${esc(ov.slug)}">${esc(ov.title)}</option>`
        ).join('');

        let html = '<table class="widefat gk-mapping-table"><thead><tr>' +
            '<th>Gemeinde</th><th>Typ</th><th>Details</th><th class="gk-col-create">Anlegen</th><th></th>' +
            '</tr></thead><tbody>';

        for (const [slug, muni] of entries) {
            const type = muni.type || 'ov';
            const link = muni.link || '';
            const linkedOV = existingOVs.find(ov => ov.slug === slug);

            mappings[slug] = { type, ovSlug: linkedOV ? linkedOV.slug : '', link };

            html += `<tr data-slug="${esc(slug)}">` +
                `<td><strong>${esc(muni.name)}</strong></td>` +
                `<td>` +
                    `<select class="gk-type-select">${typeOpts}</select>` +
                    `<p class="gk-type-desc description"></p>` +
                `</td>` +
                `<td class="gk-details-cell">` +
                    `<select class="gk-ov-select"><option value="">— Neu erstellen —</option>${ovOpts}</select>` +
                    `<input type="url" class="gk-link-input" placeholder="https://example.com" value="${esc(link)}" />` +
                `</td>` +
                `<td class="gk-col-create"><input type="checkbox" class="gk-create-check" /></td>` +
                `<td class="gk-badge-cell"></td>` +
                '</tr>';
        }

        html += '</tbody></table>';
        el.innerHTML = html;

        // Bind events and set initial state
        el.querySelectorAll('tr[data-slug]').forEach(row => {
            const slug = row.dataset.slug;
            const m = mappings[slug];
            const typeSelect = row.querySelector('.gk-type-select');
            const ovSelect = row.querySelector('.gk-ov-select');
            const linkInput = row.querySelector('.gk-link-input');

            typeSelect.value = m.type;
            if (m.ovSlug) ovSelect.value = m.ovSlug;

            typeSelect.addEventListener('change', () => { m.type = typeSelect.value; syncRow(row, slug); });
            ovSelect.addEventListener('change', () => { m.ovSlug = ovSelect.value; syncRow(row, slug); });
            linkInput.addEventListener('input', () => { m.link = linkInput.value.trim(); syncRow(row, slug); });

            syncRow(row, slug);
        });
    }

    function syncRow(row, slug) {
        const m = mappings[slug];
        const ovSelect = row.querySelector('.gk-ov-select');
        const linkInput = row.querySelector('.gk-link-input');
        const createCheck = row.querySelector('.gk-create-check');
        const badge = row.querySelector('.gk-badge-cell');
        const descEl = row.querySelector('.gk-type-desc');

        const needsWP = m.type === 'ov' || m.type === 'ortsgruppe' || m.type === 'werbung';
        const exists = needsWP && (!!existingOVs.find(ov => ov.slug === slug) || !!m.ovSlug);

        // Type description
        descEl.textContent = MUNI_TYPES[m.type]?.desc || '';

        // Details column: OV dropdown for WP types, URL input for link type
        ovSelect.style.display = needsWP ? '' : 'none';
        linkInput.style.display = m.type === 'link' ? '' : 'none';

        // Create checkbox: only for WP types that don't already exist
        const canCreate = needsWP && !exists;
        createCheck.style.display = canCreate ? '' : 'none';
        if (!canCreate) createCheck.checked = false;

        // Badge
        if (m.type === 'keine') {
            badge.innerHTML = '<span class="gk-badge gk-badge--muted">nur Karte</span>';
        } else if (m.type === 'link') {
            badge.innerHTML = m.link
                ? '<span class="gk-badge gk-badge--ok">verlinkt</span>'
                : '<span class="gk-badge gk-badge--warn">URL fehlt</span>';
        } else if (exists) {
            badge.innerHTML = '<span class="gk-badge gk-badge--ok">zugeordnet</span>';
        } else {
            badge.innerHTML = '<span class="gk-badge gk-badge--warn">nicht angelegt</span>';
        }
    }

    async function doSaveMappings() {
        const btn = document.getElementById('gk-save-btn');
        const statusEl = document.getElementById('gk-config-status');
        btn.disabled = true;
        statusEl.innerHTML = '<p>Speichere...</p>';

        try {
            const resp = await fetch(gkKreiskarte.ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'gk_update_kreiskarte_mappings',
                    nonce: gkKreiskarte.nonce,
                    mappings: JSON.stringify(mappings),
                }),
            });
            const result = await resp.json();
            if (!result.success) throw new Error(result.data || 'Speichern fehlgeschlagen');

            statusEl.innerHTML = '<div class="notice notice-success inline"><p>Gespeichert.</p></div>';
        } catch (err) {
            statusEl.innerHTML = `<div class="notice notice-error inline"><p>${esc(err.message)}</p></div>`;
        }
        btn.disabled = false;
    }

    async function doCreateSelected() {
        if (!currentData) return;

        const btn = document.getElementById('gk-create-btn');
        const statusEl = document.getElementById('gk-config-status');

        // Collect checked rows
        const toCreate = [];
        document.querySelectorAll('#gk-config-table tr[data-slug]').forEach(row => {
            const check = row.querySelector('.gk-create-check');
            if (!check.checked || check.style.display === 'none') return;
            const slug = row.dataset.slug;
            toCreate.push({
                slug,
                name: currentData.municipalities[slug]?.name || slug,
                type: mappings[slug]?.type || 'ov',
            });
        });

        if (toCreate.length === 0) {
            statusEl.innerHTML = '<div class="notice notice-warning inline"><p>Keine Einträge markiert. Setze Häkchen in der Spalte "Anlegen".</p></div>';
            return;
        }

        btn.disabled = true;
        statusEl.innerHTML = `<p>Erstelle ${toCreate.length} Einträge...</p>`;

        try {
            // Save mappings first
            await fetch(gkKreiskarte.ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'gk_update_kreiskarte_mappings',
                    nonce: gkKreiskarte.nonce,
                    mappings: JSON.stringify(mappings),
                }),
            });

            const resp = await fetch(gkKreiskarte.ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'gk_create_ortsverbaende',
                    nonce: gkKreiskarte.nonce,
                    municipalities: JSON.stringify(toCreate),
                }),
            });
            const result = await resp.json();

            if (result.success) {
                const d = result.data;
                let msg = `${d.created} Einträge erstellt`;
                if (d.skipped) msg += `, ${d.skipped} bereits vorhanden`;
                statusEl.innerHTML = `<div class="notice notice-success inline"><p>${msg}.</p></div>`;

                // Refresh table to update badges
                await loadExistingOVs();
                renderConfigTable(currentData);
            } else {
                throw new Error(result.data || 'Erstellen fehlgeschlagen');
            }
        } catch (err) {
            statusEl.innerHTML = `<div class="notice notice-error inline"><p>${esc(err.message)}</p></div>`;
        }
        btn.disabled = false;
    }


    // ════════════════════════════════════════════════════════════════════════
    //  Shared Helpers
    // ════════════════════════════════════════════════════════════════════════

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
            if (result.success) existingOVs = result.data;
        } catch (e) {
            console.warn('Could not load existing OVs:', e);
            existingOVs = [];
        }
    }

    async function fetchOverpass(query, statusEl, retries = 2) {
        for (let attempt = 0; attempt <= retries; attempt++) {
            if (attempt > 0) {
                const wait = attempt * 5;
                console.log('[Kreiskarte] Overpass: Versuch %d/%d, warte %ds...', attempt + 1, retries + 1, wait);
                if (statusEl) statusEl.textContent = `Overpass API überlastet — neuer Versuch in ${wait}s...`;
                await new Promise(r => setTimeout(r, wait * 1000));
                if (statusEl) statusEl.textContent = `Lade Gemeindegrenzen (Versuch ${attempt + 1}/${retries + 1})...`;
            }

            console.log('[Kreiskarte] Overpass-Anfrage (Versuch %d/%d)...', attempt + 1, retries + 1);
            const resp = await fetch(OVERPASS, {
                method: 'POST',
                body: 'data=' + encodeURIComponent(query),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            });
            console.log('[Kreiskarte] Overpass-Antwort: HTTP %d', resp.status);

            if (!resp.ok) {
                if (resp.status === 429 || resp.status === 504) {
                    console.warn('[Kreiskarte] Overpass %d — Retry...', resp.status);
                    continue;
                }
                throw new Error(`Overpass API Fehler ${resp.status}`);
            }

            const ct = resp.headers.get('content-type') || '';
            if (!ct.includes('json')) {
                console.warn('[Kreiskarte] Overpass: kein JSON (%s)', ct);
                if (attempt < retries) continue;
                throw new Error('Overpass API hat kein JSON zurückgegeben. Bitte in 30s erneut versuchen.');
            }

            const data = await resp.json();
            console.log('[Kreiskarte] Overpass: %d Elemente empfangen', data.elements?.length ?? 0);
            return data;
        }
        throw new Error('Overpass API nicht erreichbar. Bitte später erneut versuchen.');
    }

    function renderSvgPreview(data) {
        const vb = data._meta?.viewBox || '0 0 400 400';
        let svg = `<svg viewBox="${vb}" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:auto;">`;
        svg += `<style>
            .gk-gen polygon { fill:#CCE7D7; stroke:#005538; stroke-width:0.5; transition:fill .2s; cursor:pointer; }
            .gk-gen polygon:hover { fill:#005538; }
            .gk-gen .district { fill:#005538; opacity:.12; }
            .gk-gen-water path { fill:#3CB4E4; stroke:#0981B1; stroke-width:0.3; opacity:.7; }
            .gk-gen text { font-family:sans-serif; font-size:7px; pointer-events:none; fill:#333; }
        </style>`;

        if (data.district?.fill) svg += `<path class="district" d="${data.district.fill}"/>`;

        svg += '<g class="gk-gen">';
        for (const muni of Object.values(data.municipalities)) {
            svg += `<polygon points="${muni.polygon}"><title>${esc(muni.name)}</title></polygon>`;
        }
        svg += '</g>';

        if (data.water) {
            svg += '<g class="gk-gen-water">';
            for (const [id, path] of Object.entries(data.water)) {
                svg += `<path d="${path}"><title>${esc(id)}</title></path>`;
            }
            svg += '</g>';
        }

        for (const muni of Object.values(data.municipalities)) {
            if (muni.label) {
                svg += `<text transform="${muni.label.transform}"><tspan x="0" y="0">${esc(muni.label.text)}</tspan></text>`;
            }
        }

        svg += '</svg>';
        return svg;
    }


    // ── Geo Processing ──────────────────────────────────────────────────────

    function processOverpassData(data, kreisOsmId, displayName) {
        const nodes = {};
        data.elements.filter(e => e.type === 'node').forEach(n => { nodes[n.id] = [n.lat, n.lon]; });

        const ways = {};
        data.elements.filter(e => e.type === 'way').forEach(w => {
            ways[w.id] = (w.nodes || []).map(nid => nodes[nid]).filter(Boolean);
        });

        const allLevel8 = data.elements.filter(e =>
            e.type === 'relation' && e.tags?.name && e.tags['admin_level'] === '8');

        const waterRels = [], gemeindeRels = [];
        for (const rel of allLevel8) {
            const tags = rel.tags;
            const ags = tags['de:amtlicher_gemeindeschluessel'] || '';
            const agsLast3 = ags.length >= 3 ? parseInt(ags.slice(-3), 10) : 0;
            const isFrei = agsLast3 >= 401 || tags.natural === 'water' ||
                (tags['name:prefix'] || '').toLowerCase().includes('gemeindefrei');
            (isFrei ? waterRels : gemeindeRels).push(rel);
        }

        const gemeinden = gemeindeRels
            .map(rel => ({ name: rel.tags.name, coords: extractRelCoords(rel, ways) }))
            .filter(g => g.coords.length > 2);

        const kreisRel = data.elements.find(e => e.type === 'relation' && e.id === kreisOsmId);
        const kreisCoords = kreisRel ? extractRelCoords(kreisRel, ways) : [];

        if (gemeinden.length === 0) return { municipalities: {} };

        // Bounding box
        let minLat = Infinity, maxLat = -Infinity, minLon = Infinity, maxLon = -Infinity;
        for (const [lat, lon] of [...gemeinden.flatMap(g => g.coords), ...kreisCoords]) {
            if (lat < minLat) minLat = lat; if (lat > maxLat) maxLat = lat;
            if (lon < minLon) minLon = lon; if (lon > maxLon) maxLon = lon;
        }

        const latRange = maxLat - minLat, lonRange = maxLon - minLon;
        const latCos = Math.cos((minLat + maxLat) / 2 * Math.PI / 180);
        const aspect = latRange / (lonRange * latCos);
        const svgW = SVG_WIDTH, svgH = svgW * aspect;
        const uW = svgW - 2 * SVG_PADDING, uH = svgH - 2 * SVG_PADDING;

        const project = ([lat, lon]) => [
            Math.round((SVG_PADDING + ((lon - minLon) / lonRange) * uW) * 1000) / 1000,
            Math.round((SVG_PADDING + ((maxLat - lat) / latRange) * uH) * 1000) / 1000,
        ];

        const municipalities = {};
        for (const g of gemeinden) {
            const slug = 'ov-' + slugify(g.name);
            const simp = simplify(g.coords.map(project), 0.5);
            if (simp.length < 3) continue;
            const c = centroid(simp);
            municipalities[slug] = {
                name: g.name,
                polygon: simp.map(p => p.join(' ')).join(' '),
                arrow: `translate(${c[0] - 5} ${c[1] - 5}) scale(.287)`,
                label: { text: g.name, transform: `translate(${c[0] - 15} ${c[1] - 15})` },
            };
        }

        const district = {};
        if (kreisCoords.length > 2) {
            const p = polygonPath(simplify(kreisCoords.map(project), 0.8));
            district.shadow = p; district.fill = p;
        }

        const water = {};
        for (const rel of waterRels) {
            const coords = extractRelCoords(rel, ways);
            if (coords.length < 3) continue;
            const s = simplify(coords.map(project), 0.4);
            if (s.length >= 3) water[slugify(rel.tags.name)] = polygonPath(s);
        }

        return {
            _meta: {
                title: displayName.split(',')[0],
                description: 'Generiert aus OpenStreetMap-Daten',
                viewBox: `0 0 ${Math.round(svgW)} ${Math.round(svgH)}`,
                source: 'openstreetmap',
                generated: new Date().toISOString().split('T')[0],
            },
            municipalities, district, water,
        };
    }

    function extractRelCoords(rel, ways) {
        const ids = (rel.members || [])
            .filter(m => m.type === 'way' && (m.role === 'outer' || m.role === ''))
            .map(m => m.ref);
        return joinSegs(ids.map(id => ways[id]).filter(Boolean));
    }

    function joinSegs(segs) {
        if (segs.length <= 1) return segs[0] || [];
        const result = [...segs[0]];
        const used = new Set([0]);
        for (let iter = 0; iter < segs.length * 2; iter++) {
            const last = result[result.length - 1];
            let found = false;
            for (let i = 0; i < segs.length; i++) {
                if (used.has(i) || !segs[i]?.length) continue;
                if (close(last, segs[i][0])) { result.push(...segs[i].slice(1)); used.add(i); found = true; break; }
                if (close(last, segs[i][segs[i].length - 1])) { result.push(...[...segs[i]].reverse().slice(1)); used.add(i); found = true; break; }
            }
            if (!found || used.size === segs.length) break;
        }
        return result;
    }

    function close(a, b) { return a && b && Math.abs(a[0] - b[0]) < 0.0001 && Math.abs(a[1] - b[1]) < 0.0001; }

    function simplify(pts, eps) {
        if (pts.length < 3) return pts;
        let mx = 0, mi = 0;
        for (let i = 1; i < pts.length - 1; i++) {
            const d = perpDist(pts[i], pts[0], pts[pts.length - 1]);
            if (d > mx) { mx = d; mi = i; }
        }
        if (mx > eps) {
            const l = simplify(pts.slice(0, mi + 1), eps);
            return [...l.slice(0, -1), ...simplify(pts.slice(mi), eps)];
        }
        return [pts[0], pts[pts.length - 1]];
    }

    function perpDist(pt, a, b) {
        const dx = b[0] - a[0], dy = b[1] - a[1];
        const len = Math.sqrt(dx * dx + dy * dy);
        if (!len) return Math.sqrt((pt[0] - a[0]) ** 2 + (pt[1] - a[1]) ** 2);
        return Math.abs(dy * pt[0] - dx * pt[1] + b[0] * a[1] - b[1] * a[0]) / len;
    }

    function centroid(pts) {
        let cx = 0, cy = 0;
        for (const [x, y] of pts) { cx += x; cy += y; }
        const n = pts.length;
        return [Math.round(cx / n * 100) / 100, Math.round(cy / n * 100) / 100];
    }

    function polygonPath(pts) { return pts.length ? 'M' + pts.map(p => p.join(',')).join('L') + 'Z' : ''; }

    function slugify(s) {
        return s.toLowerCase()
            .replace(/ä/g, 'ae').replace(/ö/g, 'oe').replace(/ü/g, 'ue').replace(/ß/g, 'ss')
            .replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    }

    function esc(s) {
        const el = document.createElement('span');
        el.textContent = s;
        return el.innerHTML;
    }

})();
