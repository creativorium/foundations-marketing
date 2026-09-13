/**
 * Block markup hazard lint.
 *
 * WHAT THIS IS NOT: a Gutenberg validator. Real validation means running each block's
 * save() and comparing it to the stored markup, which needs the editor. The only true
 * test is still "open it in Gutenberg, save, reload, see no validation warning" — that
 * check stays in the acceptance list and this script does not replace it.
 *
 * WHAT THIS IS: a catcher for the specific ways hand-written block markup goes wrong,
 * every one of which has already happened in this repo:
 *
 *   1. An attribute in the markup that the block comment never declared — `id=` without
 *      `anchor`, an extra class without `className`. The editor regenerates save output
 *      from the comment, so the attribute vanishes or the block is marked invalid.
 *   2. A foreign attribute on a core block (`data-*`), which no core save() emits.
 *   3. Bare <li> instead of core/list-item inner blocks — the pre-6.0 shape, silently
 *      migrated on open, producing a diff nobody asked for.
 *   4. Unbalanced block comments.
 *   5. A malformed import token, or a token in a file that should have none.
 *
 * Run: npm run validate:blocks
 */

import { readFileSync } from 'node:fs';
import { glob } from 'node:fs/promises';

const TARGETS = [
  'theme-customer-base/templates/*.html',
  'theme-customer-base/parts/*.html',
  'fixtures/*/content/pages/*.blocks.txt',
  'plugin/src/templates/*/content/pages/*.blocks.txt',
  'plugin/src/templates/*/parts/*.html',
];

/**
 * Classes WordPress generates itself from block attributes. Anything left over after
 * these are stripped had to come from `className`, or it will not survive a save.
 */
const GENERATED_CLASS = [
  /^wp-block-[a-z0-9-]+$/,
  /^wp-element-[a-z0-9-]+$/,
  /^wp-image-\d+$/,
  /^has-[a-z0-9-]+$/,
  /^is-[a-z0-9-]+$/,
  /^align(wide|full|left|right|center|none)$/,
  /^size-[a-z0-9-]+$/,
  /^items-justified-[a-z]+$/,
  /^screen-reader-text$/,
];

const TOKEN = /\{\{(media|page):([^|}]+)\|(url|id)\}\}/g;
const MALFORMED_TOKEN = /\{\{(?!(?:media|page):[^|}]+\|(?:url|id)\}\})[^}]*\}\}/g;

const problems = [];

function report(file, line, message) {
  problems.push({ file, line, message });
}

function lineOf(text, index) {
  return text.slice(0, index).split('\n').length;
}

/** Parse the attribute JSON out of an opening block comment, tolerating none. */
function parseAttrs(raw) {
  if (!raw || !raw.trim()) return {};
  try {
    return JSON.parse(raw);
  } catch {
    return null; // signalled to the caller as malformed
  }
}

