/** Read-only responsive smoke test, using Playwright's matching Chromium build. */
import { chromium } from 'playwright';
import fs from 'node:fs/promises';
import assert from 'node:assert/strict';
const [site, ...requested] = process.argv.slice(2);
if (!site) throw new Error('Usage: npm run test:browser -- http://your-site.local / /about/ /contact/');
const base = new URL(site);
if (!['http:', 'https:'].includes(base.protocol)) throw new Error('Use a WordPress HTTP(S) URL.');
const paths = requested.length ? requested : ['/'];
const output = 'artifacts/browser';
await fs.mkdir(output, { recursive: true });
const browser = await chromium.launch({ headless: true });
const results = [];
try {
  const page = await browser.newPage();
  const errors = [];
  page.on('pageerror', error => errors.push(error.message));
  for (const path of paths) {
    const url = new URL(path, base);
    assert.equal(url.origin, base.origin, 'Pages must stay on the selected test site.');
    for (const width of [375, 820, 1440]) {
      await page.setViewportSize({ width, height: 1000 });
      const response = await page.goto(url.href, { waitUntil: 'load', timeout: 60000 });
      assert.equal(response.status(), 200, `${url.href}: HTTP status`);
      // Load lazy images by visiting their position before capturing the whole page.
      for (const img of await page.locator('main img').all()) {
        if (await img.isVisible()) await img.scrollIntoViewIfNeeded();
      }
      await page.evaluate(() => window.scrollTo(0, 0));
      await page.waitForFunction(() => [...document.querySelectorAll('main img')].every(img => img.complete), null, { polling: 100 });
      const state = await page.evaluate(() => ({
        main: document.querySelectorAll('main').length,
        h1: document.querySelectorAll('h1').length,
        overflow: document.documentElement.scrollWidth > innerWidth,
        brokenImages: [...document.querySelectorAll('main img')].filter(img => !img.naturalWidth).map(img => img.src),
        unresolvedTokens: document.querySelector('main')?.innerHTML.includes('{{') ?? false,
      }));
      assert.equal(state.main, 1, 'Expected one main landmark');
      assert.equal(state.h1, 1, 'Expected one H1');
      assert.equal(state.overflow, false, `${path} overflows at ${width}px`);
      assert.deepEqual(state.brokenImages, [], 'Broken content images');
      assert.equal(state.unresolvedTokens, false, 'Unresolved import token');
      const name = (url.pathname.replace(/[^a-z0-9-]/gi, '_') || 'home') + '-' + width;
      await page.screenshot({ path: `${output}/${name}.png`, fullPage: true });
      results.push({ url: url.href, width, ...state });
    }
  }
  assert.deepEqual(errors, [], 'Browser JavaScript errors');
} finally {
  await fs.writeFile(`${output}/results.json`, JSON.stringify(results, null, 2));
  await browser.close();
}
console.log(`${results.length} page/viewport checks passed. Screenshots: ${output}/`);
