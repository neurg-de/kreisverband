// Read-only browser acceptance for every configured municipality.
// Install Playwright separately; GK_BROWSER_CHANNEL defaults to system Chrome.
const assert = require('node:assert/strict');
const { chromium } = require(process.env.GK_PLAYWRIGHT_MODULE || 'playwright');
const base = process.env.GK_SMOKE_URL || 'http://localhost:8083/';

(async () => {
    const browser = await chromium.launch({ channel: process.env.GK_BROWSER_CHANNEL || 'chrome', headless: true });
    const results = [];
    try {
        for (const width of [1280, 390]) {
            const context = await browser.newContext({ viewport: { width, height: 900 } });
            await context.addCookies([{ name: 'gk_cookie_consent', value: '1', url: base }]);
            const page = await context.newPage();
            const errors = [];
            page.on('pageerror', error => errors.push(error.message));
            const home = async () => {
                const response = await page.goto(base, { waitUntil: 'networkidle' });
                assert.equal(response.status(), 200);
                assert.equal(await page.evaluate(() => innerWidth), width);
            };
            await home();
            const items = await page.locator('.gk-ov-chip').evaluateAll(nodes => nodes.map(n => ({ slug: n.dataset.ovSlug, url: n.href })));
            assert.ok(items.length);
            for (const item of items) {
                await home();
                const map = page.locator(`svg a[data-ov-slug="${item.slug}"]`);
                assert.equal(await map.getAttribute('href'), item.url);
                assert.ok(item.url && !item.url.endsWith('#') && !item.url.includes('example.com'));
                if (width < 768) await page.locator('.gk-ov-toggle').click();
                await map.scrollIntoViewIfNeeded();
                // Find a hittable interior point; concave polygons need not contain their center.
                const position = await map.evaluate(node => {
                    const r = node.getBoundingClientRect();
                    for (let y = 1; y < 10; ++y) for (let x = 1; x < 10; ++x) {
                        const px = r.left + r.width * x / 10, py = r.top + r.height * y / 10;
                        if (document.elementFromPoint(px, py)?.closest('[data-ov-slug]') === node) return { x: px, y: py };
                    }
                });
                assert.ok(position, `No clickable point for ${item.slug} at ${width}px`);
                await page.mouse.click(position.x, position.y);
                if (width < 768) {
                    const sheet = page.locator('.gk-ov-sheet');
                    await sheet.waitFor({ state: 'visible' });
                    const cta = sheet.locator('a');
                    assert.equal(await cta.getAttribute('href'), item.url);
                    assert.ok(await cta.evaluate(n => n === document.activeElement));
                    await page.keyboard.press('Shift+Tab');
                    assert.ok(await sheet.locator('button').evaluate(n => n === document.activeElement));
                    await page.keyboard.press('Tab');
                    assert.ok(await cta.evaluate(n => n === document.activeElement));
                    await page.keyboard.press('Escape');
                    await sheet.waitFor({ state: 'hidden' });
                    assert.ok(await map.evaluate(n => n === document.activeElement));
                    await page.mouse.click(position.x, position.y);
                    await cta.click();
                }
                await page.waitForURL(item.url);
                assert.ok(await page.locator('h1').count());
                const status = await context.request.get(item.url);
                assert.equal(status.status(), 200);
                await home();
                if (width < 768) {
                    await page.locator(`.gk-ov-chip[data-ov-slug="${item.slug}"]`).click();
                    await page.waitForURL(item.url);
                    await home();
                    await page.locator('.gk-ov-toggle').click();
                }
                await map.focus();
                await page.keyboard.press('Enter');
                await page.waitForURL(item.url);
                results.push({ width, slug: item.slug, url: item.url, status: status.status(), mouse: true, keyboard: true, mobileListAndDialog: width < 768 });
            }
            assert.deepEqual(errors, []);
            await context.close();
        }
        console.log(JSON.stringify({ pass: true, results }, null, 2));
    } finally {
        await browser.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
