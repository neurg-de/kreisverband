// Read-only interaction checks for a page with multiple populated team departments.
// Use synthetic content on staging. Install Playwright separately.
const assert = require('node:assert/strict');
const { chromium } = require(process.env.GK_PLAYWRIGHT_MODULE || 'playwright');
const base = process.env.GK_SMOKE_URL || 'http://localhost:8083/team-carousel-check/';

(async () => {
    const browser = await chromium.launch({ channel: process.env.GK_BROWSER_CHANNEL || 'chrome', headless: true });
    try {
        for (const width of [1280, 768, 390]) {
            const page = await browser.newPage({ viewport: { width, height: 900 } });
            const errors = [];
            page.on('pageerror', error => errors.push(error.message));
            await page.context().addCookies([{ name: 'gk_cookie_consent', value: '1', url: base }]);
            await page.clock.install();
            await page.goto(base, { waitUntil: 'networkidle' });
            const team = page.locator('[data-team-carousel]').first();
            const play = team.locator('[data-team-play]');
            const next = team.locator('[data-team-next]');
            const state = () => team.evaluate(root => {
                const row = root.querySelector('.gk-team__panel:not([hidden]) .gk-team__track');
                const bounds = row.getBoundingClientRect();
                return {
                    panel: row.parentElement.id,
                    left: row.scrollLeft,
                    shown: [...row.children].filter(card => {
                        const r = card.getBoundingClientRect();
                        return r.left >= bounds.left - 2 && r.right <= bounds.right + 2;
                    }).map(card => card.textContent.trim()),
                    height: row.clientHeight,
                    scrollbar: getComputedStyle(row).scrollbarWidth
                };
            });
            const tick = async ms => {
                await page.clock.fastForward(ms);
                // Native smooth scrolling uses the compositor, not JS timers.
                await page.waitForTimeout(800);
            };
            await team.scrollIntoViewIfNeeded();
            await team.locator('.gk-team__track').first().hover();
            const initial = await state();
            assert.equal(initial.scrollbar, 'none');
            await tick(12500);
            assert.notEqual((await state()).left, initial.left, 'A resting mouse must not block autoplay');

            await play.click();
            const paused = await state();
            await tick(18000);
            assert.deepEqual(await state(), paused, 'Explicit pause must stop autoplay');
            await play.click();
            await tick(6500);
            assert.notDeepEqual(await state(), paused, 'Explicit start must work with the button focused');

            await next.click();
            await page.waitForTimeout(800);
            const manuallyChanged = await state();
            await tick(12500);
            assert.notDeepEqual(await state(), manuallyChanged, 'Pointer focus must not prevent resuming after idle');

            await team.locator('[role="tab"]').first().focus();
            await page.keyboard.press('Home');
            const keyboard = await state();
            await tick(18000);
            assert.deepEqual(await state(), keyboard, 'Keyboard navigation must pause autoplay');
            await play.focus();
            await page.keyboard.press('Enter'); // Pause.
            await page.keyboard.press('Enter'); // Explicit keyboard start.
            await tick(6500);
            assert.notDeepEqual(await state(), keyboard, 'Explicit keyboard start must work');

            const seen = new Set();
            const total = await team.locator('.gk-team__card').count();
            for (let i = 0; i < total + 2 && seen.size < total; i++) {
                const current = await state();
                current.shown.forEach(name => seen.add(name));
                assert.equal(current.height, initial.height, 'The compact row must retain its height');
                await tick(6500);
            }
            assert.equal(seen.size, total, 'Every member must become fully visible during autoplay');
            await team.locator('[data-team-expand]').click();
            assert.equal(await team.locator('.gk-team__panel:visible').count(), await team.locator('.gk-team__panel').count());
            assert.equal(await team.locator('.gk-team__card:visible').count(), total);
            await team.locator('[data-team-expand]').click();
            assert.equal(await team.locator('.gk-team__panel:visible').count(), 1);
            assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false);

            await page.emulateMedia({ reducedMotion: 'reduce' });
            await page.waitForFunction(() => document.querySelector('[data-team-play]').textContent === 'Automatik starten');
            assert.equal(await play.textContent(), 'Automatik starten');
            await page.locator('h1').first().click();
            await team.scrollIntoViewIfNeeded();
            const reduced = await state();
            await tick(18000);
            assert.deepEqual(await state(), reduced, 'Reduced motion must default to paused');
            assert.deepEqual(errors, []);
            console.log(`${width}px: idle, pointer/keyboard start, pause, all ${total} members, expansion and reduced motion passed`);
            await page.close();
        }
    } finally {
        await browser.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
