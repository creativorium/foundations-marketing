# Contributing

**All working rules live in [how-to-work.md](how-to-work.md).** Read that first — it
covers start-up checks, branching, push/merge permissions, how to build a component,
previewing in Local, responsiveness, speed, SEO, and the pre-commit checklist.

Quick version:

1. `git pull origin main`, then branch: `Prefix/short-kebab-description`
   (`Feat/`, `BugFix/`, `HotFix/`, `Theme/`, `Refactor/`, `Chore/`, `Docs/`, `Content/`, `Security/`).
2. Components are self-contained folders in `plugin/src/blocks/<name>/`. Copy
   `section-heading/` as the pattern. Don't touch the backend.
3. `npm run build`, check it in Local at 375px / 820px / 1440px.
4. Push your branch and **open a Pull Request**. Only `nego94` / `creativorium` may push
   to `main` or merge.
