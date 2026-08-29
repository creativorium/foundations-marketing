# how-to-work.md

**Read this first, every time, before touching anything.**

This is the single source of truth for working on the Foundations Marketing website —
for humans and for AI assistants alike. If you are an AI assistant and someone pointed
you at this file, follow it literally: run the start-up checks in §1 before you write a
single line, and **do not skip the gate in §2** — you must establish who you are working
for and what job this is before you touch anything.

Repo: <https://github.com/creativorium/foundations-marketing.git>

---

## 0. What this project is

Foundations Marketing sells **done-for-you websites** to UK service businesses —
therapists, coaches, doulas, pilates instructors, nutritionists, salons, tutors. A buyer
picks a **site template**, buys a **package**, and we build and deploy the site for them.

Packages (WooCommerce products): **Root £199** · **Growth £349** · **Rise £480**
Add-ons: Branding kit £199 · Advanced SEO £149 · Booking integration £75 · Extra pages £50 each

> It is **not** a ThemeForest-style marketplace. Templates are *chosen*, not bought
> individually, and we do the install. An earlier brief said otherwise — it was wrong.

**The stack:** WordPress, a classic PHP theme, and a plugin of server-rendered Gutenberg
blocks, all built with **Vite**. The site ships as plain PHP/CSS/JS so it runs on Bluehost
or any shared host. **Node never runs on the server** — it only builds on your machine.

---

## 1. Start-up checks — do these before anything else

### 1.1 Sync with the remote before you branch

Never branch from a stale copy. Someone else may have merged since you last pulled.

```bash
git remote -v                  # confirm origin points at creativorium/foundations-marketing
git fetch origin
git checkout main
git pull origin main
git log --oneline -5           # confirm you are on the latest
git status                     # must be clean before you start
```

If `git remote -v` prints nothing, add it:

```bash
git remote add origin https://github.com/creativorium/foundations-marketing.git
git fetch origin
```

### 1.2 Check Local is running and the junctions are intact

The repo is **not** copied into WordPress. WordPress is pointed at it with **directory
junctions**, so editing here updates the site immediately. Verify both exist — a missing
junction is the single most common reason "my changes do nothing".

```powershell
# PowerShell (Git Bash mangles these paths)
$wp = "C:\Users\Nego\Local Sites\foundationsmarketing\app\public\wp-content"
Get-Item "$wp\themes\foundations", "$wp\plugins\foundations-blocks" |
  Select-Object Name, LinkType, Target
```

Expect `LinkType: Junction` and a `Target` pointing at this repo's `theme/` and `plugin/`.

**If they are missing, create them** (adjust `$src` to wherever you cloned):

```powershell
$src = "C:\Users\Nego\Documents\Works\Foundations Marketing"
$wp  = "C:\Users\Nego\Local Sites\foundationsmarketing\app\public\wp-content"

cmd /c mklink /J "$wp\themes\foundations"         "$src\theme"
cmd /c mklink /J "$wp\plugins\foundations-blocks" "$src\plugin"
```

> ⚠️ **To remove a junction, run `cmd /c rmdir "<the-link>"` on the link itself.**
> Never `rmdir /s` into it, and never `rm -rf` it from Git Bash — that deletes the
> **real files in your repo**, not just the link.

macOS/Linux equivalent: `ln -s "$src/theme" "$wp/themes/foundations-marketing"` etc.

Then confirm Local itself is up: open the **Local** app, start the
**foundationsmarketing** site, and load <http://foundationsmarketing.local>.
If it does not respond, nothing else in this document will work.

### 1.3 Confirm the theme and plugin are active

WP Admin → **Appearance → Themes**: *Foundations* must be active.
WP Admin → **Plugins**: *Foundations Blocks* must be active.

> Blocks are server-rendered with `save: () => null`, so the database stores **no** HTML
> for them. If the plugin is inactive, those sections render as *nothing at all* — a blank
> page usually means the plugin is off, not that your block is broken.

### 1.4 Build before you look

```bash
npm install      # first time only
npm run build    # required — without it there is no CSS or JS at all
```

### 1.5 Read the documentation before you build anything

**AI assistants: read these before you write a line.** You cannot build a block that fits
this site, or a template that sells, by looking at one HTML file and guessing the rest.
The structure, the content model, the SEO phrase each template owns and the constraints
every page must meet are all written down. Read them, then build.

