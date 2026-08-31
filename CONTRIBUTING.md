# Contributing

**All working rules live in [how-to-work.md](how-to-work.md).** Read that first — it
covers start-up checks, branching, push/merge permissions, how to build a component,
previewing in Local, responsiveness, speed, SEO, and the pre-commit checklist.

## First — read the docs (§1.5)

Before you write anything, read `how-to-work.md` — §0 for what the business sells, §5 for
the file layout, §8–§10 for the constraints every change must meet.

Then open **your doc pack**. It is not in the repository — `/doc/` is gitignored by
design, so you are sent your own copy. `COMPONENTS.md` (the index of blocks that already
exist) matters on every job; a template also needs `SEO-AND-PERFORMANCE.md` for its target
phrase and the decoded design canvas in `client-html/extracted/`. Missing something? Ask
the owner and wait. Full list in §1.5.

## Before anything else — the gate (§2)

Working with an AI assistant? Point it at `how-to-work.md` and make it answer these two
questions before it writes a line. It must ask you; it must not guess.

1. **Which GitHub account are you working under?**
   `nego94` / `creativorium` are the owner accounts and have full freedom.
   **Everyone else is a contributor.** Contributors build the two things this business
   sells work from: **blocks** and **sellable site templates**. That means their own
   block folder under `plugin/src/blocks/<name>/`, their own template folder under
   `plugin/src/templates/<slug>/`, plus one import line in `plugin/src/editor.js` and one
   `@use` line in `plugin/src/styles/blocks.scss`. The theme, the build, the plugin
   internals and anything WooCommerce are **read-only** — the full list is in §2.1.

2. **Which of the three jobs is this?**

   | Job | What you must supply up front |
   |---|---|
   | **New component** | The name, where it appears, and **the HTML file**. No HTML? Then written details: structure, editable fields, states, and 375 / 820 / 1440px behaviour. |
   | **Fix an existing component** | Which block, exactly what is wrong (page on Local, breakpoint, browser, screenshot), and what it should do instead. |
   | **Site template** | Which template, **the HTML file**, its target SEO phrase, and which blocks it needs that don't exist yet. Files go in `plugin/src/templates/<slug>/` — §2.1a. |

   Work on the main website itself — pages, packages, checkout, account, anything
   server-side — is **owner-only**.

## Quick version

1. **Branch first, before the first edit** — `git pull origin main`, then
   `git checkout -b Prefix/short-kebab-description`
   (`Feat/`, `BugFix/`, `HotFix/`, `Theme/`, `Refactor/`, `Chore/`, `Docs/`, `Content/`, `Security/`).
   AI assistants: do this yourself, unprompted. A block and a template are **two**
   branches. See §2.3.
2. Components are self-contained folders in `plugin/src/blocks/<name>/`. Copy
   `section-heading/` as the pattern. Don't touch the backend.
3. Templates are self-contained mini sites in `plugin/src/templates/<slug>/` — **each one
   carries its own blocks** in `templates/<slug>/blocks/`, namespaced
   `foundations/<slug>-<name>`. The 19 blocks in `plugin/src/blocks/` are for *our*
   marketing site; don't use or edit them in a template (§2.1b).
   The deliverable is `content.blocks.txt` — **Gutenberg block markup**,
   which we import onto the client's site so it arrives editable. It is `<!-- wp:… /-->`
   comments, **not an HTML page**: if the file has a `<div>` in it, it is wrong. Missing a
   block you need? Build the block first (new component), then use it. See §2.1a and §6a.
4. Preview it on Local and get a demo link — paste the markup into the editor's Code
   editor (`Ctrl+Shift+Alt+M`), or use WP-CLI. See §6b. Put the demo URL in your PR.
5. `npm run build`, check it in Local at 375px / 820px / 1440px.
6. **Commit, push, and open the Pull Request yourself** — the work isn't done until the
   PR is open (§2.4). Then tell the owner. Only `nego94` / `creativorium` may push to
   `main` or merge; never merge your own PR.
