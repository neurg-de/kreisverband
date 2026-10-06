/**
 * Pure OpenStreetMap boundary processing shared by the generator and tests.
 *
 * @package Neurg_Kreisverband
 */

(function(root, factory) {
    'use strict';
    if (typeof module === 'object' && module.exports) module.exports = factory();
    else root.gkMapGeometry = factory();
})(typeof globalThis !== 'undefined' ? globalThis : this, function() {
    'use strict';
    const SVG_WIDTH = 400;
    const SVG_PADDING = 10;
    function metadataQuery(osmId) {
        if (!Number.isSafeInteger(Number(osmId)) || Number(osmId) <= 0) throw new Error('Ungültiges Gebiet.');
        return `[out:json][timeout:25];rel(${Number(osmId)})->.root;.root out tags;.root map_to_area->.gebiet;rel(area.gebiet)["boundary"="administrative"]["admin_level"~"^(4|5|6|7|8|9|10|11)$"];out tags;`;
    }

    function chooseSubdivisionLevel(data, osmId, requested = 'auto') {
        const root = data.elements.find(e => e.type === 'relation' && e.id === Number(osmId));
        const rootLevel = Number(root?.tags?.admin_level);
        if (!rootLevel) throw new Error('Bitte eine Verwaltungsgrenze auswählen.');
        const counts = new Map();
        for (const e of data.elements) {
            const level = Number(e.tags?.admin_level);
            if (e.type === 'relation' && e.id !== Number(osmId) && e.tags?.boundary === 'administrative' && level > rootLevel) {
                counts.set(level, (counts.get(level) || 0) + 1);
            }
        }
        if (requested !== 'auto') {
            const level = Number(requested);
            if (!Number.isInteger(level) || level <= rootLevel || !counts.has(level)) throw new Error('Diese Unterebene ist im gewählten Gebiet nicht vorhanden. Bitte eine andere Unterteilung wählen.');
            return level;
        }
        const preferred = rootLevel <= 2 ? 4 : rootLevel <= 5 ? 6 : rootLevel <= 7 ? 8 : 9;
        const levels = [...counts.keys()].sort((a, b) => a - b);
        const result = counts.has(preferred) ? preferred : levels.find(level => counts.get(level) > 1) || levels[0];
        if (!result) throw new Error('Keine Untergebiete gefunden. Bitte ein größeres Gebiet oder eine andere Unterteilung wählen.');
        return result;
    }

    function boundaryQuery(osmId, childLevel) {
        if (!Number.isSafeInteger(Number(osmId)) || Number(osmId) <= 0 || !Number.isInteger(Number(childLevel)) || childLevel < 4 || childLevel > 11) throw new Error('Ungültige Gebietsauswahl.');
        return `[out:json][timeout:90];rel(${Number(osmId)})->.root;.root map_to_area->.gebiet;(rel(area.gebiet)["boundary"="administrative"]["admin_level"="${Number(childLevel)}"];.root;);out body;>>;out skel qt;`;
    }

    function restoreMappings(data, previous) {
        if (!previous) return data;
        const sameArea = previous._meta?.osmId ? Number(data._meta?.osmId) === Number(previous._meta.osmId) : data._meta?.title === previous._meta?.title;
        const sameLevel = Number(data._meta?.subdivisionLevel) === Number(previous._meta?.subdivisionLevel || 8);
        if (!sameArea || !sameLevel) return data;
        const restored = {};
        const unassigned = [];
        for (const [slug, area] of Object.entries(data.municipalities)) {
            const legacy = previous.municipalities[slug];
            const oldEntry = Object.entries(previous.municipalities).find(([, m]) => m.osmId && m.osmId === area.osmId) || (legacy && !legacy.osmId ? [slug, legacy] : null);
            const old = oldEntry?.[1];
            if (!old) { unassigned.push([slug, area]); continue; }
            restored[oldEntry[0]] = area;
            for (const key of ['type', 'link', 'ovSlug', 'eventsEnabled']) if (Object.hasOwn(old, key)) area[key] = old[key];
        }
        for (const [slug, area] of unassigned) {
            restored[restored[slug] ? slug + '-' + area.osmId : slug] = area;
        }
        data.municipalities = restored;
        return data;
    }

    // ── Geo Processing ──────────────────────────────────────────────────────

    function processOverpassData(data, kreisOsmId, displayName, childLevel = 8, urlPrefix = 'ov') {
        if (data.remark || !Array.isArray(data.elements)) throw new Error('Unvollständige Geodaten. Bitte erneut laden.');
        const nodes = {};
        data.elements.filter(e => e.type === 'node').forEach(n => { nodes[n.id] = [n.lat, n.lon]; });

        const ways = {};
        data.elements.filter(e => e.type === 'way').forEach(w => {
            ways[w.id] = (w.nodes || []).map(nid => nodes[nid]).filter(Boolean);
        });

        const allLevel8 = data.elements.filter(e =>
            e.type === 'relation' && e.id !== kreisOsmId && e.tags?.name &&
            e.tags.boundary === 'administrative' && Number(e.tags['admin_level']) === Number(childLevel));

        const waterRels = [], gemeindeRels = [];
        for (const rel of allLevel8) {
            const tags = rel.tags;
            const ags = tags['de:amtlicher_gemeindeschluessel'] || '';
            const agsLast3 = ags.length >= 3 ? parseInt(ags.slice(-3), 10) : 0;
            const isFrei = (Number(childLevel) === 8 && ags.length === 8 && agsLast3 >= 401) || tags.natural === 'water' ||
                (tags['name:prefix'] || '').toLowerCase().includes('gemeindefrei');
            (isFrei ? waterRels : gemeindeRels).push(rel);
        }

        const gemeinden = gemeindeRels
            .map(rel => ({ id: rel.id, name: rel.tags.name, rings: extractRelRings(rel, ways), coords: extractRelCoords(rel, ways) }))
            .filter(g => g.coords.length > 2);

        const kreisRel = data.elements.find(e => e.type === 'relation' && e.id === kreisOsmId);
        const kreisCoords = kreisRel ? extractRelCoords(kreisRel, ways) : [];

        if (gemeinden.length === 0) return { municipalities: {} };

        // Bounding box
        let minLat = Infinity, maxLat = -Infinity, minLon = Infinity, maxLon = -Infinity;
        for (const [lat, lon] of [...gemeinden.flatMap(g => g.rings.flat()), ...(kreisRel ? extractRelRings(kreisRel, ways).flat() : [])]) {
            if (lat < minLat) minLat = lat; if (lat > maxLat) maxLat = lat;
            if (lon < minLon) minLon = lon; if (lon > maxLon) maxLon = lon;
        }

        const latRange = maxLat - minLat, lonRange = maxLon - minLon;
        if (!(latRange > 0 && lonRange > 0)) throw new Error('Die Gebietsgrenzen haben keine gültige Ausdehnung.');
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
            const base = slugify(urlPrefix) + '-' + slugify(g.name);
            const slug = municipalities[base] ? base + '-' + g.id : base;
            const simp = simplify(g.coords.map(project), 0.5);
            if (simp.length < 3) continue;
            const c = centroid(simp);
            municipalities[slug] = {
                name: g.name,
                osmId: g.id,
                path: g.rings.map(ring => polygonPath(simplify(ring.map(project), 0.5))).join(''),
                polygon: simp.map(p => p.join(' ')).join(' '),
                arrow: `translate(${c[0] - 5} ${c[1] - 5}) scale(.287)`,
                label: { text: g.name, transform: `translate(${c[0] - 15} ${c[1] - 15})` },
            };
        }

        const district = {};
        if (kreisCoords.length > 2) {
            const p = extractRelRings(kreisRel, ways).map(ring => polygonPath(simplify(ring.map(project), 0.8))).join('');
            district.shadow = p; district.fill = p;
        }

        const water = {};
        for (const rel of waterRels) {
            const coords = extractRelCoords(rel, ways);
            if (coords.length < 3 || rel.tags.natural !== 'water') continue;
            const s = simplify(coords.map(project), 0.4);
            if (s.length >= 3) water[slugify(rel.tags.name)] = polygonPath(s);
        }

        return {
            _meta: {
                title: displayName.split(',')[0],
                description: 'Generiert aus OpenStreetMap-Daten',
                viewBox: `0 0 ${Math.round(svgW)} ${Math.round(svgH)}`,
                source: 'openstreetmap',
                osmId: kreisOsmId,
                subdivisionLevel: Number(childLevel),
                generated: new Date().toISOString().split('T')[0],
            },
            municipalities, district, water,
        };
    }

    function extractRelRings(rel, ways, includeInner = true) {
        const rings = [];
        for (const role of includeInner ? ['outer', 'inner'] : ['outer']) {
            const segments = (rel.members || [])
                .filter(m => m.type === 'way' && (m.role === role || (role === 'outer' && m.role === '')))
                .map(m => ways[m.ref]).filter(s => s?.length > 1);
            while (segments.length) {
                const ring = [...segments.shift()];
                while (!close(ring[0], ring[ring.length - 1])) {
                    const last = ring[ring.length - 1];
                    const next = segments.findIndex(segment => close(last, segment[0]) || close(last, segment[segment.length - 1]));
                    if (next < 0) throw new Error('Unvollständige Grenze: ' + (rel.tags?.name || rel.id));
                    let segment = segments.splice(next, 1)[0];
                    if (!close(last, segment[0])) segment = [...segment].reverse();
                    ring.push(...segment.slice(1));
                }
                if (ring.length > 3) rings.push(ring);
            }
        }
        return rings;
    }

    function extractRelCoords(rel, ways) {
        return extractRelRings(rel, ways, false).sort((a, b) => ringArea(b) - ringArea(a))[0] || [];
    }

    function ringArea(ring) {
        let area = 0;
        for (let i = 1; i < ring.length; i++) area += ring[i - 1][0] * ring[i][1] - ring[i][0] * ring[i - 1][1];
        return Math.abs(area);
    }

    function close(a, b) { return a && b && Math.abs(a[0] - b[0]) < 0.0000001 && Math.abs(a[1] - b[1]) < 0.0000001; }

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

    return { metadataQuery, chooseSubdivisionLevel, boundaryQuery, processOverpassData, restoreMappings };
});
