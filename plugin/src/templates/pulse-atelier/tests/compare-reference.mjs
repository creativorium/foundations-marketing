import { mkdir, readFile, writeFile } from 'node:fs/promises';
import { pathToFileURL } from 'node:url';
import { resolve, join } from 'node:path';
import { browser, delay } from './cdp.mjs';
const argument = name => process.argv[process.argv.indexOf(name) + 1];
const output = resolve(argument('--output'));
await mkdir(output, { recursive: true });
const chrome = await browser(argument('--chrome'));
const reports = [];
try {
  for (const width of [1440, 820, 375]) {
    const page = await chrome.page(pathToFileURL(resolve(argument('--preview'))).href, width);
    await page.ready(); await delay(500);
    const report = await page.evaluate(`(() => {
      const props = ['display','position','boxSizing','fontWeight','fontStyle','fontSize','lineHeight','letterSpacing','color','backgroundColor','width','maxWidth','height','minHeight','gridTemplateColumns','gap','padding','margin','border','borderRadius','backgroundSize','backgroundPosition','transition','outline','textDecoration'];
      const read = el => { const c = getComputedStyle(el), r = el.getBoundingClientRect(); return {tag:el.tagName,text:el.textContent.trim(),rect:{x:r.x,y:r.y,width:r.width,height:r.height},style:Object.fromEntries(props.map(p=>[p,c[p]]))}; };
      return {width:innerWidth,height:document.documentElement.scrollHeight,header:read(document.querySelector('[data-pa-part="header"]')),sections:[...document.querySelectorAll('#top > section')].map((el,index)=>({index,...read(el),children:[...el.querySelectorAll('*')].map(read)})),footer:read(document.querySelector('footer')),fonts:[...document.fonts].map(f=>({family:f.family,status:f.status}))};
    })()`);
    const reference = JSON.parse(await readFile(join(resolve(argument('--baseline')), `${width}.json`), 'utf8'));
    const differences = [];
    for (const [name, actual, expected] of [['header', report.header, reference.header], ...report.sections.map((s, i) => [`section-${i}`, s, reference.sections[i]]), ['footer', report.footer, reference.footer]]) {
      for (const key of ['x','y','width','height']) if (Math.abs(actual.rect[key] - expected.rect[key]) > 0.02) differences.push({name, property:key, expected:expected.rect[key], actual:actual.rect[key]});
      for (const key of Object.keys(actual.style)) if (actual.style[key] !== expected.style[key]) differences.push({name, property:key, expected:expected.style[key], actual:actual.style[key]});
      // Interpolation-only spans are a reference implementation detail; compare real elements.
      // Closed answers are hidden in PHP rather than mounted by the artifact runtime.
      const elements = s => s.children?.filter(c => c.style.display !== 'none' && !(c.tag === 'SPAN' && !c.children && c.style.display === 'inline')) ?? [];
      const children = elements(actual), sourceChildren = elements(expected);
      if (children.length !== sourceChildren.length) differences.push({name,property:'elementCount',expected:sourceChildren.length,actual:children.length});
      if (children.length === sourceChildren.length) children.forEach((child, index) => {
        const source = sourceChildren[index];
        for (const key of ['x','y','width','height']) if (Math.abs(child.rect[key]-source.rect[key]) > 0.02) differences.push({name:`${name}/${index}-${child.tag}`,property:key,expected:source.rect[key],actual:child.rect[key]});
        for (const key of Object.keys(child.style)) if (child.style[key] !== source.style[key]) differences.push({name:`${name}/${index}-${child.tag}`,property:key,expected:source.style[key],actual:child.style[key]});
      });
    }
    const shot = await page.call('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true, clip: { x: 0, y: 0, width, height: report.height, scale: 1 } });
    await writeFile(join(output, `${width}.png`), Buffer.from(shot.data, 'base64'));
    await writeFile(join(output, `${width}.json`), JSON.stringify({report,differences}, null, 2));
    reports.push({width,height:report.height,expectedHeight:reference.height,differences:differences.length,first:differences.slice(0,10),fonts:report.fonts});
  }
} finally { await chrome.close(); }
console.log(JSON.stringify(reports, null, 2));
if (reports.some(report => report.differences || report.height !== report.expectedHeight)) process.exitCode = 1;
