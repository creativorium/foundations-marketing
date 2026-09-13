# Foundations Customer Base

The minimal block theme every customer site theme is generated from.

**This theme is never shipped as-is.** `scripts/package.mjs` (step 3, not built) assembles a
per-design theme — `foundations-<slug>` — from this base plus one template's `theme.json`,
`parts/` and styles. See [customer-runtime.md](../customer-runtime.md) §3.

It is **not** related to `theme/`, which is the classic marketing theme for
foundationsmarketing.co.uk and runs WooCommerce. Two themes, two sites.

---

## Status

| Piece | State |
|---|---|
| Token contract (`theme.json`) and the `--fm-*` bridge | **Built** |
| Page shell — `templates/`, `parts/` | **Built** |
| Header/footer integration points — `blocks/site-*` | **Built**, reading Site Settings |
| Site Settings **contract** — schema, defaults, read helpers | **Built** |
| Site Settings **screen**, Site Owner role, capabilities | **Not built — step 2** |
| Packager | **Not built — step 3** |
| Importer | **Not built — step 4** |

So on this branch settings come from defaults or from whatever wrote the option row by
hand. That is enough to prove the shell renders real data, which is what this branch is
for — and it is why the acceptance checks below are run as an administrator *and* the
capability checks are explicitly deferred to step 2.

---

## What is in here

```
theme-customer-base/
  style.css                  theme header, the --fm-* bridge, shell styles
  theme.json                 THE TOKEN CONTRACT — colour, type, spacing, layout
  functions.php              constants + requires, nothing else
  inc/
    settings-contract.php    the field schema, fm_setting(), fm_nav() — read side only
    setup.php                theme supports, skip link
    assets.php               cache busting, and trimming core assets we do not use
    shell-blocks.php         registers the two shell blocks
  blocks/
    site-header/             one block, fixed layout, content from Site Settings
    site-footer/
  templates/                 page, index, single, 404
  parts/                     header.html, footer.html — one block each
```

### `page.html` renders no post title, deliberately

The page's H1 comes from its **content** — the hero block in a real design, a
`core/heading` in the fixture. If the template also rendered `core/post-title` as an H1,
every page would have two, which breaks the one-H1 rule in `how-to-work.md` §10 on every
page of every site we ship. `single.html` does render the title, because a blog post's
title genuinely is its H1.

---

## The token contract

`theme.json` is the source of truth. Everything else reads from it.

**Initial contract — small on purpose, and not yet stable.** It covers what every design
needs: a seven-colour palette, two font families, five sizes, six spacing steps, and the
content/wide layout widths. Expect the fixture and the first real design to show what is
missing. Do not call it stable until a sellable template has been built through it.

| Group | Tokens |
|---|---|
| Colour | `canvas` `surface` `ink` `muted` `line` `accent` `on-accent` |
| Type | families `display` `body`; sizes `small` `medium` `large` `x-large` `xx-large` |
| Spacing | `10` `20` `30` `40` `50` `60` |
| Layout | `contentSize` 42rem, `wideSize` 72rem |

### The `--fm-*` bridge

Block authors write `--fm-accent`, not `--wp--preset--color--accent`. `style.css` aliases
every preset to its `--fm-*` name, because the rest of this project's rules
(`how-to-work.md` §1.5) are written in `--fm-*` and asking block authors to hold two
vocabularies is how a hardcoded hex gets in.

Consequence worth stating: a design changes its whole palette by editing **one file** — its
own `theme.json` — with no block CSS touched.

### Extension points

| To add | Do this | Not this |
|---|---|---|
| A colour, size or spacing step | Add a preset to the **design's** `theme.json`, alias it in that template's `style.scss` | Edit this base's `theme.json` |
| A Site Settings field one design needs | Declare it in that template's `template.json` under `settings` | Edit `fm_settings_core()` |
| A different header shape | Set `variant` in the design's `parts/header.html`, restyle via its `style.scss` | Fork `blocks/site-header/` |

The rule behind all three: **anything true of one design belongs to that design.** The base
holds only what is true of all of them. If you are editing the base to make one design
work, it is in the wrong place.

Core wins a key collision in `fm_settings_schema()` on purpose — a design must not redefine
`phone` to mean something else, or the shared header stops being shared.

---

## Deliberate omissions

Each of these is a decision, not an oversight:

- **No WooCommerce support.** The customer is not selling our packages.
- **No Customizer.** Settings live in one screen (step 2), not scattered across two systems.
- **No classic nav menu locations.** Registering one puts `Appearance → Menus` back in play
  and reopens the capability conflict — it shares `edit_theme_options` with the Site Editor
  (customer-runtime.md §5.2). Navigation is a Site Settings field instead.
- **No JavaScript at all**, including a burger menu. The nav is a list that wraps. A toggle
  is layout JS and the budget is 85+ mobile PageSpeed on shared hosting (§9). A design that
  truly needs one builds it as its own block, with its own script.
- **No hover-only submenus.** One level, always visible — a hover submenu is unreachable by
  touch and needs JS to fix.
- **No build step.** Plain CSS, no Vite target. The base is small enough not to need one,
  and the packager compiles per-design assets in step 3.
- **No webfonts.** System stacks until a design supplies its own; self-hosted, max two
  families (§9, `doc/FONTS.md`).

---

## Acceptance checks for this branch

Install on a clean WordPress with the fixture — the manual path is in
[`fixtures/base-three-page/README.md`](../fixtures/base-three-page/README.md).

**Shell**
- [ ] All three fixture pages render with a header above and a footer below.
- [ ] The header appears **once** per page, and is identical on all three.
- [ ] Editing `fm_site_settings.phone` changes the footer on **every** page, one edit.
- [ ] With no logo set, the header falls back to the business name, then the site title.
- [ ] With `cta_label` or `cta_url` empty, **no** button renders — not an empty one.
- [ ] With `nav_primary` empty, no empty `<nav>` is emitted.
- [ ] The nav item for the current page carries `aria-current="page"`.

**Tokens**
- [ ] Changing one colour in `theme.json` changes the header, the footer and the buttons.
- [ ] No hardcoded hex anywhere outside `theme.json`:
      `grep -rEn "#[0-9a-fA-F]{3,8}" --include=*.css --include=*.php .` returns nothing.
- [ ] Every `--fm-*` used in `style.css` is defined in the `:root` bridge.

**Structure and accessibility**
- [ ] Exactly one `<h1>` per page, and it comes from the content, not the template.
- [ ] One `<header>`, one `<main>`, one `<footer>` — no duplicate landmarks.
- [ ] Tab from the top of the page: the skip link appears first and reaches `#fm-content`.
- [ ] Focus is visible on every link and button.
- [ ] Nav links are at least 44px tall.

**Responsiveness and speed**
- [ ] 375px, 820px, 1440px — no horizontal scroll on the body.
- [ ] No JavaScript loaded on the front end except what core emits.
- [ ] Mobile PageSpeed 85+.

**404 and fallbacks**
- [ ] An unknown URL renders `404.html` inside the same shell.
- [ ] Deactivating and reactivating the theme loses no content.

**Explicitly NOT checked on this branch** — these need the runtime plugin and move to
step 2: that a Site Owner can edit settings, that `Appearance → Editor` is unreachable by
that role, and that capabilities are enforced rather than hidden. Testing as an
administrator proves nothing about either.