**In the repository — read these, in this order:**

| Read | Why |
|---|---|
| **`how-to-work.md`** (this file) | The working rules. §0 is what the business actually sells, §5 is the file layout and how the build works, §8–§10 are the constraints every change must meet. |
| **`CONTRIBUTING.md`** | The short version of the gate and the workflow. |
| **`DEPLOYMENT.md`** | Only if you touch deployment — that is owner work. |

**Not in the repository — you are given your own copy.** `/doc/` is **gitignored** by
design, so cloning does not bring it down; contributors receive their own doc pack
separately. It holds the design source and the requirements, so **open it before you
start** — this is where lane A and lane C actually get their brief:

| File | What is in it | Needed for |
|---|---|---|
| `doc/COMPONENTS.md` | the index of every existing block, what it does, and the catalogue state | **Every job.** Check it before building anything — the block may already exist. |
| `doc/TEMPLATES.md` | the template index and the publish checklist | Lane C |
| `doc/SEO-AND-PERFORMANCE.md` | the full SEO strategy and the **keyword/URL table** — which phrase each template owns | Lane C, and §10 on any page |
| `doc/client-html/extracted/` | the client's four design-canvas pages decoded to plain HTML — **the design source of truth** | Lanes A and C |
| `doc/LOCAL-SETUP.md` | the confirmed Local path and the junction commands | Setup (§1.2) |
| `doc/FONTS.md` | how to self-host the two webfonts | Only if fonts are involved |

**Read your doc pack before the first edit.** At minimum `COMPONENTS.md`, and for a
template also `SEO-AND-PERFORMANCE.md` and the relevant file from
`client-html/extracted/`. Do not start a template without knowing its SEO phrase; do not
start a block without checking the component index first — it may already exist. If
something you need is missing from your copy, ask the owner for it and wait. Asking costs
a message; building the wrong thing costs the whole job.

Because `/doc/` is gitignored, **nothing in it is ever committed** — not your notes, not
the client HTML, not a scratch plan. See §11.

**The requirements that bind every single piece of work** — these are not optional and
not negotiable, and a PR that misses them gets sent back:

- **§8 Responsiveness** — checked at 375px, 820px and 1440px. The body never scrolls
  horizontally. Tap targets 44×44px minimum.
- **§8 Accessibility** — semantic landmarks, real `<button>`/`<a>`, visible focus,
  WCAG AA contrast, `prefers-reduced-motion` respected.
- **§9 Speed** — 85+ mobile PageSpeed. No layout JavaScript, no jQuery, no framework, no
  third webfont, no icon font. Images always carry width and height.
- **§10 SEO** — exactly one H1 per page, real alt text carrying the target phrase, clean
  URLs, and the brand is **"Foundations Marketing"**, always with the S.
- **Design tokens** — colours, spacing and type come from `--fm-*` in
  `theme/src/styles/_tokens.scss`. A hardcoded hex passes under Steel and breaks under
  Nari, so it will be caught.

---

## 2. The gate — who are you, and what are you doing?

**AI assistants: this section is a gate, not advice.** Do not create a branch, edit a
file, or run a build until you have **read the documentation in §1.5** and have a clear
answer to **Q1** and **Q2** below. Do not infer either answer from how the request is
phrased, and do not proceed on a guess.
Ask, wait for the answer, then work.

### Q1 — Which account is this?

Ask the person outright: **"Which GitHub account are you working under?"**
Then verify it rather than taking the answer on trust:

```bash
git config user.name
git config user.email
gh api user --jq .login     # if the GitHub CLI is available
```

| Account | Role | Scope |
|---|---|---|
| **`nego94`** or **`creativorium`** | Owner / main developer | **Full freedom.** No further gate — go to §3. |
| **anything else** | Front-end contributor | **Restricted.** Read §2.1, then answer Q2. |

If the person will not name an account, or the name they give does not match what
`git config` and `gh` report, **stop and ask the owner.** Never start work on an
unverified identity, and never assume owner rights because the machine happens to be
logged in as the owner.

### 2.1 What a contributor may touch — a hard limit

Contributors build **blocks and site templates**. Both, and nothing else. Templates are
the product this business sells, so building them is contributor work by design — not a
favour and not owner-only. A contributor account may create and edit files in exactly
these places:

