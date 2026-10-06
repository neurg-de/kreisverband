const assert = require('node:assert/strict');
const { test } = require('node:test');
const { metadataQuery, boundaryQuery, chooseSubdivisionLevel, processOverpassData, restoreMappings } = require('../theme/lib/js/map-geometry.js');

function fixture(rootLevel, childLevel) {
    const elements = [];
    let node = 1;
    function ring(id, coords) {
        const ids = coords.map(([lat, lon]) => { const id = node++; elements.push({ type: 'node', id, lat, lon }); return id; });
        ids.push(ids[0]);
        elements.push({ type: 'way', id, nodes: ids });
        return { type: 'way', ref: id, role: 'outer' };
    }
    elements.push({ type: 'relation', id: 100, tags: { name: 'Mustergebiet', boundary: 'administrative', admin_level: String(rootLevel) }, members: [ring(200, [[0, 0], [0, 3], [3, 3], [3, 0]])] });
    elements.push({ type: 'relation', id: 101, tags: { name: 'Musterort', boundary: 'administrative', admin_level: String(childLevel) }, members: [ring(201, [[0, 0], [0, 1], [1, 1], [1, 0]]), ring(202, [[2, 2], [2, 3], [3, 3], [3, 2]])] });
    elements.push({ type: 'relation', id: 102, tags: { name: 'Musterort', boundary: 'administrative', admin_level: String(childLevel) }, members: [ring(203, [[0, 1], [0, 2], [1, 2], [1, 1]])] });
    return { elements };
}

test('automatic and manual levels handle county, city, district and state', () => {
    for (const [root, child] of [[6, 8], [6, 9], [5, 6], [4, 6], [8, 10]]) {
        const raw = fixture(root, child);
        assert.equal(chooseSubdivisionLevel(raw, 100), child);
        assert.equal(chooseSubdivisionLevel(raw, 100, String(child)), child);
        assert.throws(() => chooseSubdivisionLevel(raw, 100, String(root)), /Unterebene/);
        assert.match(boundaryQuery(100, child), new RegExp(`admin_level"="${child}`));
    }
    assert.throws(() => metadataQuery('100);out;'), /Ungültiges/);
    assert.throws(() => chooseSubdivisionLevel({ elements: [{ type: 'relation', id: 100, tags: { admin_level: '6' } }] }, 100), /Keine Untergebiete/);
});

test('geometry keeps multipart areas, unique names and configured URL prefixes', () => {
    const raw = fixture(6, 9);
    // A smaller exclave beyond the largest outer ring must fit in the viewport.
    const island = raw.elements.find(e => e.type === 'way' && e.id === 202);
    for (const id of new Set(island.nodes)) {
        const node = raw.elements.find(e => e.type === 'node' && e.id === id);
        node.lat += 10; node.lon += 10;
    }
    const map = processOverpassData(raw, 100, 'Mustergebiet, Deutschland', 9, 'stadtteil');
    assert.equal(Object.keys(map.municipalities).length, 2);
    assert.ok(map.municipalities['stadtteil-musterort']);
    assert.ok(map.municipalities['stadtteil-musterort-102']);
    assert.equal((map.municipalities['stadtteil-musterort'].path.match(/M/g) || []).length, 2);
    assert.doesNotMatch(JSON.stringify(map), /NaN|Infinity/);
    assert.equal(map._meta.subdivisionLevel, 9);
    assert.equal(map._meta.osmId, 100);
    const [, , width, height] = map._meta.viewBox.split(' ').map(Number);
    for (const match of map.municipalities['stadtteil-musterort'].path.matchAll(/([\d.]+),([\d.]+)/g)) {
        assert.ok(Number(match[1]) <= width && Number(match[2]) <= height, 'Exclave stays inside the viewport');
    }
});

test('reload retains scope URLs by OSM ID after renaming and changing prefixes', () => {
    const raw = fixture(6, 9);
    const oldMap = processOverpassData(raw, 100, 'Mustergebiet', 9);
    oldMap.municipalities['ov-musterort'] = { ...oldMap.municipalities['ov-musterort'], type: 'link', ovSlug: 'existing-account', link: 'https://example.org/', eventsEnabled: true };
    raw.elements.find(e => e.id === 101).tags.name = 'Neuer Ortsname';
    const fresh = processOverpassData(raw, 100, 'Mustergebiet', 9, 'lokal');
    restoreMappings(fresh, oldMap);
    assert.equal(fresh.municipalities['ov-musterort'].name, 'Neuer Ortsname');
    assert.equal(fresh.municipalities['ov-musterort'].ovSlug, 'existing-account');
    assert.equal(fresh.municipalities['ov-musterort'].eventsEnabled, true);
    assert.equal(fresh.municipalities['ov-musterort'].link, 'https://example.org/');
    const other = processOverpassData(fixture(6, 9), 100, 'Anderes Gebiet', 9, 'lokal');
    other._meta.osmId = 999;
    restoreMappings(other, oldMap);
    assert.equal(other.municipalities['lokal-musterort'].ovSlug, undefined);
});

test('unincorporated land is not rendered as water and county AGS remains visible', () => {
    const raw = fixture(5, 6);
    raw.elements.find(e => e.id === 101).tags['de:amtlicher_gemeindeschluessel'] = '09572';
    const map = processOverpassData(raw, 100, 'Musterbezirk', 6);
    assert.equal(Object.keys(map.municipalities).length, 2);
    const local = fixture(6, 8);
    local.elements.find(e => e.id === 101).tags['de:amtlicher_gemeindeschluessel'] = '09188401';
    const area = processOverpassData(local, 100, 'Musterkreis', 8);
    assert.equal(Object.keys(area.municipalities).length, 1);
    assert.deepEqual(area.water, {});
});

test('reload preserves all areas when renamed areas swap slug keys', () => {
    const raw = fixture(6, 9);
    raw.elements.find(e => e.id === 102).tags.name = 'Zweiter Ort';
    const old = processOverpassData(raw, 100, 'Mustergebiet', 9);
    old.municipalities['ov-musterort'].ovSlug = 'first-account';
    old.municipalities['ov-zweiter-ort'].ovSlug = 'second-account';
    raw.elements.find(e => e.id === 101).tags.name = 'Zweiter Ort';
    raw.elements.find(e => e.id === 102).tags.name = 'Musterort';
    const fresh = restoreMappings(processOverpassData(raw, 100, 'Mustergebiet', 9), old);
    assert.equal(Object.keys(fresh.municipalities).length, 2);
    assert.equal(fresh.municipalities['ov-musterort'].osmId, 101);
    assert.equal(fresh.municipalities['ov-musterort'].ovSlug, 'first-account');
    assert.equal(fresh.municipalities['ov-zweiter-ort'].osmId, 102);
    assert.equal(fresh.municipalities['ov-zweiter-ort'].ovSlug, 'second-account');
});

test('incomplete responses and open boundaries fail before saving a map', () => {
    assert.throws(() => processOverpassData({ remark: 'timeout', elements: [] }, 100, 'Mustergebiet'), /Unvollständige/);
    const raw = fixture(6, 9);
    raw.elements.find(e => e.type === 'way' && e.id === 201).nodes.pop();
    assert.throws(() => processOverpassData(raw, 100, 'Mustergebiet', 9), /Unvollständige Grenze/);
});
