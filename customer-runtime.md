# customer-runtime.md

**What a buyer receives, and how a template becomes it.**

`how-to-work.md` says how we *build* a template. This file says what we *ship*. It is the
missing half: until the pipeline described here exists, a finished template cannot be
delivered to a buyer.

This file is in git on purpose. It is a shared technical contract — every contributor
builds against it. Confidential client material stays in `/doc/`, which is gitignored.

---

## Status — planned vs implemented

The shared base, customer runtime, packager and internal delivery manager are implemented.
Local acceptance evidence is recorded in delivery-verification.md. A passing fixture
proves the pipeline; each real design still needs its own acceptance checks.
See [delivery-operations.md](delivery-operations.md) for the current installation workflow.

| Piece | State | Where |
|---|---|---|
| Block discovery scans `src/templates/*/blocks/*/block.json` | **Implemented** | `plugin/inc/register.php` |
| Standalone demo route, single page from `content.blocks.txt` | **Implemented** | `plugin/inc/demo.php` |
| Per-template block categories in the inserter | **Implemented** | `plugin/inc/register.php` |
| `fm_image()`, `fm_url()` and the block helpers | **Implemented** | `plugin/inc/helpers.php` |
| Multi-page template folder (§2) | **Specified and exercised by fixture** | `fixtures/base-three-page/` |
| Multi-page demo routing (§2.3) | **Built for compiled designs** | `plugin-delivery/inc/preview.php` |
| Customer theme base — shell, tokens, parts (§3) | **Built** | `theme-customer-base/` |
| Site Settings **contract** — schema, defaults, read helpers | **Built** | `theme-customer-base/inc/settings-contract.php` |
| Shell blocks — `foundations/site-header`, `site-footer` (§5.1) | **Built** | `theme-customer-base/blocks/` |
| Internal three-page fixture | **Built** | `fixtures/base-three-page/` |
| Customer blocks plugin output (§4) | **Built** | `plugin-site/`, `scripts/package.mjs` |
| Site Settings **screen**, Site Owner role, capabilities (§5) | **Built** | `plugin-site/inc/settings.php` |
| Starter-site import (§6a) — **milestone 1** | **Built** | `plugin-site/inc/bundle.php` |
| Customised-site export (§6b) — **milestone 2** | **Built for explicit pages/media/settings** | `plugin-site/inc/bundle.php` |
| Customer projects, releases and manual installation records | **Built** | `plugin-delivery/` |
| Always-active keycard | **Built; no remote heartbeat** | `plugin-site/inc/tools.php` |
| Maintenance reporting (§8) | **Deferred**, after milestone 2 | — |
| Templates in the catalogue | **Zero.** `plugin/src/templates/` does not exist | — |

Because no template has ever been built, **there is nothing to migrate.** The structure in
§2 is adopted as the starting shape, not as a change to existing work.

---

## 1. The three deliverables

A buyer's site is built from three packages, not one. All three are produced by
`scripts/package.mjs` from one `plugin/src/templates/<slug>/` folder.

| Package | Contains | Why separate |
|---|---|---|
| **Theme** — `foundations-<slug>` | `theme.json`, header/footer parts, page templates, compiled CSS, fonts | The design. Customer-facing; it is *their* theme. |
| **Plugin** — `foundations-site` | that template's blocks only, compiled JS/CSS, Site Settings screen, PHP helpers | Blocks must be registered by PHP or they render nothing. Keeping them out of the theme means content survives a theme update. |
| **Starter content bundle** | pages as block markup, media files, navigation, site settings, a manifest | Pages and images live in the **database**, not in our repo. Source files alone cannot recreate a site. |

The third is the one that is easy to forget and impossible to skip.

A customer never receives *Foundations Blocks*. That plugin carries `template-grid`,
`template-library`, `package-builder` and `inc/checkout.php`, which hooks WooCommerce and
reads prices off our own checkout page. None of it belongs on a client's site.

---

## 2. The template folder — multi-page

A real customer site is four or five pages, not one. This replaces the single-page anatomy
in `how-to-work.md` §2.1a.

```
plugin/src/templates/<template-slug>/
  template.json          name, niche, category, target SEO phrase, demo slug,
                         page list, and this template's Site Settings extras (§5.3)
  theme.json             this design's palette, typography, spacing
  parts/
    header.html          composition of the header — one block, see §5.1
    footer.html          composition of the footer
  blocks/<name>/         THIS TEMPLATE'S OWN BLOCKS, namespaced foundations/<slug>-<name>
  content/
    pages/
      home.blocks.txt    the homepage. Required. Owns the H1.
      about.blocks.txt   every other page, one file each
      contact.blocks.txt
    media/               the real images, as files
    media.json           filename → the block attribute that references it
    navigation.json      menu structure, linking by page slug
    settings.json        default Site Settings values (logo, phone, CTA, socials…)
    manifest.json        versions, homepage slug, page order
  screenshot.webp        catalogue image, 1200×900, compressed
  style.scss             optional, template-level styles that are not a block
```

