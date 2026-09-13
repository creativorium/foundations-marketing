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
 * Every rule here has a case in validate-blocks.test.mjs. Add one when you add a rule —
 * an untested linter is worse than no linter, because it is believed.
 *
 * Run: npm run validate:blocks   ·   Test: npm run test:blocks
 */

import { readFileSync } from 'node:fs';
import { glob } from 'node:fs/promises';
import { pathToFileURL } from 'node:url';

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

/**
 * The complete token grammar, anchored so it must match from the `{{` onward.
 *
 * Anchoring is the whole point. An earlier version searched for a malformed token with a
 * pattern that itself required the closing `}}`, so an UNTERMINATED token —
 * `{{page:contact|url` with no braces — matched nothing and passed, including inside a
 * template part where no token is allowed at all. Every `{{` is now checked against this;
 * anything that does not match completely is a problem.
 */
const TOKEN_AT = /^\{\{(media|page):([^|{}\s]+)\|(url|id)\}\}/;

/** Is this file a template part? Parts are rendered as-is and never pass the importer. */
function isTemplatePart(file) {
  return /[\\/]parts[\\/]/.test(file);
}

function lineOf(text, index) {
  return text.slice(0, index).split('\n').length;
}

/** Parse the attribute JSON out of an opening block comment. null means malformed. */
function parseAttrs(raw) {
  if (!raw || !raw.trim()) return {};
  try {
    return JSON.parse(raw);
  } catch {
    return null;
  }
}

/**
 * Lint one file's contents.
 *
 * Exported and pure so the test suite can drive it with strings instead of fixtures on
 * disk.
 *
 * @returns {Array<{file: string, line: number, message: string}>}
 */
export function checkText(file, text) {
  const problems = [];
  const report = (line, message) => problems.push({ file, line, message });

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
        report(line, `closing /wp:${name} with nothing open`);
      } else if (top.name !== name) {
        report(line, `closing /wp:${name} but wp:${top.name} is open (line ${top.line})`);
      }
      continue;
    }

    const attrs = parseAttrs(rawAttrs);

    if (attrs === null) {
      report(line, `block comment for wp:${name} has malformed attribute JSON`);
      continue;
    }

    if (!selfClosing) stack.push({ name, line });

    // --- 1 & 2. declared vs written attributes ----------------------------
    const after = text.slice(m.index + full.length);
    const el = after.match(/^\s*<([a-z0-9]+)([^>]*)>/i);

    if (!el) continue;

    const [, tag, attrString] = el;

    const idAttr = attrString.match(/\sid="([^"]*)"/);
    if (idAttr && attrs.anchor !== idAttr[1]) {
      report(
        line,
        `<${tag} id="${idAttr[1]}"> but wp:${name} does not declare "anchor":"${idAttr[1]}" — ` +
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
          line,
          `<${tag}> carries class ${extra.map((c) => `"${c}"`).join(', ')} that wp:${name} ` +
            `does not declare in "className" — add it, or the class is lost on save`
        );
      }
    }

    const foreign = [...attrString.matchAll(/\s(data-[a-z0-9-]+)="/g)].map((x) => x[1]);
    if (foreign.length) {
      report(line, `<${tag}> carries ${foreign.join(', ')} — no block save() emits this`);
    }
  }

  for (const left of stack) {
    report(left.line, `wp:${left.name} is never closed`);
  }

  // --- 3. bare list items -------------------------------------------------
  for (const m of text.matchAll(/<li[\s>]/g)) {
    const before = text.slice(0, m.index);
    const lastOpen = before.lastIndexOf('<!-- wp:list-item');
    const lastClose = before.lastIndexOf('<!-- /wp:list-item');
    if (lastOpen === -1 || lastOpen < lastClose) {
      report(
        lineOf(text, m.index),
        '<li> is not inside a wp:list-item block — this is the pre-6.0 list shape and ' +
          'Gutenberg migrates it on open'
      );
    }
  }

  // --- 5. tokens ----------------------------------------------------------
  // Check EVERY `{{`, not just things that already look like tokens. A pattern that
  // requires the closing braces cannot see an unterminated one.
  const part = isTemplatePart(file);

  for (const m of text.matchAll(/\{\{/g)) {
    const line = lineOf(text, m.index);
    const rest = text.slice(m.index);
    const matched = rest.match(TOKEN_AT);
    const excerpt = rest.slice(0, 40).split('\n')[0];

    if (part) {
      // Parts are never imported, so a token here ships to the customer literally —
      // whether it is well-formed or not.
      report(
        line,
        `import token \`${matched ? matched[0] : excerpt}\` in a template part — parts are ` +
          `not imported, so it would render literally`
      );
      continue;
    }

    if (!matched) {
      report(
        line,
        `malformed import token near \`${excerpt}\` — expected {{media:<file>|url}}, ` +
          `{{media:<file>|id}}, {{page:<slug>|url}} or {{page:<slug>|id}}`
      );
    }
  }

  return problems;
}

/** Read and lint one file from disk. */
export function checkFile(file) {
  return checkText(file, readFileSync(file, 'utf8'));
}

async function main() {
  const files = [];
  for (const pattern of TARGETS) {
    for await (const entry of glob(pattern)) files.push(entry);
  }

  if (files.length === 0) {
    console.log('validate:blocks — no block markup found to check');
    return 0;
  }

  const problems = files.sort().flatMap((file) => checkFile(file));

  console.log(`validate:blocks — checked ${files.length} file${files.length === 1 ? '' : 's'}`);

  if (problems.length === 0) {
    console.log('no hazards found');
    console.log('\nThis is a hazard lint, NOT a Gutenberg validator. Still open each page in');
    console.log('the editor, save, and reload to confirm there are no validation errors.');
    return 0;
  }

  for (const p of problems) console.error(`${p.file}:${p.line}  ${p.message}`);
  console.error(`\n${problems.length} problem${problems.length === 1 ? '' : 's'} found`);
  return 1;
}

// Only run when invoked directly, so the test suite can import checkText().
if (process.argv[1] && import.meta.url === pathToFileURL(process.argv[1]).href) {
  process.exit(await main());
}