| Allowed | What |
|---|---|
| `plugin/src/blocks/<block-name>/**` | the block's own folder — this is where component work lives |
| `plugin/src/templates/<template-slug>/**` | the template's own folder — this is where template work lives (§2.1a) |
| `plugin/src/editor.js` | **one** added `import './blocks/<name>';` line, nothing else |
| `plugin/src/styles/blocks.scss` | **one** added `@use '../blocks/<name>/style';` line, nothing else |
| `doc/COMPONENTS.md` | the index row for a new block (local only — `/doc/` is gitignored) |
| `doc/TEMPLATES.md` | the index row for a new template (local only — `/doc/` is gitignored) |

Everything else in the repository is **read-only** to a contributor. Read it to
understand the patterns; do not change it. In particular, never edit:

`theme/inc/` · `theme/functions.php` · `theme/src/styles/_tokens.scss` ·
`plugin/inc/` · `plugin/foundations-blocks.php` · `vite.config.js` ·
`package.json` / `package-lock.json` · anything under `*/build/` ·
any WooCommerce integration · `.github/` · `how-to-work.md` · `CONTRIBUTING.md` ·
`DEPLOYMENT.md`

If the work seems to require touching one of those, that is the signal that it is
**owner work, not contributor work**. Stop, explain which file you would have needed
and why, and hand it to the owner. Do not work around the limit — no editing the theme
"just this once", no adding a dependency, no changing the build.

### 2.1a Where a site template lives

**`plugin/src/templates/<template-slug>/`** — one self-contained folder per template,
sitting beside `blocks/`. This is now fixed; you no longer need to ask where it goes.

```
plugin/src/templates/<template-slug>/
  template.json     name, niche, category, target SEO phrase, demo URL slug
  content.html      the page as block markup — this is the deliverable
  screenshot.webp   catalogue image, 1200×900, compressed before it lands
  style.scss        optional, template-only styles
```

**`content.html` is the whole point.** Blocks are server-rendered with `save: () => null`,
so the database stores no markup — only a block comment and its attributes. A finished
page is therefore a few KB of plain text, which means a template is a file we can zip,
send to a client, and import into any site already running the Foundations theme and the
Foundations Blocks plugin. That is exactly the install we perform when someone buys, so
the format we build in and the format we hand over are the same thing. Do not invent a
second format for delivery.

**A template is assembled from existing blocks — it is not a new pile of HTML.** Build
the page in the editor from the blocks already in `plugin/src/blocks/`, then export the
block markup into `content.html`. If the design genuinely needs something no block can
do, that is **lane A work first**: build the block, get it merged, then use it in the
template. Never hardcode a section into a template that should have been a block, and
never hardcode a brand colour — templates use the `--fm-*` tokens like everything else,
or they break under the Nari palette.

`style.scss` is for the rare template-only tweak. It is **not** a place to restyle a
shared block; if a block looks wrong, that is lane B. Keep it empty unless you need it.

### Q2 — Which of the three jobs is this?

Ask: **"Are you creating a new component, fixing an existing one, or building a site
template?"** Each one has a different thing you must collect *before* you write code.

| Lane | Means | What you must ask for, and wait for |
|---|---|---|
| **A — New component** | A block that does not exist yet | **1.** What is it called and where does it appear? **2.** **"Send the HTML file."** Clients normally provide one — build from it, do not redesign it. **3.** If there is no HTML: written details — the structure, which parts the editor must be able to change, any states (empty, hover, loading), and what it should look like at 375px / 820px / 1440px. |
| **B — Fix / update a component** | An existing block is wrong or needs a change | **1.** **Which block** — the folder name under `plugin/src/blocks/`. **2.** **What exactly is wrong**: the page on Local, the breakpoint, the browser, and a screenshot if there is one. **3.** What it should do instead. Never "improve" a block beyond what was asked. |
| **C — Site template** | A **sellable site template** in the catalogue | **1.** **Which template** — its name and niche. **2.** **"Send the HTML file."** Same fallback as lane A if there is none. **3.** The target SEO phrase for it — the table is in `doc/SEO-AND-PERFORMANCE.md` (§10). **4.** Which blocks it needs that do not exist yet — those are lane A and come first. The files go in `plugin/src/templates/<slug>/` (§2.1a); you no longer need to ask. |

