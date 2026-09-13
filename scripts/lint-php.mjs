/**
 * php -l across every PHP file we ship.
 *
 * Why this exists: `npm run build` is four Vite targets, and Vite only sees what it is
 * pointed at. theme-customer-base/ is not a Vite target and has no build step at all, so
 * before this ran, nothing automated ever looked at it — a fatal in one of its PHP files
 * would have reached a customer site with a green build behind it.
 *
 * Skips gracefully if php is not on PATH, so a front-end contributor without PHP
 * installed is not blocked. CI and the owner's machine have it.
 */

import { execFileSync, execFile } from 'node:child_process';
import { promisify } from 'node:util';
import { glob } from 'node:fs/promises';

const run = promisify(execFile);

const TARGETS = [
  'theme/**/*.php',
  'plugin/**/*.php',
  'theme-customer-base/**/*.php',
];

const IGNORE = /[\\/](node_modules|build|vendor)[\\/]/;

try {
  execFileSync('php', ['--version'], { stdio: 'ignore' });
} catch {
  console.log('lint:php — php not found on PATH, skipping');
  console.log('   (install PHP, or rely on CI; this is not a pass)');
  process.exit(0);
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
