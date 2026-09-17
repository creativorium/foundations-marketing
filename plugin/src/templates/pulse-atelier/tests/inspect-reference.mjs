import { mkdir, writeFile } from 'node:fs/promises';
import { pathToFileURL } from 'node:url';
import { resolve, join } from 'node:path';
import { browser, delay } from './cdp.mjs';
const argument = name => process.argv[process.argv.indexOf(name) + 1];
const output = resolve(argument('--output'));
await mkdir(output, { recursive: true });
const chrome = await browser(argument('--chrome'));
try {
  for (const width of [1440, 820, 375]) {
    const page = await chrome.page(pathToFileURL(resolve(argument('--reference'))).href, width);
    await page.ready('!!document.querySelector("#top h1") && !document.querySelector("#__bundler_loading") && document.fonts.status === "loaded"');
    await delay(500);
    const report = await page.evaluate(`(() => {
      const props = ['display','position','boxSizing','fontFamily','fontWeight','fontStyle','fontSize','lineHeight','letterSpacing','color','backgroundColor','width','maxWidth','height','minHeight','gridTemplateColumns','gap','padding','margin','border','borderRadius','backgroundImage','backgroundSize','backgroundPosition','transition','outline','textDecoration'];
      const read = el => { const c = getComputedStyle(el), r = el.getBoundingClientRect(); return { tag: el.tagName, text: el.textContent.trim(), rect: {x:r.x,y:r.y,width:r.width,height:r.height}, style: Object.fromEntries(props.map(p=>[p,c[p]])), before: getComputedStyle(el, '::before').content, after: getComputedStyle(el, '::after').content }; };
      return {width:innerWidth,height:document.documentElement.scrollHeight,body:read(document.body),header:read(document.querySelector('#top').parentElement.querySelector('a[href="#top"]').parentElement),sections:[...document.querySelectorAll('#top > section')].map((el,i)=>({index:i,...read(el),children:[...el.querySelectorAll('*')].map(read)})),footer:read(document.querySelector('footer')),dom:document.body.innerHTML,styles:[...document.styleSheets].flatMap(s=>{try{return [...s.cssRules].map(r=>r.cssText)}catch{return []}})};
    })()`);
    await writeFile(join(output, `${width}.json`), JSON.stringify(report, null, 2));
    const shot = await page.call('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true, clip: { x: 0, y: 0, width, height: report.height, scale: 1 } });
    await writeFile(join(output, `${width}.png`), Buffer.from(shot.data, 'base64'));
    console.log(JSON.stringify({width, height:report.height, header:report.header.rect, sections:report.sections.map(s=>({index:s.index,y:s.rect.y,height:s.rect.height})), media:report.styles.filter(s=>s.startsWith('@media'))}));
  }
} finally { await chrome.close(); }