### 2.1 What did not change

Everything in `how-to-work.md` §2.1b still holds and is not reopened:

- Each sold template carries **its own blocks**. Never reach into `plugin/src/blocks/`.
- Namespace every template block `foundations/<slug>-<name>`.
- Blocks are server-rendered, `save: () => null`.
- A page file is **Gutenberg block markup** — block comments and attributes, nothing else.
  Do not put arbitrary markup outside block delimiters. Core blocks may contain their
  own saved HTML; custom dynamic blocks store attributes and save no HTML.
- Tokens only. No hardcoded hex.

### 2.2 What changed

- `content.blocks.txt` at the template root is replaced by `content/pages/*.blocks.txt`.
  The homepage is `home.blocks.txt` and is required.
- **Exactly one H1 per page**, not per template. The homepage H1 owns the target phrase;
  interior pages own their own.
- Header and footer are **no longer blocks in the page content.** They move to
  `parts/header.html` and `parts/footer.html` and render site-wide (§5.1). A page file
  that starts with a header block is now wrong — it would repeat on every page.
- Two new required files a template did not previously have: `theme.json` and
  `content/manifest.json`.

### 2.3 Demo routing

`plugin-delivery/inc/preview.php` serves compiled multi-page designs and isolated customer previews.
It reads parts from the packaged theme and applies that design's tokens. The older
`plugin/inc/demo.php` remains a **single-page compatibility route**: it scans for `content.blocks.txt`,
matches `^templates/([^/]+)/demo/?$`, and renders that file with no header or footer,
"because the template supplies its own". Multi-page templates and part-based headers break
three assumptions in it. The delivery manager implements their replacements:

| Legacy route | Delivery manager |
|---|---|
| A template is discovered by a readable `content.blocks.txt` | Discovered by `content/pages/home.blocks.txt` |
| One route, `/templates/<slug>/demo/` | `/templates/<slug>/demo/` for the homepage, `/templates/<slug>/demo/<page>/` for the rest |
| The page content carries its own header and footer | The demo must render `parts/header.html` and `parts/footer.html` around the page, populated from `content/settings.json` |

There is a fourth, subtler one. On our marketing site the template's **theme is not
installed**, so the demo cannot reach `parts/*.html` through the normal theme layer. The
demo route has to load those files from the template folder directly and render the
site-header block with `settings.json` as its data source.

Internal links also need handling: a page file links to `/about/`, but on the demo that has
to resolve to `/templates/<slug>/demo/about/`. Rewrite at render time from the page list in
`manifest.json`.

The `noindex` rule and the two-URLs-per-template split in `how-to-work.md` §6b are
unchanged — the branded detail page still owns the phrase, the demo still does not compete
with it.

---

## 3. The theme: one base, N generated

**Do not hand-maintain nine themes.** Keep one customer base plus per-template overrides,
and let the packager assemble the shipped theme.

```
theme/                    OUR MARKETING SITE. Classic, runs WooCommerce. Never shipped.
theme-customer-base/      the customer base — a NEW, minimal block theme
```

### 3.1 The base is new, not a fork of `theme/`

Settled: `theme-customer-base/` is written from scratch. `theme/` is a classic theme that
declares `woocommerce`, `wc-product-gallery-*` and a Customizer palette control, and serves
a marketplace we are not shipping. Forking it means deleting most of it and then carrying
the rest as confusing dead weight on every client site.

Minimal means minimal: `style.css`, `functions.php`, `theme.json`, `templates/index.html`,
`templates/page.html`, `parts/header.html`, `parts/footer.html`, and the role and
capability setup in §5.2. No WooCommerce, no Customizer, no marketing-site styles.

### 3.2 Version stamping is required

Every generated package records all three versions — in the theme's `style.css` header, in
the plugin header, and in `content/manifest.json`:

```
Foundations Base: 1.4.0
Template: pulse 2.1.0
Blocks Runtime: 0.3.0
```

Without this you cannot answer "what is this client running?" six months from now, and
every support conversation starts with archaeology.

### 3.3 A base fix does not reach installed sites

Say this plainly, because it is the thing people assume away:

> Fixing the base improves **packages generated after the fix**. Sites already installed
> keep what they were given until someone updates them.

