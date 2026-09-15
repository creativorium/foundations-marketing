import { chromium } from 'playwright';
import fs from 'node:fs/promises';
import assert from 'node:assert/strict';
const css = await fs.readFile('theme/build/main.css', 'utf8');
const js = await fs.readFile('theme/build/main.js', 'utf8');
const browser = await chromium.launch();
try {
  for (const reducedMotion of ['no-preference', 'reduce']) {
    const context = await browser.newContext({ reducedMotion });
    await context.route('http://transition.test/**', route => route.fulfill({
      contentType: 'text/html',
      body: `<html><head><style>${css}</style></head><body><div class="fm-px" aria-hidden="true">${'<span class="fm-px__cell"></span>'.repeat(180)}</div><main><h1>Transition fixture</h1><a href="/next">Next</a></main><script>${js}</script></body></html>`,
    }));
    const page = await context.newPage();
    await page.goto('http://transition.test/start');
    await page.waitForTimeout(650);
    if (reducedMotion === 'reduce') {
      assert.equal(await page.locator('.fm-px__cell').first().evaluate(el => getComputedStyle(el).animationName), 'none');
    } else {
      assert.equal(await page.locator('.fm-px').evaluate(el => getComputedStyle(el).visibility), 'hidden');
    }
    await page.evaluate(() => document.addEventListener('click', () => sessionStorage.setItem('clickTime', String(Date.now())), { capture: true }));
    await Promise.all([page.waitForURL('**/next'), page.getByRole('link', { name: 'Next' }).click()]);
    const elapsed = await page.evaluate(() => Date.now() - Number(sessionStorage.getItem('clickTime')));
    if (reducedMotion === 'no-preference') assert.ok(elapsed >= 140, 'Fast navigation skipped the cover');
    await page.goBack();
    assert.equal(await page.locator('.fm-px').getAttribute('data-leaving'), null, 'Back navigation left the curtain closed');
    console.log(`${reducedMotion}: navigation, curtain recovery and reduced-motion checks passed (${elapsed}ms navigation).`);
    await context.close();
  }
} finally { await browser.close(); }