**Work on the main website itself** — pages, the packages flow, checkout, the account
area, anything server-side — is **owner-only**. If a contributor asks for that, say so
and stop.

If an answer is missing, ask for it and wait. A block built from a guess gets thrown
away, and rebuilding it costs more than the question would have.

### 2.2 Then say what you are about to do

Before the first edit, state plainly, in one or two sentences: **what you are about to
build, and which branch you will build it on.** This is how the owner catches a
misunderstanding before the work is done rather than after.

### 2.3 Branch first — AI assistants, this is on you

**Every component and every template starts on its own new branch, created before the
first file is written.** Not after the first edit, not "once it works", not at commit
time. If you are an AI assistant, you create the branch yourself as your first action
after the gate — do not wait to be told, and do not ask permission to branch. Branching
is free; unpicking a component and a template tangled together on `main` is not.

```bash
git checkout main
git pull origin main                                  # never branch from a stale copy
git checkout -b Feat/testimonial-carousel-block       # a component  → Feat/
git checkout -b Theme/halo-skincare-template          # a template   → Theme/
```

The rules that catch people out:

- **One branch = one thing.** A block and a template are two branches, even when the
  template is the reason the block exists. Build the block, open its PR, then branch
  again for the template. See §3.
- **Never work on `main`.** If you have already edited files on `main`, stop: branch now
  and carry the changes over (`git checkout -b Prefix/...` keeps your uncommitted work).
- **Never reuse a branch** from work that is already merged or already in review.
- Check where you are before the first edit — `git branch --show-current`. If it says
  `main`, you are not ready to start.

### 2.4 Finish the job — commit and open the PR yourself

Work is not done when the code works. It is done when it is **on a branch, pushed, and
waiting in a Pull Request.** AI assistants: carry it all the way there. Do not stop at
"the files are saved" and leave the owner to run the git commands.

When the build passes and you have checked it in the browser (§11):

```bash
npm run build                                # must pass before you commit
git status                                   # must be clean of /doc/, .env, dumps, notes
git add <the files you actually changed>     # never `git add -A` blindly
git commit -m "Feat: add the testimonial carousel block"
git push -u origin Feat/testimonial-carousel-block
gh pr create --base main --title "..." --body "..."
```

Then **stop and tell the owner the PR is open**, with its number or link. Write the PR
description as §12 sets out: what changed, why, how to check it, and anything you
deliberately left out.

**Contributors open the PR. They never merge it.** Pushing your own branch is expected;
pushing to `main`, merging your own PR, force-pushing, or skipping hooks is not — §4 is
the hard limit and it does not bend because the work looks finished or the change looks
small. If the push is rejected, that is the limit doing its job: report it, do not route
around it.

---

## 3. Branching

Create the branch **before the first edit**, always, no exceptions.

```bash
git checkout main
git pull origin main
git checkout -b Prefix/short-kebab-description
```

| Prefix | Use for | Example |
|---|---|---|
| `Feat/` | new feature, component/block, or page | `Feat/pricing-table-block` |
| `BugFix/` | a normal bug fix, through a PR | `BugFix/checkout-tax-rounding` |
| `HotFix/` | production is broken **right now** — smallest possible fix | `HotFix/checkout-500-error` |
| `Theme/` | a site template in the catalogue | `Theme/pulse-pilates-homepage` |
| `Refactor/` | restructuring, no behaviour change | `Refactor/blocks-shared-tokens` |
| `Chore/` | build, config, dependencies, tooling | `Chore/vite-7-upgrade` |
| `Docs/` | documentation only | `Docs/component-index` |
| `Content/` | copy, images, catalogue entries — no code | `Content/services-page-copy` |
| `Security/` | vulnerability patch, credential rotation, malware cleanup | `Security/remove-backdoor-plugin` |

**Form:** `Prefix/short-kebab-description` — prefix capitalised exactly as above,
description lowercase kebab-case, under ~50 characters. Never a bare ticket number.

Rules:
- **Never commit directly to `main`.**
- Never reuse a branch from finished work.
- One branch = one piece of work. If you find an unrelated bug, note it and open a
  separate branch — do not smuggle it into this one.
- **A block and a template are two pieces of work**, even when the template is the whole
  reason the block exists. Build the block on `Feat/`, open its PR, then branch again on
  `Theme/` for the template. A PR that adds both is a PR nobody can review or revert
  cleanly.
