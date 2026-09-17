/** Compare the PHP-rendered blocks with the original artifact in the same Chrome build. */
import { spawnSync } from 'node:child_process';
import { mkdir, writeFile } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';
import { browser, delay } from './cdp.mjs';
const here = dirname(fileURLToPath(import.meta.url));
const arg = name => process.argv[process.argv.indexOf(name) + 1];
for (const name of ['--php','--wordpress','--chrome','--reference']) if (!process.argv.includes(name)) throw new Error(`Missing ${name}`);
const output = join(tmpdir(), 'pulse-atelier-review');
await mkdir(join(output, 'interactions'), { recursive: true });
const render = spawnSync(arg('--php'), [join(here, 'render-smoke.php'), arg('--wordpress'), '--preview'], { windowsHide: true });
if (render.status !== 0) throw new Error(render.stdout.toString() + render.stderr.toString());
await writeFile(join(output, 'index.html'), render.stdout);
const chrome = await browser(arg('--chrome'));
const results = [];
try {
  for (const width of [1440, 820, 375]) {
    const original = await chrome.page(pathToFileURL(resolve(arg('--reference'))).href, width);
    await original.ready('!!document.querySelector("#top h1") && !document.querySelector("#__bundler_loading") && document.fonts.status === "loaded"');
    const actual = await chrome.page(pathToFileURL(join(output, 'index.html')).href, width);
    await actual.ready();
    const pages = [original, actual];
    const both = async expression => { for (const page of pages) { await page.call('Page.bringToFront'); await page.evaluate(expression); await delay(200); } };
    const snapshot = async (name, selector, screenshot = false) => {
      const values = [];
      for (const [index,page] of pages.entries()) {
        await page.call('Page.bringToFront'); await delay(200);
        values.push(await page.evaluate(`(() => { const el = document.querySelector(${JSON.stringify(selector)}), r=el.getBoundingClientRect(), s=getComputedStyle(el); return {text:el.innerText,rect:[r.x,r.y,r.width,r.height],color:s.color,background:s.backgroundColor,transition:s.transition,outline:s.outline,textDecoration:s.textDecoration,scrollY,hash:location.hash}; })()`));
        if (screenshot) {
          await page.call('Page.bringToFront');
          const shot = await page.call('Page.captureScreenshot', {format:'png'});
          await writeFile(join(output,'interactions',`${width}-${name}-${index ? 'actual' : 'reference'}.png`), Buffer.from(shot.data,'base64'));
        }
      }
      values.forEach(value => { value.text = value.text.replace(/\s+/g,' ').trim(); value.rect=value.rect.map(x=>Math.round(x*100)/100); });
      if (JSON.stringify(values[0]) !== JSON.stringify(values[1])) throw new Error(`${width} ${name}: ${JSON.stringify(values)}`);
      results.push({width,state:name,pass:true});
    };
    const click = async selector => {
      for (const page of pages) {
        await page.call('Page.bringToFront');
        await page.evaluate(`document.querySelector(${JSON.stringify(selector)}).click()`);
        await delay(1400);
      }
    };
    const scrollTo = async selector => { await both(`document.querySelector(${JSON.stringify(selector)}).scrollIntoView({behavior:'instant',block:'center'})`); await delay(100); };
    const key = async (key,code,windowsVirtualKeyCode) => {
      for (const page of pages) for (const type of ['keyDown','keyUp']) await page.call('Input.dispatchKeyEvent',{type,key,code,windowsVirtualKeyCode});
      await delay(100);
    };
    await snapshot('initial', '#top');
    for (let i=0;i<3;i++) {
      const dot=`#top > section:nth-of-type(9) button:nth-child(${i+1})`;
      await scrollTo(dot); await click(dot);
      await snapshot(`quote-${i}`, '#top > section:nth-of-type(9)', true);
    }
    await both(`document.querySelector('#top > section:nth-of-type(9) button').focus()`);
    await key('Enter','Enter',13); await snapshot('quote-keyboard', '#top > section:nth-of-type(9)');
    if (!process.argv.includes('--visual-only')) {
    for (let i=0;i<5;i++) {
      await both(`document.querySelectorAll('#faq button')[${i}].click()`);
      await scrollTo('#faq'); await snapshot(`faq-${i}`, '#faq', true);
    }
    await both(`document.querySelectorAll('#faq button')[4].click()`);
    await snapshot('faq-all-closed','#faq');
    await both(`document.querySelector('#faq button').focus()`);
    await key(' ','Space',32); await snapshot('faq-keyboard','#faq');
    }
    for (const [name,selector] of [['session','#sessions > a'],['header-cta','a[href="#book"]'],['hero-cta','#top > section:first-child a'],['booking-cta','#book a']]) {
      await scrollTo(selector);
      for (const page of pages) {
        const point=await page.evaluate(`(() => {const r=document.querySelector(${JSON.stringify(selector)}).getBoundingClientRect();return {x:Math.max(1,Math.min(innerWidth-1,r.x+r.width/2)),y:Math.max(1,Math.min(innerHeight-1,r.y+r.height/2))};})()`);
        await page.call('Input.dispatchMouseEvent',{type:'mouseMoved',...point});
      }
      await snapshot(name+'-hover',selector,true);
    }
    if (process.argv.includes('--visual-only')) {
      console.log(`PASS ${width}px: ${results.filter(r=>r.width===width).length} targeted visual states`);
      continue;
    }
    await scrollTo('#top > section:nth-of-type(11)');
    await both(`document.querySelector('input[type="email"]').value='invalid email';document.querySelector('input[type="email"]').focus()`);
    await snapshot('newsletter-focus','input[type="email"]');
    await click('#top > section:nth-of-type(11) button');
    await snapshot('newsletter-click','#top > section:nth-of-type(11)',true);
    await click('#top > section:nth-of-type(11) a'); await delay(1400);
    await snapshot('privacy','footer',true);
    await click('a[href="#practice"]'); await delay(1400);
    await snapshot('return-home','#top',true);
    for (const section of ['practice','teacher','sessions','faq','book']) {
      await click(`a[href="#${section}"]`); await delay(1400);
      await snapshot('anchor-'+section,'#'+section);
    }
    await click('#book a'); await delay(1400);
    await snapshot('contact','footer',true);
    // Contact controls remain ordinary inputs/buttons, as in the source.
    await both(`document.querySelector('textarea').value='Test visitor';document.querySelector('textarea').parentElement.parentElement.querySelector('button').click()`);
    await snapshot('contact-no-submit','footer');
    await click('footer a[href="#privacy"]'); await delay(1400);
    await snapshot('footer-privacy','footer');
    await click('footer a[href="#contact"]'); await delay(1400);
    await snapshot('footer-contact','footer');
    if (process.argv.includes('--screenshot') && width === 1440) {
      await click('a[href="#top"]'); await delay(1400);
      await actual.call('Emulation.setDeviceMetricsOverride',{width:1200,height:900,deviceScaleFactor:1,mobile:false});
      const shot=await actual.call('Page.captureScreenshot',{format:'webp',quality:90});
      await writeFile(join(here,'../screenshot.webp'),Buffer.from(shot.data,'base64'));
    }
    console.log(`PASS ${width}px: ${results.filter(r=>r.width===width).length} reference interaction states`);
  }
} finally { await chrome.close(); await writeFile(join(output,process.argv.includes('--visual-only') ? 'interactions-visual.json' : 'interactions.json'),JSON.stringify(results,null,2)); }
console.log(`Preview: ${join(output,'index.html')}`);