There is no auto-update channel and building one is not in scope. The update process is
manual and versioned: regenerate the package, diff the versions, apply on the client site,
re-run the §7 acceptance checklist. Customer edits live in the database and in site
settings, so replacing theme and plugin files *should* leave them intact — that is a claim
to **verify on a real site**, not to assume.

---

## 4. The plugin: stripped, and compiled

`foundations-site` contains only:

- the selected template's blocks
- `inc/helpers.php` — the blocks call `fm_image()` and `fm_url()` from it
- `inc/register.php`, reduced to scanning one `blocks/` directory
- the Site Settings screen, roles and capabilities (§5)
- a compiled `build/` for **this template only**

### 4.1 The packager compiles. It does not copy.

`plugin/inc/assets.php` enqueues one shared `build/editor.js` and one `build/frontend.css`,
and `block.json` deliberately declares no `editorScript` or `style` handles — every block's
JS and CSS is bundled together. So copying `blocks/` folders ships blocks that **do not
appear in the editor and have no styles.**

`scripts/package.mjs` must run a Vite build scoped to the selected template, producing that
template's own `build/editor.js`, `build/frontend.css` and any block's frontend JS. It sits
beside `scripts/build-error-pages.mjs` and is driven by `npm run package <slug>`.

### 4.2 What "content survives a theme change" actually means

Qualify this whenever describing it. The plugin keeps the blocks **registered and
rendering**, so content is not lost and pages do not collapse into invalid-block warnings.
But appearance depends on the theme's tokens and `theme.json`. Swap the theme and the
blocks still work; they will not still look right. That is the correct trade — it is not
the same as "theme-independent".

---

## 5. Header, footer, navigation, and who may edit what

### 5.1 The mechanism

`parts/header.html` contains one block:

```html
<!-- wp:foundations/site-header /-->
```

Its `render.php` reads Site Settings from one option row and renders a fixed layout.
`parts/footer.html` works the same way. One file, one option row, every page.

**These two blocks are registered by the theme base, not by the customer plugin** —
`theme-customer-base/blocks/`. They are site shell, identical in every design apart from a
`variant` and the tokens around them, so they are not "a template's blocks" in the §4
sense. Keeping them in the theme also means `parts/header.html` renders whether or not the
plugin is present, which is what makes the base testable on its own. A design varies the
shell through `variant`, its `theme.json` and its own `style.scss`.

**When that is not enough, override the part.** CSS can rearrange existing elements, but a
genuinely different header — different elements, a different nesting, a search field the
shared one has no concept of — needs different markup, and pretending otherwise would force
every future design into the first one's structure. So there is a documented escape hatch:

| Need | Do this |
|---|---|
| Different arrangement of the same elements | `variant` + the design's `style.scss` |
| Different markup entirely | Add `parts/header.html` **and** a `blocks/<slug>-site-header/` to the design; the packager prefers a design's own part over the base's |

The escape hatch is deliberately more work than the variant, because a forked header stops
receiving base fixes. Use it when the design genuinely differs, not to avoid writing a
selector. Record which designs have forked, so a base fix can be applied by hand to them.

**Staff previewing the shell in the Site Editor.** The shell blocks have PHP registration
and an editor registration using `ServerSideRender` in
`theme-customer-base/assets/editor.js`. Customers edit their business details through
Site Settings; the Site Owner role cannot access the Site Editor.

**Describe the product accurately, internally and to the customer:**

> Customers edit header and footer **information**. Our team controls their **layout**.

### 5.2 The capability conflict, and how it is resolved

There is a real conflict here and it has to be settled deliberately, because the obvious
design does not work.

**The conflict:** `Appearance → Menus` (`nav-menus.php`) and `Appearance → Editor`
(`site-editor.php`) are both gated by the **same** capability, `edit_theme_options`. We
want the customer to have menus and *not* have the Site Editor. Capabilities cannot
express that. And removing an admin menu item only hides a screen — the URL still works,
so hiding is not enforcing.

**The resolution: navigation moves into Site Settings, and the customer never gets
`edit_theme_options`.**

- The customer's default account is a **Site Owner** role, not Administrator.
- Site Owner does **not** have `edit_theme_options`. The Site Editor is therefore
  genuinely unreachable — enforced by capability, not concealed by a missing menu item.
- Site Owner **does** have a custom capability, `fm_manage_site_settings`, and the Site
  Settings screen requires that and not `manage_options`. This is the trap to avoid:
  gating our own screen behind `manage_options` would lock the customer out of the one
  screen built for them.
