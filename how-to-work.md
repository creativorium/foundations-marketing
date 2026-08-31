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

### 0.1 Settled decisions — do not relitigate these

These are **closed**. They are written here so nobody — human or AI — spends a day
proposing an alternative, or quietly introduces one mid-task. If you think one is wrong,
raise it with the owner as its own conversation. Do not act on it.

| Decision | Why it is closed |
|---|---|
| **Native Gutenberg blocks. Not ACF, not a page builder.** | ACF Blocks is a paid **ACF PRO** feature, so every client site we ship would need a licensed third-party plugin — a recurring cost and a licensing question against a £199 product. ACF's ownership and its plugin-directory listing have also changed hands recently, which is a dependency risk we will not take on dozens of client sites. Native blocks need **nothing but our own theme and plugin**. |
| **Blocks are server-rendered** — `save: () => null`, output from `render.php`. | The database stores attributes only, never markup, so `render.php` can change with no content migration. It is also what makes a template a few KB of portable text. |
| **Each sold template carries its own blocks** (`templates/<slug>/blocks/`). The 19 blocks in `plugin/src/blocks/` are for **our marketing site**, not for the templates we sell. | A template has to be liftable — one folder, installable on a client's hosting, without dragging the rest of the catalogue with it. **Accepted cost:** there is no shared block to fix once, so the same bug in three templates is three fixes. Chosen deliberately for independence; see §2.1b. |
| **No new plugin dependency**, for the site or for a client build. | Every plugin is another thing to license, update, and have compromised. We have already had a backdoor on this site once. |
| **No JS framework, no jQuery, no layout JavaScript.** | The target is 85+ mobile PageSpeed on shared hosting (§9). JSX compiles to `wp.element.createElement`; **no React ships to the browser**. Ship vanilla JS only for real interaction, from the block's own folder. |
| **Elementor is being removed, not extended.** | The live site was built in it; we are rebuilding page by page as blocks. Everything new is blocks. Do not half-convert a page (§13). |
| **Two palettes, driven by `--fm-*` tokens.** | Steel and Nari both have to work. A hardcoded hex passes under one and breaks the other, so it will be caught. |

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
| `plugin/src/blocks/<block-name>/**` | a **main-website** block — the Foundations Marketing site itself (§2.1b) |
| `plugin/src/templates/<template-slug>/**` | the template's whole folder, **including its own blocks** — this is where template work lives (§2.1a) |
| `plugin/src/editor.js` | **one added `import` line per block**, nothing else — `'./blocks/<name>'` for a main-site block, `'./templates/<slug>/blocks/<name>'` for a template block |
| `plugin/src/styles/blocks.scss` | **one added `@use` line per block**, nothing else — `'../blocks/<name>/style'` or `'../templates/<slug>/blocks/<name>/style'` |
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
  template.json        name, niche, category, target SEO phrase, demo URL slug
  content.blocks.txt   the page as Gutenberg block markup — this is the deliverable
  screenshot.webp      catalogue image, 1200×900, compressed before it lands
  palette.scss         this template's --fm-* token set — what makes it look like its own site
  blocks/              THIS TEMPLATE'S OWN BLOCKS — one folder each, same anatomy as §6
    <block-name>/
      block.json  index.js  edit.jsx  render.php  style.scss  editor.scss
  style.scss           optional, template-level styles that are not a block
