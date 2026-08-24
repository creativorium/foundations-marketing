/**
 * Generates the static server error pages into theme/errors/.
 *
 * WHY THESE ARE STATIC
 * --------------------
 * WordPress can only render a 404 — for that, PHP is running and the theme loads
 * normally (see theme/404.php). A 500, 502 or 503 means PHP or the database is *not*
 * available, so nothing WordPress-shaped can render. The web server has to serve a
 * plain file it can read from disk without executing anything.
 *
 * That means these pages must also be entirely self-contained: no external stylesheet,
 * no webfont, no image. If the server is unwell, every extra request is another chance
 * to fail. So the CSS is inlined and the type falls back to the system stack.
 *
 * Run as part of `npm run build`. Edit this file, not the generated HTML.
 */
import { mkdirSync, writeFileSync } from 'node:fs';
import { resolve, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const outDir = resolve(root, 'theme/errors');

const BRAND = 'FOUNDATIONS';
const BRAND_FULL = 'Foundations Marketing';
const SITE = 'https://foundationsmarketing.co.uk';

/**
 * Steel palette, matching theme/src/styles/_tokens.scss. Duplicated deliberately:
 * these files cannot import anything at runtime.
 */
const C = {
  bg: '#EBEBDF',
  ink: '#1F1E1A',
  muted: '#5E5D53',
  accent: '#EA631B',
  accentDeep: '#C24E11',
};

const css = `
*,*::before,*::after{box-sizing:border-box}
html{-webkit-text-size-adjust:100%}
body{margin:0;background:${C.bg};color:${C.ink};
  font-family:Archivo,'Helvetica Neue',Helvetica,Arial,sans-serif;
  -webkit-font-smoothing:antialiased;
  display:flex;flex-direction:column;min-height:100vh}
a{color:inherit;text-decoration:none}
a:hover{text-decoration:underline}
:focus-visible{outline:2px solid ${C.accent};outline-offset:3px}
.hdr{display:flex;align-items:center;justify-content:space-between;gap:24px;
  min-height:74px;padding:0 40px;border-bottom:1.5px solid ${C.ink}}
.logo{font-size:19px;font-weight:800;letter-spacing:-.035em;text-transform:uppercase;color:${C.accent}}
.main{flex:1;padding:96px 40px 120px}
.rule{display:flex;justify-content:space-between;align-items:baseline;gap:24px;
  padding-bottom:12px;border-bottom:1.5px solid ${C.ink};
  font-size:10px;font-weight:700;letter-spacing:.18em;text-transform:uppercase}
.rule span+span{color:${C.accent}}
h1{margin:48px 0 26px;font-size:clamp(44px,7vw,104px);line-height:.88;font-weight:800;
  letter-spacing:-.045em;text-transform:uppercase;text-wrap:pretty}
h1 span{display:block;color:${C.accent}}
p{margin:0 0 32px;font-size:18px;line-height:1.6;color:${C.muted};max-width:52ch;text-wrap:pretty}
.btn{display:inline-block;background:${C.accent};color:#fff;padding:17px 34px;
  border-radius:40px;font-size:14px;font-weight:700}
.btn:hover{background:${C.accentDeep};text-decoration:none;color:#fff}
.ftr{padding:28px 40px;border-top:1.5px solid ${C.ink};
  font-size:11px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:${C.muted}}
@media(max-width:820px){.hdr,.main,.ftr{padding-left:20px;padding-right:20px}
  .main{padding-top:64px;padding-bottom:80px}}
`.trim().replace(/\n\s*/g, '');

/**
 * @type {Array<{code:string,file:string,label:string,title:string,accent:string,body:string}>}
 */
const PAGES = [
  {
    code: '403',
    file: '403.html',
    label: 'Forbidden',
    title: 'This door',
    accent: 'is locked',
    body: 'You do not have permission to view this page. If you think you should, get in touch and we will sort it out.',
  },
  {
    code: '500',
    file: '500.html',
    label: 'Server error',
    title: 'Something',
    accent: 'broke here',
    body: 'This one is on us, not on you. The problem has been logged. Try again in a minute — and if it keeps happening, tell us.',
  },
  {
    code: '502',
    file: '502.html',
    label: 'Bad gateway',
    title: 'No answer',
    accent: 'upstream',
    body: 'The server did not get a valid response from the service behind it. This is usually brief. Refreshing in a moment normally works.',
  },
  {
    code: '503',
    file: '503.html',
    label: 'Back shortly',
    title: 'Down for',
    accent: 'a moment',
    body: 'We are doing a short piece of maintenance. Nothing is lost and everything will be exactly where you left it. Please try again in a few minutes.',
  },
  {
    code: '504',
    file: '504.html',
    label: 'Gateway timeout',
    title: 'That took',
    accent: 'too long',
    body: 'The server took too long to respond and gave up waiting. Try again — it usually goes through on a second attempt.',
  },
];

const page = ({ code, label, title, accent, body }) => `<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>${code} — ${label} · ${BRAND_FULL}</title>
<!-- An error page must never be indexed. -->
<meta name="robots" content="noindex,nofollow">
<style>${css}</style>
</head>
<body>
<header class="hdr">
  <a class="logo" href="${SITE}/">${BRAND} ${BRAND_FULL.split(' ')[1].toUpperCase()}</a>
</header>
<main class="main">
  <div class="rule"><span>Error ${code}</span><span>${label}</span></div>
  <h1>${title} <span>${accent}</span></h1>
  <p>${body}</p>
  <a class="btn" href="${SITE}/">Back to the homepage &rarr;</a>
</main>
<footer class="ftr">&copy; ${new Date().getFullYear()} ${BRAND_FULL}</footer>
</body>
</html>
`;

mkdirSync(outDir, { recursive: true });

for (const spec of PAGES) {
  writeFileSync(resolve(outDir, spec.file), page(spec), 'utf8');
  console.log(`error page  theme/errors/${spec.file}`);
}

// A .htaccess fragment for Apache hosts (Bluehost is Apache). It is emitted rather
// than hand-written so the paths always match the files actually generated.
const htaccess = `# Foundations Marketing — error documents
# GENERATED by scripts/build-error-pages.mjs. Do not edit by hand.
#
# Paste into the site's root .htaccess, ABOVE the "# BEGIN WordPress" block so
# WordPress's rewrite rules do not swallow these paths.
#
# 404 is deliberately absent: WordPress renders it through the theme (404.php),
# which gives a searchable, linked page instead of a dead end. Let WordPress keep it.

${PAGES.map((p) => `ErrorDocument ${p.code} /wp-content/themes/foundations/errors/${p.file}`).join('\n')}

# Serve the error pages themselves without rewriting them into WordPress.
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteRule ^wp-content/themes/foundations/errors/ - [L]
</IfModule>

# A 503 should tell crawlers when to come back. Without Retry-After, a long outage
# can be read as the pages being genuinely gone rather than temporarily unavailable.
<IfModule mod_headers.c>
  <FilesMatch "503\\.html$">
    Header always set Retry-After "3600"
    Header always set Cache-Control "no-store"
  </FilesMatch>
</IfModule>
`;

writeFileSync(resolve(outDir, 'htaccess-snippet.txt'), htaccess, 'utf8');
console.log('error page  theme/errors/htaccess-snippet.txt');