- `HotFix/` is the only prefix that may shortcut review, only for the owner, only when
  the live site is down — and it still gets a branch and a PR opened afterwards.

---

## 4. Who may push and merge — this is a hard limit

| You are | Feature branch | Push to `main` | Merge |
|---|---|---|---|
| **nego94 / creativorium** (owner) | yes | yes | yes |
| **anyone else** | yes | **no** | **no — open a Pull Request** |

You should already know which of these you are — §2 Q1 settles it before any work
starts. Confirm it again **before any push**, because a push is the irreversible step:

```bash
git config user.name
git config user.email
gh api user --jq .login     # if the GitHub CLI is available
```

**If the login is not `nego94` or `creativorium`:**

```bash
git push -u origin Prefix/your-branch
gh pr create --base main --title "..." --body "..."
```

Then **stop** and tell the owner a PR is waiting. Do not merge it yourself.

Never `git push origin main`. Never `--force`. Never merge a PR as a non-owner.
Never skip hooks (`--no-verify`) or bypass signing.

---

## 5. Project structure

```
Foundations Marketing/
├─ how-to-work.md            ← this file
├─ package.json              build scripts
├─ vite.config.js            four build targets
│
├─ theme/                    → junctioned to wp-content/themes/foundations-marketing
│  ├─ style.css              theme header only — never put real CSS here
│  ├─ functions.php          bootstrap; loads inc/*
│  ├─ header.php footer.php index.php page.php single.php 404.php
│  ├─ inc/
│  │  ├─ assets.php          enqueues, filemtime cache-busting
│  │  ├─ fonts.php           self-host-if-present, else Google CDN
│  │  ├─ nav.php             menu output
│  │  ├─ performance.php     the speed layer — read §9 before editing
│  │  ├─ seo.php             schema + brand constant — read §10
│  │  └─ setup.php           theme supports, menus, palette Customizer
│  ├─ src/
│  │  ├─ main.js login.js    entry points
│  │  └─ styles/             _tokens _base _layout _header _footer
│  ├─ assets/fonts/          drop woff2 here to self-host (gitignored)
│  └─ build/                 ← GENERATED. Never edit. Gitignored.
│
└─ plugin/                   → junctioned to wp-content/plugins/foundations-blocks
   ├─ foundations-blocks.php plugin header
   ├─ inc/
   │  ├─ register.php        scans src/blocks/*/block.json — auto-discovery
   │  ├─ assets.php          editor + frontend bundles
   │  └─ helpers.php         fm_wrapper() fm_image() fm_section_rule() fm_url()
   ├─ src/
   │  ├─ editor.js           one import line per block
   │  ├─ frontend.js
   │  ├─ styles/             blocks.scss (one @use per block) + _shared.scss
   │  ├─ blocks/<name>/      ← THE COMPONENTS LIVE HERE
   │  └─ templates/<slug>/   ← THE SELLABLE TEMPLATES LIVE HERE (§2.1a)
   └─ build/                 ← GENERATED. Never edit. Gitignored.
```

### How the build works

Vite has **four targets**, each `--mode`:

| Mode | Output | What |
|---|---|---|
| `theme` | `theme/build/main.{js,css}` | site-wide front end, no WP deps |
| `login` | `theme/build/login.css` | branded wp-login screen |
| `editor` | `plugin/build/editor.{js,css}` | registers blocks; `@wordpress/*` externalised to `wp.*` globals |
| `frontend` | `plugin/build/frontend.css` | block styles for the front end |

Fixed filenames, cache-busted with PHP `filemtime()` — **no manifest**, nothing for the
shared host to resolve. JSX compiles straight to `wp.element.createElement`; **no React
ships to the browser**.

```bash
npm run build     # all four, after a clean
npm run dev       # watch the theme target
npm run watch:editor
npm run watch:frontend
```

**Deploying:** `theme/` and `plugin/` must ship **including** their `build/` folders.
Those are gitignored, so a copy of the repo is not a deployable site — always
`npm run build` first.

The dev site deploys itself: pushing to `main` runs `.github/workflows/deploy-dev.yml`,
which builds and rsyncs over SSH. **Read [DEPLOYMENT.md](DEPLOYMENT.md)** for the secrets
it needs, how to run it by hand against a feature branch, and what it deliberately does
not touch (the database, uploads, and theme activation).

---

## 6. Building a component (the common case)