```

### 2.1b Two kinds of block — know which you are building

This trips people up, so be clear before you start:

| Kind | Lives in | For | Who |
|---|---|---|---|
| **Main-website block** | `plugin/src/blocks/<name>/` | the Foundations Marketing site itself — homepage, services, templates, checkout | Shared by our own pages. Changing one affects **our** site. |
| **Template block** | `plugin/src/templates/<slug>/blocks/<name>/` | one sold template, and only that one | Ships with that template to the client. Bespoke to it. |

**Each sold template carries its own blocks.** A template is a self-contained mini site:
its own blocks, its own palette, its own content. That is what lets us lift one folder out
and install it on a client's hosting without dragging the rest of the catalogue with it.

So **do not reach into `plugin/src/blocks/` while building a template.** Those are our
marketing site's components. If your template needs a hero, it gets **its own** hero at
`plugin/src/templates/<slug>/blocks/hero/`. Copy `plugin/src/blocks/section-heading/` as
the starting pattern and then make it yours.

**Naming — namespace every template block with its template slug**, or two templates will
collide the moment both are installed on our catalogue site:

```json
{ "name": "foundations/pulse-hero",  "category": "foundations-pulse" }
{ "name": "foundations/pulse-faq",   "category": "foundations-pulse" }
```

Never `foundations/hero` — that name belongs to the main site.

**The trade-off you are accepting**, so nobody is surprised later: a fix to a template's
block improves **that template only**. There is no shared block to fix once. If you find
the same bug in three templates, it is three fixes. That is the cost of each template
being independently shippable, and it was chosen deliberately (§0.1).

#### `content.blocks.txt` is Gutenberg block markup — NOT an HTML page

**Read this twice. It is the single most common thing to get wrong, and a template that
gets it wrong is worthless to us.**

A template is **not a design file**. It is a **pre-assembled WordPress page made of
Foundations blocks** — our blocks, in order, with their attributes filled with placeholder
copy. We import it onto the client's site, swap in their words, logo, colours and photos,
and ship. **The entire value is that it arrives editable in the WordPress editor.**

Every block is registered with `save: () => null` and rendered on the server by its
`render.php`. So WordPress stores **no HTML at all** for them — just a block comment and a
JSON blob of attributes. A correct `content.blocks.txt` therefore looks like this, and
almost nothing else:

```html
<!-- wp:foundations/hero {"heading":"Calm, clear pilates in Bristol","variant":"split"} /-->

<!-- wp:foundations/steps {"heading":"How it works","items":[
  {"title":"Book a class","body":"Pick a time that suits you."},
  {"title":"Come along","body":"Mats, blocks and straps provided."}
]} /-->

<!-- wp:foundations/faq {"schema":true,"items":[
  {"q":"Do I need experience?","a":"No — every class is mixed ability."}
]} /-->

