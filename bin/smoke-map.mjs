#!/usr/bin/env node
/** Read-only live checks of the generator against public OpenStreetMap data. */
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
import path from 'node:path';
import geometry from '../theme/lib/js/map-geometry.js';

const cacheDir = process.env.NEURG_MAP_CACHE;
const endpoints = (process.env.NEURG_OVERPASS_ENDPOINTS || 'https://overpass-api.de/api/interpreter,https://overpass.private.coffee/api/interpreter').split(',');
const cases = [
    { name: 'München', level: 9, count: 25, kind: 'city' },
    { name: 'Landkreis München', level: 8, count: 29, kind: 'county' },
    { name: 'Landkreis Starnberg', level: 8, count: 14, kind: 'county' },
    { name: 'Oberbayern', level: 6, count: 23, kind: 'state_district' },
];
const headers = { 'User-Agent': 'Neurg-map-validation/1.0 (https://neurg.de)' };
async function request(url, options = {}) {
    const response = await fetch(url, { ...options, headers: { ...headers, ...options.headers }, signal: AbortSignal.timeout(240000) });
    if (!response.ok) throw new Error(`${response.status}: ${url}`);
    const data = await response.json();
    if (data.remark) throw new Error(data.remark);
    return data;
}
async function overpass(query, key) {
    const file = cacheDir ? path.join(cacheDir, key + '.json') : null;
    if (file) {
        try { return JSON.parse(await fs.readFile(file, 'utf8')); } catch (error) { if (error.code !== 'ENOENT') throw error; }
    }
    let data;
    let lastError;
    for (const endpoint of endpoints) {
        try {
            data = await request(endpoint + '?data=' + encodeURIComponent(query));
            break;
        } catch (error) { lastError = error; console.log(`Retry: ${error.message}`); }
    }
    if (!data) throw lastError;
    if (file) { await fs.mkdir(cacheDir, { recursive: true }); await fs.writeFile(file, JSON.stringify(data)); }
    return data;
}
for (const testcase of cases) {
    const search = await request('https://nominatim.openstreetmap.org/search?' + new URLSearchParams({ q: testcase.name, format: 'json', countrycodes: 'de', limit: '8', 'accept-language': 'de' }));
    const result = search.find(r => r.osm_type === 'relation' && r.type === 'administrative' && r.addresstype === testcase.kind)
        || search.find(r => r.osm_type === 'relation' && r.type === 'administrative');
    assert.ok(result, `Boundary search: ${testcase.name}`);
    const id = Number(result.osm_id);
    console.log(`Loading ${testcase.name}: relation ${id}`);
    const metadata = await overpass(geometry.metadataQuery(id), `${id}-metadata`);
    const level = geometry.chooseSubdivisionLevel(metadata, id);
    assert.equal(level, testcase.level, `Subdivision level: ${testcase.name}`);
    const raw = await overpass(geometry.boundaryQuery(id, level), `${id}-${level}-boundaries`);
    const map = geometry.processOverpassData(raw, id, result.display_name, level);
    const areas = Object.values(map.municipalities);
    assert.equal(areas.length, testcase.count, `Area count: ${testcase.name}`);
    assert.ok(map.district.fill && map._meta.viewBox, 'Root outline and viewport');
    assert.ok(!/NaN|Infinity/.test(JSON.stringify(map)), 'Finite SVG coordinates');
    assert.equal(new Set(areas.map(a => a.osmId)).size, areas.length, 'Unique OSM areas');
    for (const area of areas) assert.ok(area.path.startsWith('M') && area.path.endsWith('Z') && area.polygon && area.label, `Geometry: ${area.name}`);
    if (cacheDir) await fs.writeFile(path.join(cacheDir, `${id}-map.json`), JSON.stringify(map));
    console.log(`PASS ${testcase.name}: ${areas.length} areas, level ${level}, ${areas.filter(a => (a.path.match(/M/g) || []).length > 1).length} multipart/holed areas`);
    await new Promise(resolve => setTimeout(resolve, 1100));
}