- Navigation is edited in Site Settings as repeatable rows — label, plus either a page
  from a dropdown or a custom URL — with one level of children.
- The site-header block renders navigation from that option row. It does **not** call
  `wp_nav_menu()`, and the customer theme registers no classic menu locations.

**What this costs**, stated so nobody is surprised: we lose the familiar
`Appearance → Menus` screen and everything WordPress gives for free there — nesting beyond
one level, menu item classes, non-page object types. For a four-to-six-link service
business site that is an acceptable trade, and we gain full control of the nav markup,
which matters for the accessibility and speed rules in `how-to-work.md` §8–§9.

**Rejected:** granting `edit_theme_options` and removing the Site Editor menu item. The
capability is what grants access; `site-editor.php` remains reachable by URL. A client who
finds it can dismantle the header and the layout control we sold, and that becomes a
support call we cannot bill for.

### 5.3 Settings are shared, with per-template extensions

Settled: **one shared core field set, plus extras declared per template.**

The core lives in the customer base and is identical everywhere: logo, site name, phone,
email, address, primary CTA label and link, social links, footer legal text, navigation.

A template adds its own in `template.json`:

```json
{
  "settings": {
    "openingHours": { "type": "text",  "label": "Opening hours" },
    "bookingUrl":   { "type": "url",   "label": "Booking link" }
  }
}
```

The packager reads that and generates the extra fields into the shipped Site Settings
screen. A salon header needing opening hours does not force every other template to carry
an empty field, and the shared core still gets fixed in one place.

Add to the core only what **every** template needs. Everything else is an extension.

#### Business name and the WordPress site title are ONE field

`site_name` drives the shell. WordPress `blogname` drives the `<title>` tag, feeds, the
admin bar and outgoing mail. Left as two settings they drift, and the customer edits the
one they can see — then asks why the browser tab still says something else.

**Decision: the Site Settings "Business name" field is the single control, and saving it
writes `blogname` too.** No separate website-title field is exposed. A service business's
site title *is* its business name; offering two is a question the customer cannot answer,
and a support call when they answer it differently.

The read side already behaves this way: `fm_setting('site_name')` falls back to
`get_bloginfo('name')`, so the shell is correct before the screen exists. **The write-through
is implemented in the customer settings screen**, which also updates `blogname`.

Verified on the installed fixture: with a separate `blogname`, the header said one thing
and the document title another. That is the drift this closes.

### 5.4 Other capability decisions

- Use block locking — `templateLock`, and per-block `lock` attributes — for sections inside
  page content that must keep their structure.
- Site Owner does not get `install_plugins`, `switch_themes`, `update_core` or
  `edit_files`. Default no; revisit per client, in writing.
- We retain a separate administrator account on every site we deliver.

---

## 6. Content — the part that is not in the repo

Blocks store **attachment IDs**: `fm_image(int $attachment_id, …)` in
`plugin/inc/helpers.php`. An ID is meaningful only on the site that created it. A page
carried to another site with no media handling renders with **every image missing** — and
that looks like a broken template rather than a missing import step.

Two directions, very different amounts of work. Do not conflate them.

### 6a. Starter-site import — MILESTONE 1

Our master template onto a fresh site. This is the install we perform when someone buys,
and it is **file-driven**, which is why it is the tractable one. Source is
`content/` from §2.

**Page files and `settings.json` contain tokens, never real URLs or ids.** Our blocks store
attachment ids and core blocks store URLs, and neither survives a move between sites, so a
reference is written as a token and resolved after the pages exist and the media is
sideloaded:

| Token | Becomes |
|---|---|
| `{{media:<filename>\|url}}` | the sideloaded attachment's URL |
| `{{media:<filename>\|id}}` | its attachment id |
| `{{page:<slug>\|url}}` | that page's permalink |
| `{{page:<slug>\|id}}` | that page's post id |

**Required behaviour when resolving tokens**, so that a bad bundle fails loudly rather
than installing a subtly broken site:

- An unresolvable reference is a **hard error** naming the file, the token and the line.
  Never substitute an empty string — that produces `<img src="">` and a site that looks
  installed and is not.
- `|id}}` resolves to an **integer** in the block attribute, not a numeric string. Blocks
  type their id attributes as integers and a string fails the attribute's type.
- **Inside a block comment's attribute JSON the token is authored quoted**, because a bare
  `{{…}}` is not valid JSON and nothing — editor, parser or lint — could read the block:

  ```
  <!-- wp:image {"id":"{{media:logo.png|id}}","sizeSlug":"large"} -->
  ```

  So the importer must replace the **quoted string including its quotes** with a bare
  integer, not the token alone — substituting inside the quotes leaves `"id":"8"`, a
  string, and the attribute type fails. The same token unquoted in a class
  (`class="wp-image-{{media:logo.png|id}}"`) is a plain textual substitution.
