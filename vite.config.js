import { defineConfig } from 'vite';
import { resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = fileURLToPath(new URL('.', import.meta.url));

/**
 * Four independent build targets, each selected with `--mode <name>`.
 *
 *  theme    -> theme/build/main.js  + main.css      (site-wide frontend CSS/JS, no WP deps)
 *  login    -> theme/build/login.css                (branded wp-login screen)
 *  editor   -> plugin/build/editor.js + editor.css  (block editor: registers blocks; @wordpress/* externalized to wp.* globals)
 *  frontend -> plugin/build/frontend.css            (front-end styles for the blocks)
 *
 * Fixed filenames + PHP filemtime() cache-busting — no manifest, nothing for the shared
 * host to resolve. JSX compiles straight to wp.element.createElement (no React shipped).
 */
const TARGETS = {
  theme: {
    entry: resolve(root, 'theme/src/main.js'),
    name: 'foundationsTheme',
    outDir: resolve(root, 'theme/build'),
    fileBase: 'main',
    external: [],
    globals: {},
  },
  editor: {
    entry: resolve(root, 'plugin/src/editor.js'),
    name: 'foundationsEditor',
    outDir: resolve(root, 'plugin/build'),
    fileBase: 'editor',
    external: [
      '@wordpress/blocks',
      '@wordpress/block-editor',
      '@wordpress/components',
      '@wordpress/element',
      '@wordpress/i18n',
      '@wordpress/data',
      '@wordpress/server-side-render',
    ],
    globals: {
      '@wordpress/blocks': 'wp.blocks',
      '@wordpress/block-editor': 'wp.blockEditor',
      '@wordpress/components': 'wp.components',
      '@wordpress/element': 'wp.element',
      '@wordpress/i18n': 'wp.i18n',
      '@wordpress/data': 'wp.data',
      '@wordpress/server-side-render': 'wp.serverSideRender',
    },
  },
  login: {
    entry: resolve(root, 'theme/src/login.js'),
    name: 'foundationsLogin',
    outDir: resolve(root, 'theme/build'),
    fileBase: 'login',
    external: [],
    globals: {},
  },
  frontend: {
    entry: resolve(root, 'plugin/src/frontend.js'),
    name: 'foundationsFrontend',
    outDir: resolve(root, 'plugin/build'),
    fileBase: 'frontend',
    external: [],
    globals: {},
  },
};

export default defineConfig(({ mode }) => {
  const t = TARGETS[mode] ?? TARGETS.theme;

  return {
    esbuild: {
      jsx: 'transform',
      jsxFactory: 'wp.element.createElement',
      jsxFragment: 'wp.element.Fragment',
    },
    build: {
      outDir: t.outDir,
      // editor + frontend share plugin/build, so never auto-empty here —
      // the `clean` npm script wipes build dirs once before all runs.
      emptyOutDir: false,
      cssCodeSplit: false,
      // Never inline assets (fonts) as base64 — emit them as cacheable files.
      assetsInlineLimit: 0,
      target: 'es2020',
      minify: 'esbuild',
      lib: {
        entry: t.entry,
        name: t.name,
        formats: ['iife'],
        fileName: () => `${t.fileBase}.js`,
      },
      rollupOptions: {
        external: t.external,
        output: {
          globals: t.globals,
          assetFileNames: (asset) => {
            const n = asset.names?.[0] ?? asset.name ?? '';
            if (n.endsWith('.css')) return `${t.fileBase}.css`;
            return '[name][extname]';
          },
        },
      },
    },
  };
});
