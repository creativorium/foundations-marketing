# Contributing — Foundations Marketing

The shareable version of the working rules. (Owner-only operational notes live outside the repo.)

## 1. One branch per task

```bash
git checkout main && git pull origin main
git checkout -b feat/my-thing
```

`feat/` new work · `fix/` bug · `theme/` a site template in the catalogue · `chore/` build/config/docs.
Never commit to `main`. Never reuse a finished branch.

## 2. Push and merge rights

- **Owner (`nego94` / `creativorium`)** — may push to `main` and merge.
- **Everyone else** — push your feature branch and **open a Pull Request**. Do not push to
  `main`, do not merge your own PR, do not force-push.

## 3. Front-end contributors: stay inside your component

The site is built from blocks. Each block is self-contained:

```
plugin/src/blocks/<block-name>/
  index.js      registration + editor controls
  render.php    server-side markup (this is what ships)
  style.scss    front-end styles
  editor.scss   editor-only styles
  block.json    metadata
```

You should never need to open `theme/functions.php`, `theme/inc/`,
`plugin-marketplace/`, `vite.config.js` or `package.json`. If a component needs data from
the backend, ask the owner for a helper function instead of querying the database.

Use the design tokens in `theme/src/styles/_tokens.scss`. Don't hardcode brand colours.

## 4. Setup

Requires **Node 20+** (your machine only — the production host runs no Node) and
**[Local](https://localwp.com)** for WordPress/PHP/MySQL.

```bash
npm install
npm run build     # required before the site has CSS/JS
npm run dev       # watch mode
```

Never edit `theme/build/` or `plugin/build/` — generated and gitignored.
The repo is linked into Local with directory junctions; ask the owner for the setup path.

## 5. Before you push

- `npm run build` passes.
- `git status` is clean of local notes, DB dumps, `.env`, client asset drops and scratch
  markdown. Those belong in `/doc/` (gitignored), never in a commit.
- Checked in the browser at your Local URL, not only in the editor.