- `|url}}` is escaped for where it lands — `esc_url()` in an href or src, and JSON-encoded
  when it sits inside a block comment's attribute JSON.
- After the second pass, **no `{{` may remain anywhere** in any created page, option row
  or setting. Assert it, and fail the import if one survives.

The importer, running on the fresh site:

1. Sideloads every file in `media/` and records the new attachment IDs.
2. Creates each page from its block markup, rewriting image attributes via `media.json` to
   the new IDs.
3. Rewrites internal links from page slugs to the new permalinks.
4. Builds the navigation option row from `navigation.json`, resolving page slugs to IDs.
5. Writes the Site Settings option from `settings.json`.
6. Sets the static homepage and the permalink structure, then flushes rewrite rules.

Every one of those steps is a way the install can silently half-work, which is what §7
exists to catch.

### 6b. Customised-site export — MILESTONE 2

Once we have edited a site for a client, **their content is in their database and nowhere
else.** Zipping the template folder captures none of it. Moving or re-delivering that site
needs an exporter that reads the database back out into the §6a bundle format — same
shape, real content, media exported as files, IDs mapped back to filenames.

Harder than 6a, and deliberately second. The rule it enforces starts now:

> **The master template and a customer's copy are two different things.** Never edit a
> master to suit one client. Never treat a customer site as the source of truth for a
> template.

---

## 7. Acceptance — run on a clean WordPress install

The checklist in `doc/TEMPLATES.md` checks that the **demo looks right**. This one checks
that the site **survives installation**. Both are required, and this one is what "done"
means for a delivered template.

- [ ] Fresh WP, nothing else installed. Theme and plugin activate with no notices.
- [ ] Every page in `manifest.json` exists, with the right title and slug.
- [ ] **Every image renders.** No missing attachments, no blank figures.
- [ ] No URL, image or link anywhere points at our dev or Local site. Grep the database.
- [ ] Navigation is populated and correct in the header and the footer.
- [ ] The homepage is set as a static front page.
- [ ] Every block opens in the editor as a **separate, editable block** — no invalid-block
      warnings, no "this block contains unexpected content".
- [ ] Site Settings is reachable and editable **by the Site Owner role**, not only by an
      administrator. Changing a field changes the header on every page.
- [ ] Appearance → Editor is **not** reachable by the Site Owner role, by menu or by URL.
- [ ] Each page has exactly one H1.
- [ ] 375px / 820px / 1440px, no horizontal scroll (`how-to-work.md` §8).
- [ ] Mobile PageSpeed 85+ (§9).
- [ ] Switch our dev site off. The customer site still works completely.

That last one is the real test. Everything else can pass while the site is quietly
depending on us.

---

## 8. Deferred — maintenance reporting

Wanted, but **after** milestone 2. It proves nothing about delivery, and it is not what
unblocks the business.

When it is built:

- **No kill switch. Ever.** Not built, not disabled-but-present. A customer's site stays
  fully working if the key is missing, invalid, or our endpoint is down. A lock on a
  £199–£480 done-for-you site turns a billing question into a client's business going dark.
- **Enrol on activation, do not stamp.** A key baked into a package is duplicated by every
  clone of it, and identities collide. The plugin registers itself on first activation and
  receives its identifier then.
- **It is maintenance reporting, not uptime monitoring.** WP-Cron fires on page loads, so a
  quiet site checks in late while working perfectly. A missed check-in means "look into
  this", never "the site is down". Use external monitoring for outages.
- Send versions and an identifier. **No customer content, no personal data.** Disclose the
  check-in in the contract and on the plugin's own settings screen.

---

## 9. Where this binds the working rules

`how-to-work.md` is still the working process. This file changes four things in it, and
those sections now point here:

| Section | Change |
|---|---|
| §2.1a | Template folder is multi-page; adds `theme.json`, `parts/`, `content/` |
| §6a | Build order includes the pages, the parts and the manifest |
| §6b | Demo routing is multi-page through the delivery manager (§2.3) |
| §13 | The packaging pipeline is specified here rather than only named as missing |

Nothing in §0.1 is reopened. Native Gutenberg blocks, server-rendered, one folder per
template, no new plugin dependency, no JS framework, token-driven palettes — all
unchanged. The customer theme being a **block** theme is not a contradiction of the classic
marketing theme in §0: they are two different themes for two different sites, and the
customer one is still native Gutenberg with no page builder and no ACF.