Components are **blocks**. Each one is a self-contained folder. A front-end contributor
should never need to open the backend.

```
plugin/src/blocks/<block-name>/
  block.json    metadata + attributes; "render": "file:./render.php"
  index.js      registration only — plain JS, no JSX
  edit.jsx      the editor UI — JSX must be in a .jsx file
  render.php    server-side markup — this is what ships
  style.scss    front-end styles
  editor.scss   editor-only styles
```

**Copy `plugin/src/blocks/section-heading/` — it is the reference pattern.**

Then two one-line registrations:
- `plugin/src/editor.js` → `import './blocks/<block-name>';`
- `plugin/src/styles/blocks.scss` → `@use '../blocks/<block-name>/style';`

PHP discovers the block by scanning for `block.json`. Nothing else changes.

### Hard boundaries for front-end contributors

**The full allow-list and the never-edit list are in §2.1 — that is the authority.** In
short: a contributor edits their own block folder or their own template folder, plus one
import line in `plugin/src/editor.js` and one `@use` line in
`plugin/src/styles/blocks.scss`. Nothing
else. Never `theme/inc/`, `theme/functions.php`, `_tokens.scss`, `plugin/inc/`,
`vite.config.js`, `package.json`, anything under `*/build/`, or any WooCommerce
integration.

**Need data from the backend** — a price, an order, a licence? **Do not query the database
from a block.** Ask the owner for a helper function and call that.

**Use the design tokens.** Colours, spacing and type live in
`theme/src/styles/_tokens.scss` as `--fm-*` custom properties. Never hardcode a brand
colour in a block. Two palettes ship — **Steel** (default) and **Nari** — switchable in
Appearance → Customize → Colors, so a hardcoded hex breaks one of them.

**Blocks are server-rendered** (`save: () => null`). The database stores attributes only,
never markup. This is what lets you change `render.php` without a content migration.

Add a row to `doc/COMPONENTS.md` when you add a block.

---

## 6a. Building a site template (the thing we sell)

A template is a **finished page assembled from blocks**, not a new pile of HTML. The
folder layout and the reason `content.html` is the deliverable are in §2.1a — read that
first. This is the order of work:

1. **Branch.** `Theme/<template-slug>` — see §2.3. Before anything else.
2. **Check which blocks you are missing.** Lay the design against
   `doc/COMPONENTS.md`. Anything the existing blocks cannot express is **lane A: build
   the block first, on its own branch, and get it merged.** Do not fake a section inside
   a template.
3. **Build the page in the editor** on Local, from the Foundations block category.
4. **Export the block markup** into `plugin/src/templates/<slug>/content.html` — in the
   editor, Options (⋮) → **Copy all blocks**, then paste. It is plain text, a few KB.
5. **Fill in `template.json`** — name, niche, category, the target SEO phrase from
   `doc/SEO-AND-PERFORMANCE.md` §10, and the demo URL slug.
6. **Add `screenshot.webp`**, compressed. Not a 4MB camera JPEG — see §9.
7. **Check it at 375px, 820px and 1440px** (§8) and run the §11 checklist.
8. **Add a row to `doc/TEMPLATES.md`.**
9. **Commit, push, open the PR** (§2.4).

Rules specific to templates:

- **Every template owns exactly one H1**, in its hero block. Every other section is H2 or
  lower. Two templates must never target the same SEO phrase.
- **Alt text carries the phrase** — "Pilates studio website template by Foundations
  Marketing", never "template1" (§10).
- **Tokens only.** A hardcoded hex survives Steel and breaks Nari. Use `--fm-*`.
- **No template-only JavaScript.** If it needs interaction, that belongs in a block.
- The catalogue rules — which templates are published, and why the nine new ones are held
  as drafts until they have a screenshot and a demo page — are in `doc/COMPONENTS.md`.
  **Read that before publishing anything.**

---

## 7. Previewing in Local

1. `npm run build` (or leave `npm run dev` running).
2. Open <http://foundationsmarketing.local>.
3. **Hard-refresh** — Ctrl+Shift+R. The server caches; a normal refresh will lie to you.

Check a block in the editor too, not just the front end: WP Admin → Pages → edit a page →
add your block from the **Foundations** category.

**Test both breakpoints, every time** — see §8. Use the browser's device toolbar
(F12 → Ctrl+Shift+M), and actually resize; do not trust the desktop view alone.