function checkFile(file) {
  const text = readFileSync(file, 'utf8');

  // --- 4. comment balance -------------------------------------------------
  // One ordered pass over openers AND closers. Walking them in separate passes compares
  // a closer against whatever opener happens to be last in the file, which reports every
  // well-formed document as broken.
  const DELIMITER = /<!--\s*(\/)?wp:([a-z0-9-]+\/?[a-z0-9-]*)\s*(\{[\s\S]*?\})?\s*(\/)?-->/g;
  const stack = [];

  for (const m of text.matchAll(DELIMITER)) {
    const [full, isCloser, name, rawAttrs, selfClosing] = m;
    const line = lineOf(text, m.index);

    if (isCloser) {
      const top = stack.pop();
      if (!top) {
        report(file, line, `closing /wp:${name} with nothing open`);
      } else if (top.name !== name) {
        report(file, line, `closing /wp:${name} but wp:${top.name} is open (line ${top.line})`);
      }
      continue;
    }

    if (parseAttrs(rawAttrs) === null) {
      report(file, line, `block comment for wp:${name} has malformed attribute JSON`);
      continue;
    }

    if (!selfClosing) stack.push({ name, line });

    // --- 1 & 2. declared vs written attributes ----------------------------
    const attrs = parseAttrs(rawAttrs) ?? {};
    const after = text.slice(m.index + full.length);
    const el = after.match(/^\s*<([a-z0-9]+)([^>]*)>/i);

    if (!el) continue;

    const [, , attrString] = el;

    const idAttr = attrString.match(/\sid="([^"]*)"/);
    if (idAttr && attrs.anchor !== idAttr[1]) {
      report(
        file,
        line,
        `<${el[1]} id="${idAttr[1]}"> but wp:${name} does not declare "anchor":"${idAttr[1]}" — ` +
          `the editor regenerates this element and the id is lost`
      );
    }

    const classAttr = attrString.match(/\sclass="([^"]*)"/);
    if (classAttr) {
      const declared = String(attrs.className ?? '').split(/\s+/).filter(Boolean);
      const extra = classAttr[1]
        .split(/\s+/)
        .filter(Boolean)
        .filter((c) => !GENERATED_CLASS.some((re) => re.test(c)))
        .filter((c) => !declared.includes(c));

      if (extra.length) {
        report(
          file,
          line,
          `<${el[1]}> carries class ${extra.map((c) => `"${c}"`).join(', ')} that wp:${name} ` +
            `does not declare in "className" — add it, or the class is lost on save`
        );
      }
    }

    const foreign = [...attrString.matchAll(/\s(data-[a-z0-9-]+)="/g)].map((x) => x[1]);
    if (foreign.length && name.startsWith('core/') === false && name.includes('/')) {
      // Our own server-rendered blocks emit nothing, so a data- attribute here is
      // hand-written markup regardless of which block it sits under.
      report(file, line, `<${el[1]}> carries ${foreign.join(', ')} — no block save() emits this`);
    } else if (foreign.length) {
      report(file, line, `<${el[1]}> carries ${foreign.join(', ')} — no core block save() emits this`);
    }
  }

  for (const left of stack) {
    report(file, left.line, `wp:${left.name} is never closed`);
  }

  // --- 3. bare list items -------------------------------------------------
  for (const m of text.matchAll(/<li[\s>]/g)) {
    const before = text.slice(0, m.index);
    const lastOpen = before.lastIndexOf('<!-- wp:list-item');
    const lastClose = before.lastIndexOf('<!-- /wp:list-item');
    if (lastOpen === -1 || lastOpen < lastClose) {
      report(
        file,
        lineOf(text, m.index),
        '<li> is not inside a wp:list-item block — this is the pre-6.0 list shape and ' +
          'Gutenberg migrates it on open'
      );
    }
  }

  // --- 5. tokens ----------------------------------------------------------
  for (const m of text.matchAll(MALFORMED_TOKEN)) {
    report(
      file,
      lineOf(text, m.index),
      `malformed import token ${m[0]} — expected {{media:<file>|url}}, {{media:<file>|id}}, ` +
        `{{page:<slug>|url}} or {{page:<slug>|id}}`
    );
  }

  // A template part is rendered as-is and never goes through the importer, so a token
  // in one would ship to the customer literally.
  if (/\/parts\//.test(file.replace(/\\/g, '/'))) {
    for (const m of text.matchAll(TOKEN)) {
      report(file, lineOf(text, m.index), `import token ${m[0]} in a template part — parts are not imported, so it would render literally`);
    }
  }
}

const files = [];
for (const pattern of TARGETS) {
  for await (const entry of glob(pattern)) files.push(entry);
}

if (files.length === 0) {
  console.log('validate:blocks — no block markup found to check');
  process.exit(0);
}

for (const file of files.sort()) checkFile(file);

console.log(`validate:blocks — checked ${files.length} file${files.length === 1 ? '' : 's'}`);

if (problems.length === 0) {
  console.log('no hazards found');
  console.log('\nThis is a hazard lint, NOT a Gutenberg validator. Still open each page in');
  console.log('the editor, save, and reload to confirm there are no validation errors.');
  process.exit(0);
}

for (const p of problems) {
  console.error(`${p.file}:${p.line}  ${p.message}`);
}

console.error(`\n${problems.length} problem${problems.length === 1 ? '' : 's'} found`);
process.exit(1);
