import { chromium } from 'playwright';
import fs from 'node:fs/promises';
import assert from 'node:assert/strict';

const css = await fs.readFile('theme/build/main.css', 'utf8');
const js = await fs.readFile('theme/build/main.js', 'utf8');
const browser = await chromium.launch();

try {
  for (const reducedMotion of ['no-preference', 'reduce']) {
    const context = await browser.newContext({ reducedMotion });
    await context.route('http://transition.test/**', async route => {
      const pathname = new URL(route.request().url()).pathname;
      if (pathname === '/slow') await new Promise(resolve => setTimeout(resolve, 1400));
      await route.fulfill({
        contentType: 'text/html',
        body: `<html><head><style>${css}</style></head><body><header class="fm-header">Header</header><main class="fm-main"><h1>Transition fixture</h1><a href="/next">Next</a><a href="/slow">Slow</a><a href="#section">Section</a><div id="section">Target</div></main><footer class="fm-footer">Footer</footer><script>${js}</script></body></html>`,
      });
    });

    const page = await context.newPage();
    await page.goto('http://transition.test/start');
    await page.getByRole('link', { name: 'Section' }).click();
    assert.equal(await page.locator('body').getAttribute('class'), null, 'Hash navigation started a page transition');

    const started = Date.now();
    await Promise.all([page.waitForURL('**/next'), page.getByRole('link', { name: 'Next' }).click()]);
    const elapsed = Date.now() - started;
    if (reducedMotion === 'no-preference') assert.ok(elapsed >= 120, 'Outgoing cue was skipped');
    else assert.ok(elapsed < 120, 'Reduced motion delayed navigation');
    assert.equal(await page.locator('body').getAttribute('aria-busy'), null, 'Incoming page stayed busy');

    await page.goto('http://transition.test/start');
    await page.getByRole('link', { name: 'Slow' }).click({ noWaitAfter: true });
    if (reducedMotion === 'no-preference') {
      await page.waitForTimeout(1050);
      assert.equal(await page.locator('body').getAttribute('aria-busy'), null, 'Slow navigation left the page blocked');
      assert.ok(Number(await page.locator('.fm-main').evaluate(el => getComputedStyle(el).opacity)) > .95, 'Slow navigation hid the current page');
    }
    await page.waitForURL('**/slow');
    console.log(`${reducedMotion}: links, reduced motion and slow-navigation recovery passed.`);
    await context.close();
  }
} finally {
  await browser.close();
}
