/**
 * php -l across every PHP file we ship.
 *
 * Why this exists: `npm run build` is four Vite targets, and Vite only sees what it is
 * pointed at. theme-customer-base/ is not a Vite target and has no build step at all, so
 * before this ran, nothing automated ever looked at it — a fatal in one of its PHP files
 * would have reached a customer site with a green build behind it.
 *
 * A missing PHP is a FAILURE, not a skip. An earlier version caught every startup error
 * and exited 0 with a console note — which meant a machine without PHP, CI included, got
 * a green build that had linted nothing. A disclaimer printed to a log nobody reads does
 * not make a false pass true.
 *
 * A contributor genuinely without PHP can opt out, but has to say so out loud:
 *
 *     FM_SKIP_PHP_LINT=1 npm run build
 *
 * The opt-out is ignored when CI is set, so it can never hide a broken build on a server.
 */

import { execFileSync, execFile } from 'node:child_process';
import { promisify } from 'node:util';
import { glob } from 'node:fs/promises';

const run = promisify(execFile);

const TARGETS = [
  'theme/**/*.php',
  'plugin/**/*.php',
  'theme-customer-base/**/*.php',
  'plugin-site/**/*.php',
  'plugin-delivery/**/*.php',
  'plugin-contact/**/*.php',
  'scripts/*.php',
  'fixtures/**/*.php',
];

const IGNORE = /[\\/](node_modules|build|vendor)[\\/]/;

const inCI = Boolean(process.env.CI);
const optedOut = process.env.FM_SKIP_PHP_LINT === '1';

try {
  execFileSync('php', ['--version'], { stdio: 'ignore' });
} catch (error) {
  // In CI the opt-out does not apply: a server that cannot run PHP must not report that
  // the PHP linted clean.
  if (optedOut && !inCI) {
    console.warn('lint:php — SKIPPED via FM_SKIP_PHP_LINT=1. No PHP file was checked.');
    process.exit(0);
  }

  console.error('lint:php — php is not available on PATH, so nothing was checked.');
  console.error(`   ${(error.message || '').split('\n')[0]}`);
  console.error('');

  if (inCI) {
    console.error('   CI must have PHP. Install it in the workflow; FM_SKIP_PHP_LINT is');
    console.error('   deliberately ignored here, because a green build that linted nothing');
    console.error('   is worse than a red one.');
  } else {
    console.error('   Install PHP 8.0+ and put it on PATH. Local ships one — its path is in');
    console.error('   doc/LOCAL-SETUP.md. To proceed without it, opt out explicitly:');
    console.error('');
    console.error('       FM_SKIP_PHP_LINT=1 npm run build');
  }

  process.exit(1);
}

const files = [];
for (const pattern of TARGETS) {
  for await (const entry of glob(pattern)) {
    if (!IGNORE.test(entry)) files.push(entry);
  }
}

const failures = [];

await Promise.all(
  files.map(async (file) => {
    try {
      await run('php', ['-l', file]);
    } catch (error) {
      failures.push(`${file}\n${(error.stdout || error.message).trim()}`);
    }
  })
);

console.log(`lint:php — checked ${files.length} file${files.length === 1 ? '' : 's'}`);

if (failures.length === 0) {
  console.log('no syntax errors');
  process.exit(0);
}

for (const failure of failures) console.error(failure);
console.error(`\n${failures.length} file${failures.length === 1 ? '' : 's'} failed to parse`);
process.exit(1);
