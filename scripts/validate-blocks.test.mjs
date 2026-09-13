/**
 * Regression tests for validate-blocks.mjs.
 *
 * Every case here is a bug that actually shipped, or a gap that actually got past the
 * linter. They are kept as tests rather than as a note in a PR because a linter is only
 * worth having if it is known to fire — and this one has already been wrong twice:
 *
 *   - Its first version compared closers against openers in two separate passes and
 *     reported all nine correct files as broken.
 *   - Its second version looked for malformed tokens with a pattern that itself required
 *     the closing braces, so an unterminated `{{page:contact|url` passed — including in a
 *     template part, where no token is allowed at all.
 *
 * Add a case whenever you add a rule.
 *
 * Run: npm run test:blocks
 */

import { checkText } from './validate-blocks.mjs';

const PAGE = 'fixtures/x/content/pages/home.blocks.txt';
const PART = 'theme-customer-base/parts/header.html';

/** @type {Array<{name: string, file: string, text: string, expect: string|null}>} */
const CASES = [
  // ---------------------------------------------------------------- good ---
  {
    name: 'clean page passes',
    file: PAGE,
    text: [
      '<!-- wp:group {"tagName":"main","anchor":"fm-content","className":"fm-main","layout":{"type":"constrained"}} -->',
      '<main class="wp-block-group fm-main" id="fm-content">',
      '<!-- wp:heading {"level":1} -->',
      '<h1 class="wp-block-heading">Title</h1>',
      '<!-- /wp:heading -->',
      '</main>',
      '<!-- /wp:group -->',
    ].join('\n'),
    expect: null,
  },
  {
    name: 'well-formed list passes',
    file: PAGE,
    text: [
      '<!-- wp:list -->',
      '<ul class="wp-block-list"><!-- wp:list-item -->',
      '<li>One</li>',
      '<!-- /wp:list-item --></ul>',
      '<!-- /wp:list -->',
    ].join('\n'),
    expect: null,
  },
  {
    name: 'well-formed tokens pass in a page',
    file: PAGE,
    text: '<!-- wp:paragraph --><p><a href="{{page:contact|url}}">{{media:logo.png|id}}</a></p><!-- /wp:paragraph -->',
    expect: null,
  },
  {
    name: 'nested attribute JSON is not mistaken for a token',
    file: PAGE,
    text: [
      '<!-- wp:group {"style":{"spacing":{"padding":{"top":"1rem"}}}} -->',
      '<div class="wp-block-group"></div>',
      '<!-- /wp:group -->',
    ].join('\n'),
    expect: null,
  },

  // ------------------------------------------- the three original bugs ---
  {
    name: 'id without a declared anchor',
    file: PAGE,
    text: '<!-- wp:group {"tagName":"main"} -->\n<main id="fm-content" class="wp-block-group">\n</main>\n<!-- /wp:group -->',
    expect: 'does not declare "anchor"',
  },
  {
    name: 'class without a declared className',
    file: PAGE,
    text: '<!-- wp:group {"tagName":"main"} -->\n<main class="wp-block-group fm-main">\n</main>\n<!-- /wp:group -->',
    expect: 'does not declare in "className"',
  },
  {
    name: 'foreign data- attribute on a core block',
    file: PAGE,
    text: '<!-- wp:image {"sizeSlug":"large"} -->\n<figure class="wp-block-image size-large" data-fm-media-id="3"></figure>\n<!-- /wp:image -->',
    expect: 'no block save() emits this',
  },
  {
    name: 'bare li outside wp:list-item',
    file: PAGE,
    text: '<!-- wp:list -->\n<ul class="wp-block-list">\n<li>One</li>\n</ul>\n<!-- /wp:list -->',
    expect: 'not inside a wp:list-item block',
  },

  // ------------------------------------------------------------ balance ---
  {
    name: 'unclosed block',
    file: PAGE,
    text: '<!-- wp:group -->\n<div class="wp-block-group">',
    expect: 'is never closed',
  },
  {
    name: 'mismatched closer',
    file: PAGE,
    text: '<!-- wp:group -->\n<div class="wp-block-group">\n<!-- /wp:paragraph -->',
    expect: 'but wp:group is open',
  },
  {
    name: 'closer with nothing open',
    file: PAGE,
    text: '<!-- /wp:group -->',
    expect: 'with nothing open',
  },
  {
    name: 'malformed attribute JSON',
    file: PAGE,
    text: '<!-- wp:group {"tagName":} -->\n<div></div>\n<!-- /wp:group -->',
    expect: 'malformed attribute JSON',
  },

  // -------------------------------------------------------------- tokens ---
  {
    name: 'malformed token WITH closing braces',
    file: PAGE,
    text: '<!-- wp:paragraph --><p>{{page:contact}}</p><!-- /wp:paragraph -->',
    expect: 'malformed import token',
  },
  {
    // The gap that got past the second version of the linter.
    name: 'UNTERMINATED token in a page',
    file: PAGE,
    text: '<!-- wp:paragraph --><p>{{page:contact|url</p><!-- /wp:paragraph -->',
    expect: 'malformed import token',
  },
  {
    name: 'unknown token kind',
    file: PAGE,
    text: '<!-- wp:paragraph --><p>{{post:contact|url}}</p><!-- /wp:paragraph -->',
    expect: 'malformed import token',
  },
  {
    name: 'well-formed token in a template part is still rejected',
    file: PART,
    text: '<!-- wp:foundations/site-header /-->\n<p>{{page:contact|url}}</p>',
    expect: 'in a template part',
  },
  {
    // Same gap, and the worse half: a part ships to the customer as-is.
    name: 'UNTERMINATED token in a template part is rejected',
    file: PART,
    text: '<!-- wp:foundations/site-header /-->\n<p>{{page:contact|url</p>',
    expect: 'in a template part',
  },
];

let failed = 0;

for (const testCase of CASES) {
  const problems = checkText(testCase.file, testCase.text);
  const found = problems.map((p) => p.message).join(' | ');

  if (testCase.expect === null) {
    if (problems.length !== 0) {
      console.error(`FAIL  ${testCase.name}\n      expected no problems, got: ${found}`);
      failed += 1;
      continue;
    }
  } else if (!found.includes(testCase.expect)) {
    console.error(
      `FAIL  ${testCase.name}\n      expected a problem containing "${testCase.expect}"\n` +
        `      got: ${found || '(none)'}`
    );
    failed += 1;
    continue;
  }

  console.log(`pass  ${testCase.name}`);
}

console.log(`\ntest:blocks — ${CASES.length - failed}/${CASES.length} passed`);

if (failed > 0) {
  console.error(`${failed} test${failed === 1 ? '' : 's'} failed`);
  process.exit(1);
}