<!-- wp:foundations/cta {"heading":"Book your first class"} /-->
```

**The test, and it is not a judgement call:**

> If the file contains a `<div>`, a `<section>`, an `<h1>`, or a `style=` attribute,
> **it is wrong.** Delete it and start again from the blocks.

Writing raw HTML here fails for four reasons, all fatal:

1. **It will not render.** Nothing in a hand-written HTML file connects to `render.php`.
2. **It is not editable.** It imports as one inert lump, so we rebuild the page by hand
   for every client — which destroys the margin the template existed to protect.
3. **It is frozen.** Fix a block once and every site we have ever shipped improves.
   Hardcoded HTML never receives that fix.
4. **It breaks the Nari palette** and bypasses the SEO, accessibility and speed rules that
   live *inside* the blocks — `fm_image()` setting width and height, `section-heading`
   clamping the heading level.

Because the file is plain text a few KB long, a template is something we can zip, send to
a client, and import onto any site already running the Foundations theme and plugin. That
is exactly the install we perform when someone buys, so the format we build in and the
format we hand over are the same thing. **Do not invent a second format for delivery.**

#### When there is no block for what the design needs

This is the moment the temptation to write raw HTML appears. **Do not.** A missing block
is **lane A work, and it comes first**: stop the template, branch, build the block, open
its PR, get it merged — then come back and use it in the template (§2.3: they are two
branches). Never hardcode a section into a template that should have been a block, and
never hardcode a brand colour — templates use the `--fm-*` tokens like everything else,
or they break under Nari.

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

A template is a **finished WordPress page assembled from Gutenberg blocks**, not a pile of
HTML. The folder layout, the block-markup format and the `<div>` test are in §2.1a —
**read that first, it is where people go wrong.** This is the order of work:

1. **Branch.** `Theme/<template-slug>` — see §2.3. Before anything else.
2. **Break the design into sections**, and build **a block for each**, inside
   `plugin/src/templates/<slug>/blocks/` (§2.1b). These are *this template's* blocks —
   namespaced `foundations/<slug>-<name>`. Do not use or edit the main site's blocks in
   `plugin/src/blocks/`; copy `section-heading/` as a starting pattern and make it yours.
   Register each one with its `import` line in `editor.js` and its `@use` line in
   `blocks.scss`.
3. **Build the page** from your own blocks — either way round, see §6b. In the editor on
   Local from your template's block category, or by writing the block markup and pasting
   it in.
4. **Save the block markup** to `plugin/src/templates/<slug>/content.blocks.txt` — in the
   editor, Options (⋮) → **Copy all blocks**, then paste into the file. Plain text, a few
   KB, no `<div>` anywhere in it.
5. **Fill in `template.json`** — name, niche, category, the target SEO phrase from
   `doc/SEO-AND-PERFORMANCE.md` §10, and the demo URL slug.
6. **Add `screenshot.webp`**, compressed. Not a 4MB camera JPEG — see §9.
7. **Check the demo page** at 375px, 820px and 1440px (§8) and run the §11 checklist.
8. **Add a row to `doc/TEMPLATES.md`.**
9. **Commit, push, open the PR** (§2.4) — include the demo URL so it can be reviewed.

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

## 6b. Previewing a template on Local — getting a demo link

**Before anything below:** the *Foundations Blocks* plugin must be **active** and you must
have run `npm run build`. If the plugin is off, every block renders as **nothing at all**
(§7) — a blank page means the plugin is inactive, not that your markup is broken.

### Method 0 — just open the demo URL (use this one)

**`/templates/<demo-slug>/demo/`** on your Local site renders the template straight from
`content.blocks.txt` **on disk**. No page to create, nothing to paste, no WP-CLI. Save the
file, refresh the browser.

```
http://foundationsmarketing.local/templates/pilates-website-design/demo/
```

The slug is `demoSlug` from your `template.json` — use the SEO phrase from
`doc/SEO-AND-PERFORMANCE.md` §10. Without a `template.json` it falls back to your folder
name, so a half-finished template is still previewable.

This renders it **standalone** — the template's own header, hero and footer, with none of
our site's chrome around it — because that is what the buyer is judging and what gets
installed. It is the *same code path that serves buyers in production*, so what you sign
off here is what ships. There is a fixed bar at the bottom to get back out; it is ours,
not part of the template.

**This is the URL to put in your PR.**

> If you get a 404, the rewrite rules need flushing: visit
> **Settings → Permalinks** in WP Admin and hit Save (changing nothing). That is the
> usual cause of a route that "does not exist" right after pulling.
>
> If the page loads but a section is **blank**, that section's block is not registered on
> the server — see the warning in §13.

The two methods below are fallbacks. You want them when you are building the page *in the
editor* and exporting it, rather than writing the markup by hand.

### Method 1 — paste it into the editor (when you built the page in the editor)

1. WP Admin → **Pages → Add New**
2. Open the **Code editor**: `Ctrl+Shift+Alt+M` (or Options ⋮ → Code editor)
3. Paste the whole contents of `content.blocks.txt`
4. Switch back to **Visual** — WordPress parses the comments into real, editable blocks
5. **Publish** (or Preview) → **that URL is your demo link**

This doubles as a correctness check. If it comes back as one grey lump, or as a single
"Classic" block, the markup is malformed — usually raw HTML that should have been blocks.
Separate, selectable, editable blocks means you got it right.

### Method 2 — WP-CLI, the loop an AI can drive itself

Local ships WP-CLI: right-click the site in Local → **Open site shell**. Create the page
once:

```bash
wp post create --post_type=page --post_title="Demo — Pulse" --post_status=publish \
  --post_content="$(cat '/path/to/plugin/src/templates/pulse-pilates/content.blocks.txt')"
