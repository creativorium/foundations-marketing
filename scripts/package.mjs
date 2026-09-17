/** Build an independent design release. PHP zips files; Node compiles scoped assets. */
import fs from 'node:fs/promises';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
import {execFileSync} from 'node:child_process';
import {createHash,randomUUID} from 'node:crypto';
import {build} from 'vite';
const root=fileURLToPath(new URL('../',import.meta.url));
const slug=process.argv[2];
if(!slug || !/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug)) throw new Error('Usage: npm run package -- <design-slug>');
const exists=async p=>!!(await fs.stat(p).catch(()=>null));
const source=path.join(root,slug==='base-three-page'?'fixtures':'plugin/src/templates',slug);
const meta=JSON.parse(await fs.readFile(path.join(source,'template.json'),'utf8'));
meta.settings=Object.fromEntries(Object.entries(meta.settings||{}).filter(([k,v])=>!k.startsWith('$')&&v&&typeof v==='object'));
meta.version ||= '0.1.0';
const output=path.join(root,'dist-themes',slug,randomUUID());
const theme=path.join(output,'theme',`foundations-${slug}`), plugin=path.join(output,'plugin','foundations-site');
await fs.mkdir(output,{recursive:true});
await fs.cp(path.join(root,'theme-customer-base'),theme,{recursive:true});
await fs.cp(path.join(root,'plugin-site'),plugin,{recursive:true});
for(const name of ['theme.json','parts','assets']) if(await exists(path.join(source,name))) await fs.cp(path.join(source,name),path.join(theme,name),{recursive:true});
const themeFile=path.join(theme,'style.css');
await fs.writeFile(themeFile,(await fs.readFile(themeFile,'utf8')).replace(/Theme Name:.*$/m,`Theme Name: ${meta.name}`).replace(/Version:.*$/m,`Version: ${meta.version}`).replace(/^Foundations Template:.*$/m,`Foundations Template: ${slug} ${meta.version}`).replace(/^Foundations Blocks Runtime:.*$/m,'Foundations Blocks Runtime: 0.1.0'));
await fs.writeFile(path.join(plugin,'template.json'),JSON.stringify(meta,null,2));
const helpers=(await fs.readFile(path.join(root,'plugin/inc/helpers.php'),'utf8')).replaceAll('\r\n','\n');
await fs.writeFile(path.join(plugin,'inc/helpers.php'),helpers.slice(0,helpers.indexOf('/**\n * The site templates')));
let bootstrap=await fs.readFile(path.join(plugin,'foundations-site.php'),'utf8');
bootstrap=bootstrap.replace("require_once FM_SITE_DIR . 'inc/bundle.php';","require_once FM_SITE_DIR . 'inc/bundle.php';\nrequire_once FM_SITE_DIR . 'inc/helpers.php';");
await fs.writeFile(path.join(plugin,'foundations-site.php'),bootstrap);
const dirs=await fs.readdir(path.join(source,'blocks'),{withFileTypes:true}).catch(()=>[]);
const editor=[], frontend=[];
for(const dir of dirs.filter(d=>d.isDirectory())) {
 const from=path.join(source,'blocks',dir.name);
 const metadata=JSON.parse(await fs.readFile(path.join(from,'block.json'),'utf8'));
 if(!metadata.name.startsWith(`foundations/${slug}-`)) throw new Error(`Block ${metadata.name} must be namespaced to ${slug}`);
 await fs.cp(from,path.join(plugin,'blocks',dir.name),{recursive:true,filter:p=>!/[.](jsx|scss)$/.test(p)});
 editor.push(`import ${JSON.stringify(path.join(from,'index.js').replaceAll('\\','/'))};`);
 if(await exists(path.join(from,'style.scss'))) frontend.push(`import ${JSON.stringify(path.join(from,'style.scss').replaceAll('\\','/'))};`);
 if(await exists(path.join(from,'view.js'))) frontend.push(`import ${JSON.stringify(path.join(from,'view.js').replaceAll('\\','/'))};`);
}
if(await exists(path.join(source,'style.scss'))) frontend.push(`import ${JSON.stringify(path.join(source,'style.scss').replaceAll('\\','/'))};`);
const globals={'@wordpress/blocks':'wp.blocks','@wordpress/block-editor':'wp.blockEditor','@wordpress/components':'wp.components','@wordpress/element':'wp.element','@wordpress/i18n':'wp.i18n','@wordpress/data':'wp.data','@wordpress/server-side-render':'wp.serverSideRender'};
for(const [name,lines] of [['editor',editor],['frontend',frontend]]) {
 const entry=path.join(output,`${name}-entry.js`);await fs.writeFile(entry,lines.join('\n')||'export {};');
 await build({configFile:false,root,esbuild:{jsxFactory:'wp.element.createElement',jsxFragment:'wp.element.Fragment'},build:{emptyOutDir:false,outDir:path.join(plugin,'build'),assetsInlineLimit:0,cssCodeSplit:false,lib:{entry,name:'Foundations'+name,formats:['iife'],fileName:()=>name+'.js'},rollupOptions:{external:Object.keys(globals),output:{globals,assetFileNames:asset=>(asset.names?.[0]||asset.name||'').endsWith('.css')?name+'.css':'assets/[name]-[hash][extname]'}}}});
}
await fs.cp(path.join(source,'content'),path.join(output,'content'),{recursive:true});
const release={schema:1,id:randomUUID(),slug,name:meta.name,version:meta.version,fixture:!!meta.fixture,created_at:new Date().toISOString(),source_revision:execFileSync('git',['rev-parse','HEAD'],{cwd:root,encoding:'utf8'}).trim(),files:{}};
release.source_dirty=execFileSync('git',['status','--porcelain'],{cwd:root,encoding:'utf8'}).trim()!=='';
const contentFile=path.join(output,'content/manifest.json');const content=JSON.parse(await fs.readFile(contentFile,'utf8'));content.design=slug;content.design_version=meta.version;content.versions={base:'1.0.0',template:meta.version,runtime:'0.1.0'};await fs.writeFile(contentFile,JSON.stringify(content,null,2));
const delivery=path.join(output,'delivery');await fs.mkdir(delivery);
for(const extension of ['webp','png','jpg']) {
 const screenshot=path.join(source,'screenshot.'+extension);
 if(await exists(screenshot)) {
  const name='preview.'+extension;await fs.copyFile(screenshot,path.join(delivery,name));
  release.preview=name;release.files[name]=createHash('sha256').update(await fs.readFile(screenshot)).digest('hex');break;
 }
}
if(!meta.fixture&&!release.preview){throw new Error('Add screenshot.webp, screenshot.png or screenshot.jpg before packaging a sellable design.');}
for(const name of ['theme','plugin','content']) {
 const target=path.join(delivery,name+'.zip');execFileSync('php',[path.join(root,'scripts/zip-directory.php'),path.join(output,name),target]);
 release.files[name+'.zip']=createHash('sha256').update(await fs.readFile(target)).digest('hex');
}
await fs.writeFile(path.join(delivery,'release.json'),JSON.stringify(release,null,2));
await fs.writeFile(path.join(delivery,'INSTALL.txt'),'Install theme.zip in Appearance > Themes, then plugin.zip in Plugins. Activate both. Import content.zip using Tools > Foundations Delivery on a fresh WordPress site. The outer delivery ZIP is not itself a theme. No key or remote service is required to keep the website working.\n');
const zip=path.join(output,slug+'-delivery.zip');execFileSync('php',[path.join(root,'scripts/zip-directory.php'),delivery,zip]);
console.log('Delivery package: '+zip);
await fs.writeFile(path.join(root,'dist-themes',slug,'latest.json'),JSON.stringify({zip,output,release},null,2));
