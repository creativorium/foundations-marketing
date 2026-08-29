# Contributing

**All working rules live in [how-to-work.md](how-to-work.md).** Read that first — it
covers start-up checks, branching, push/merge permissions, how to build a component,
previewing in Local, responsiveness, speed, SEO, and the pre-commit checklist.

## Before anything else — the gate (§2)

Working with an AI assistant? Point it at `how-to-work.md` and make it answer these two
questions before it writes a line. It must ask you; it must not guess.

1. **Which GitHub account are you working under?**
   `nego94` / `creativorium` are the owner accounts and have full freedom.
   **Everyone else is a contributor** and may only touch their own block folder under
   `plugin/src/blocks/<name>/`, plus one import line in `plugin/src/editor.js` and one
   `@use` line in `plugin/src/styles/blocks.scss`. The theme, the build, the plugin
   internals and anything WooCommerce are **read-only** — the full list is in §2.1.

2. **Which of the three jobs is this?**

   | Job | What you must supply up front |
   |---|---|
   | **New component** | The name, where it appears, and **the HTML file**. No HTML? Then written details: structure, editable fields, states, and 375 / 820 / 1440px behaviour. |
   | **Fix an existing component** | Which block, exactly what is wrong (page on Local, breakpoint, browser, screenshot), and what it should do instead. |
   | **Site template** | Which template, **the HTML file**, its target SEO phrase, and where the owner wants the files to go. |

   Work on the main website itself — pages, packages, checkout, account, anything
   server-side — is **owner-only**.

## Quick version

1. `git pull origin main`, then branch: `Prefix/short-kebab-description`
   (`Feat/`, `BugFix/`, `HotFix/`, `Theme/`, `Refactor/`, `Chore/`, `Docs/`, `Content/`, `Security/`).
2. Components are self-contained folders in `plugin/src/blocks/<name>/`. Copy
   `section-heading/` as the pattern. Don't touch the backend.
3. `npm run build`, check it in Local at 375px / 820px / 1440px.
4. Push your branch and **open a Pull Request**. Only `nego94` / `creativorium` may push
   to `main` or merge.