```

It prints the new post ID. After that every edit is one command and a refresh:

```bash
wp post update 123 --post_content="$(cat '.../content.blocks.txt')"
wp post list --post_type=page --fields=ID,post_title,guid   # find the demo URL again
```

That is the tight iteration loop — edit the file, run one command, refresh the browser.
No clicking, so an assistant can do it unattended.

> Windows note: Local's shell and path quoting vary by machine. If the `$(cat …)` form
> fights you, fall back to Method 1 — it always works.

### Two URLs per template, and they do different jobs

Do not confuse these — one is for Google, one is for looking at.

| URL | What it is | Who it is for |
|---|---|---|
| `/templates/<keyword-slug>/` | Our **branded detail page** — screenshot, description, and the "choose this template" CTA into the builder. Owns the template's target phrase from the SEO table and is the page that ranks. | Buyers browsing, and Google |
| `/templates/<keyword-slug>/demo/` | The **standalone mini site** — the template's own header, hero and footer, no chrome of ours. Rendered from `content.blocks.txt` on disk. **`noindex`.** | Buyers clicking "Live Preview", and you |

The demo is deliberately `noindex`: it is the same content with no branding, no
description and no way to buy, so letting it compete with the detail page would split the
signal for the phrase and land buyers somewhere they cannot act.

Use the slug from `doc/SEO-AND-PERFORMANCE.md` §10 — never a random preview URL. A
template with no detail page **cannot be published**: catalogue cards would point at a 404.
See `doc/TEMPLATES.md` for the full checklist.

**Put the demo URL in your PR** so it can be reviewed without being rebuilt.

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
- [ ] Template work only: `content.blocks.txt`, `template.json` and a compressed
      `screenshot.webp` are all present, and `doc/TEMPLATES.md` has its row (§6a).
- [ ] Template work only: `content.blocks.txt` contains **no `<div>`, `<section>` or
      `style=`** — it is block comments, nothing else (§2.1a), and the demo page renders
      as real editable blocks (§6b). Demo URL is in the PR.
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
- **Templates built so far: none — zero.** `plugin/src/templates/` is the fixed home for
  them (§2.1a) and is where contributors build the sellable catalogue; the index is
  `doc/TEMPLATES.md`. **Every catalogue entry that currently exists is a placeholder** —
  the eight "live" ones (Aether, Birth Space, Bloom, Lumen, Nova, Sequoia, Solis,
  Solstice) and the nine canvas drafts (Halo, Pulse, Meridian, Harvest, Haven, Compass,
  Gloss, Canvas, Cadence) alike. None has real blocks, real content or a real demo page.
  Treat the whole catalogue as empty and build from scratch.
- **The 19 blocks in `plugin/src/blocks/` are for THIS website**, not for the templates we
  sell — homepage, services, templates, checkout. Each sold template gets its own blocks
  under `plugin/src/templates/<slug>/blocks/` (§2.1b). Do not confuse the two.

> ### ⚠️ Owner work required before the first template block will work
>
> `plugin/inc/register.php` currently discovers blocks by scanning **`src/blocks/*/block.json`
> only**. It does **not** scan `src/templates/*/blocks/*/block.json`, so a template's own
> blocks will register in the editor (via `editor.js`) but **have no server-side render**
> and will output nothing on the front end.
>
> `plugin/inc/` is on the contributor never-edit list, so this is **owner work and it is
> not done yet.** Until it is, template blocks cannot be previewed properly. Contributors:
> if your template block renders blank, this is why — say so and stop; do not try to work
> around it.
>
> Also still missing, both owner work: the **packaging pipeline** that turns a template
> folder into a zip installable on a client's hosting, and the link between a merged
> `template.json` and the `site_template` catalogue CPT (which lives in the separate
> `foundation-packages` plugin).
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
