# Foundations Customer Base

The minimal block theme every customer site theme is generated from.

**This theme is never shipped as-is.** `scripts/package.mjs` assembles a
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
| Front-end stylesheet enqueue | **Built** — was broken in the first version, see `inc/assets.php` |
| Site Settings **contract** — schema, defaults, read helpers | **Built** |
| Site Settings **screen**, Site Owner role, capabilities | **Built** in `plugin-site/` |
| Packager | **Built** in `scripts/package.mjs` |
| Importer | **Built** in `plugin-site/` |

Settings are edited through Site Settings. Generated releases include the customer runtime. See [delivery-verification.md](../delivery-verification.md) for the completed fixture checks and remaining design-specific acceptance.

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
| A different **arrangement** of the same header elements | Set `variant` in the design's `parts/header.html`, restyle via its `style.scss` | Fork `blocks/site-header/` |
| A genuinely different header **structure** — different elements or nesting | Ship the design's own `parts/header.html` **and** `blocks/<slug>-site-header/`; the packager prefers a design's own part | Bend the shared one with CSS until it breaks |

The rule behind all of these: **anything true of one design belongs to that design.** The
base holds only what is true of all of them. If you are editing the base to make one design
work, it is in the wrong place.

**On forking the shell:** the override path is deliberately more work than setting a
`variant`, because a forked header stops receiving base fixes — but it exists, and it is
not a failure to use it. CSS can rearrange elements that are already there; it cannot
invent markup a design needs and the shared header has no concept of. Forcing every future
design through the first one's structure would be the worse outcome. Record which designs
have forked, so a base fix can be applied to them by hand.

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
- **No custom frontend JavaScript in the shared shell**, including a burger menu. The nav is a list that wraps. A toggle
  is layout JS and the budget is 85+ mobile PageSpeed on shared hosting (§9). A design that
  truly needs one builds it as its own block, with its own script.
- **No hover-only submenus.** One level, always visible — a hover submenu is unreachable by
  touch and needs JS to fix.
- **No build step.** Plain CSS, no Vite target. The base is small enough not to need one,
  and the packager compiles per-design assets in step 3.
- **No webfonts.** System stacks until a design supplies its own; self-hosted, max two
  families (§9, `doc/FONTS.md`).

---

## Audit status — verified 2026-09-13

Installed and exercised on a separate WordPress 7.1 site (`foundations-fixture.local`),
not the marketing site. **Passed:** all three pages plus 404 at 375/820/1440 with no
horizontal overflow; stylesheet, tokens, images and navigation loading; exactly one H1,
header, main and footer per page; **all 28 blocks still valid after a real editor save and
reload**; a phone change reaching every page; empty logo/nav/CTA fallbacks; a palette change
reaching rendered buttons; skip-link keyboard path; content and settings surviving a theme
switch and back.

**Fixed since that audit:** tap targets (see below).

**Follow-up status:**

| Item | Where it goes |
|---|---|
| PageSpeed 85+ — never measured, and local rendering is not a performance score | needs a deployed site |
| Site Owner capabilities, Site Settings screen | Implemented and tested; see `delivery-verification.md` |
| Shell blocks in the Site Editor | Editor registration implemented; full staff workflow remains a manual design check |
| Packager, importer, exporter | Implemented and round-trip tested; see `delivery-verification.md` |
| Full a11y audit, all header variants, nested nav, archive templates, WP/PHP version matrix | not scheduled |

### The tap-target fix, and what it says about checking only what was reported

The audit found header `Home`/`About` links about 38px wide against the project's 44×44
rule. The height was already right, which is exactly why it read as done. Checking the rest
of the stylesheet rather than only the reported line found **two more instances of the same
defect** that no one had measured:

- `.fm-nav__children .fm-nav__link` carried `min-height: 0`, removing the floor entirely —
  nested nav links were around 22px tall.
- `.fm-site-footer__list a` had a colour and no sizing at all — roughly 25px.

All three are fixed. A rule that applies to every tap target is not satisfied by fixing the
one that was measured.

---

## Acceptance checks

Install on a clean WordPress with the fixture — the manual path is in
[`fixtures/base-three-page/README.md`](../fixtures/base-three-page/README.md).

**Stylesheet actually loads** — this is where the first version failed
- [ ] View source on the front end: `style.css` is in the `<head>` with a `?ver=`.
- [ ] The shell is **styled on the page**, not only in the editor. `add_editor_style()`
      loads style.css for the editor independently, so a missing front-end enqueue looks
      correct while editing and unstyled on the site — check the page, not the editor.
- [ ] `getComputedStyle(document.documentElement).getPropertyValue('--fm-accent')` in the
      browser console returns a colour, not an empty string.

**Editor validation** — the lint is not a substitute
- [ ] Open each fixture page in the editor, **save, reload**: no "this block contains
      unexpected content" and no recovery prompt on any block.
- [ ] As an administrator, open the header and footer parts in the Site Editor and
      confirm their server-rendered previews. Editor registration now exists; an
      unsupported-block message is a failure, not expected behavior.
- [ ] `npm run validate:blocks` passes.

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
- [ ] Every tap target is at least 44px in **both** dimensions — header nav, **nested**
      nav children, footer links, and buttons. Measure width as well as height; short
      labels like "Home" fail on width while passing on height.

**Responsiveness and speed**
- [ ] 375px, 820px, 1440px — no horizontal scroll on the body.
- [ ] No JavaScript loaded on the front end except what core emits.
- [ ] Mobile PageSpeed 85+.

**404 and fallbacks**
- [ ] An unknown URL renders `404.html` inside the same shell.
- [ ] Deactivating and reactivating the theme loses no content.

Site Owner settings access and denial of Site Editor/plugin management are now tested with the runtime plugin. See [delivery-verification.md](../delivery-verification.md). The checklist above remains required for each real design.