If something looks stale or blank:

| Symptom | Usual cause |
|---|---|
| Whole section renders as nothing | Foundations Blocks plugin is inactive |
| Changes do nothing at all | junction missing (§1.2), or you forgot `npm run build` |
| CSS is old | server cache — hard-refresh |
| White screen | PHP fatal; check `wp-content/debug.log` or Local's log tab |

To see PHP errors, set in `wp-config.php`: `define('WP_DEBUG', true);` and
`define('WP_DEBUG_LOG', true);`

---

## 8. Responsiveness — required on every change

The design is drawn at 1200px and collapses to a single column. Breakpoints are in
`_tokens.scss`: `$fm-bp-lap: 1024px`, `$fm-bp-tab: 820px`, `$fm-bp-mob: 560px`.

Rules for every block you build or fix:

- **Mobile is not an afterthought.** Check 375px, 820px and 1440px before you commit.
- The page body must **never scroll horizontally.** Wide things — tables, code, wide
  grids — scroll inside their own `overflow-x: auto` container.
- Use relative units, flexbox and grid. `max-width: 100%` on media.
- Padding shrinks on small screens: the gutter token drops from 40px to 20px under
  820px automatically. Use `var(--fm-gutter)`, not a hardcoded 40.
- Headings use `clamp()` so they scale — copy the pattern from an existing block rather
  than inventing fixed sizes.
- **Tap targets at least 44×44px** on mobile.
- Test with a long word and with realistic copy, not with "Lorem".

### Accessibility, same checklist

- Semantic landmarks: one `<header>`, one `<main id="fm-content">`, one `<footer>`.
- Real `<button>` and `<a>` elements — never a clickable `<div>`.
- `:focus-visible` is styled globally. **Never remove an outline** without replacing it.
- Toggles carry `aria-expanded` and `aria-controls`, kept in sync in JS.
- Decorative text (the giant footer wordmark) is `aria-hidden`.
- Contrast WCAG AA: 4.5:1 body, 3:1 large text. `--fm-muted` on `--fm-bg` passes;
  muted on the accent band does **not** — use `--fm-band-ink` there.
- Respect `prefers-reduced-motion` for any animation.

---

## 9. Speed — the target is 85+ mobile PageSpeed

Site-wide work is already done in `theme/inc/performance.php`. **Do not undo it casually:**

- WooCommerce CSS/JS dequeued on non-commerce pages, and cart fragments disabled — this
  is the single biggest win on a WooCommerce site.
- `classic-theme-styles` and `global-styles` dropped on the front end.
- Emoji script/styles and head cruft removed.
- `wp_omit_loading_attr_threshold` tuned to 2; WordPress 6.3+ picks the LCP image and
  assigns `fetchpriority` itself.
- WebP **and** AVIF uploads allowed; JPEG quality 82.
- Speculation rules prerender same-site links on hover — never cart/checkout/account.
- Fonts: **Archivo** and **Instrument Serif** only. Self-hosted when the woff2 files are
  present in `theme/assets/fonts/` (see `doc/FONTS.md`), Google CDN otherwise.

Rules when building:

- **No layout JavaScript.** Blocks are server-rendered. Ship vanilla JS only for real
  interaction, only from the block's own folder. No jQuery. No framework.
- **Always set width and height** on images so nothing shifts. `fm_image()` does this.
- Above-the-fold images pass `$eager = true` to `fm_image()`; everything else stays lazy.
- **No third web font. No icon font** — use inline SVG.
- No `@import` in SCSS — use `@use`. One stylesheet ships, not one per block.
- Compress images to WebP/AVIF before uploading. Do not upload a 4MB camera JPEG.

Measure before claiming: Chrome DevTools → Lighthouse → **Mobile**. If you made it
slower, say so.

---

## 10. SEO — required on every page and block

Full detail in `doc/SEO-AND-PERFORMANCE.md`. The rules that bind every contributor:

- **The brand is "Foundations Marketing", with the S.** Never the singular. The client's
  SEO audit found the site calling itself "Foundation Marketing" while the domain is
  foundationsmarketing.co.uk — that split costs them their own name and collides with an
  unrelated property agency. Use the `FM_BRAND` constant in PHP.
- **Exactly one H1 per page.** The hero block owns it; every other section is H2 or lower.
  `foundations/section-heading` clamps its level and warns in the editor.
- **Alt text on every image**, carrying the page's target phrase — "Pilates studio website
  template by Foundations Marketing", not "template1".
- **Clean URLs**: `/templates/pilates-website-design`. Never a query string like
  `/template/?fpv2_step=detail&template_id=5503`.
- **Internal links**: homepage ↔ services ↔ each template ↔ blog.
- **Schema**: Organization on the homepage, Service on each template demo page
  (`fm_service_schema()`).
- **Yoast owns** titles, meta descriptions, canonicals, robots and the sitemap. Do not
  emit a second copy of any of those.

Each of the nine templates targets its own low-competition phrase — the table is in
`doc/SEO-AND-PERFORMANCE.md`. Put the phrase in the page's title, H1, first paragraph,
image alt text and URL.

---

## 11. Before you commit

- [ ] `npm run build` passes.
- [ ] Checked in the browser at your Local URL — **not just in the editor**.
- [ ] Checked at 375px, 820px and 1440px.
- [ ] Keyboard-navigable; focus is visible.
- [ ] One H1 on the page; images have real alt text.
- [ ] No hardcoded brand colour — tokens only.
- [ ] You are **on a feature branch, not `main`** — `git branch --show-current` (§2.3).
- [ ] Template work only: `content.html`, `template.json` and a compressed
      `screenshot.webp` are all present, and `doc/TEMPLATES.md` has its row (§6a).
- [ ] `git status` is clean of local notes, DB dumps, `.env`, client asset drops and
      scratch markdown. Those live in `/doc/`, which is gitignored.

Then push the branch and **open the Pull Request** — §2.4. The work is not finished until
the PR exists.

### Never commit

`/doc/`, `CLAUDE.md`, `*.local.md`, `node_modules/`, `*/build/`, `.env*`, `*.sql`,
`wp-content/uploads`, `.zip` packages, client asset drops, AI scratch notes.

Run `git status` and read it before every push. If something local slipped in, remove it
from the commit rather than pushing and apologising.

### Commit messages

Explain **why**, not just what. Reference the file and the reason. If you fixed a bug,
say what would have happened if you had not.

---

## 12. Opening a Pull Request (everyone except the owner)

```bash
npm run build                        # must pass
git status                           # must be clean of local files
git push -u origin Prefix/your-branch
gh pr create --base main
```

The PR description should say:

1. **What** changed, in one line.
2. **Why** — the request, the bug, the design file.
3. **How to check it** — which page on Local, which breakpoints you tested.
4. Anything you deliberately did **not** do, and why.

Then stop and tell the owner. Do not merge your own PR.

---

## 13. Current state of the project

- **Local site:** `C:\Users\Nego\Local Sites\foundationsmarketing\app\public`
  at <http://foundationsmarketing.local>. Junctions created; *Foundations* theme and
  *Foundations Blocks* plugin are **active**.
- **Build:** all four Vite targets compile; every PHP file lints; site returns HTTP 200
  with no PHP warnings.
- **Blocks built so far:** 19 — see `doc/COMPONENTS.md` for the index and what is left.
- **Templates built so far:** none. `plugin/src/templates/` is the fixed home for them
  (§2.1a) and is where contributors build the sellable catalogue; the index is
  `doc/TEMPLATES.md`. The eight live templates predate the folder and have not been
  migrated into it.
- **The design source** is four client canvas pages (Homepage, Services, Templates,
  Checkout), decoded to plain HTML in `doc/client-html/extracted/`.
- **Elementor is being removed.** The live site was built in Elementor; we are rebuilding
  each page as blocks. Until a page is migrated it stays on Elementor —
  **do not half-convert a page.** Everything new must be blocks.
- **Security:** a backdoor plugin (`aqygohyco`) was found and removed from Local, and its
  orphaned `active_plugins` entry cleaned. **Production on Bluehost is very likely still
  infected** — see `doc/SECURITY-PRODUCTION-CLEANUP.md`. Do not deploy to production
  until that is done.

---

## 14. If something is unclear — ask

If the request is ambiguous in a way that changes what you would build, **ask before
building**, not after. Do everything that does not depend on the answer first, then ask
one clear question.

Do not invent scope. Do not quietly narrow it either. If part of the work is blocked,
finish everything else and say plainly what you left out and why.
