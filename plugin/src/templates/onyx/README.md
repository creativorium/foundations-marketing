# Onyx Aesthetics

A sellable multi-page design for a **single-practitioner aesthetics clinic** — injectables,
advanced skin and laser. Built from the client canvas file
`doc/client-html/theme/Onyx Template (standalone).html`, decoded to
`doc/client-html/extracted/Onyx.html`.

| | |
|---|---|
| Folder slug | `onyx` |
| Block namespace | `foundations/onyx-*`, category `foundations-onyx` |
| Pages | home, treatments, about, contact, privacy |
| Target phrase | aesthetics clinic website design UK |
| Demo slug | `aesthetics-clinic-website-design` |
| Shell | shared `site-header` / `site-footer`, `inline` + `columns` variants, restyled from `style.scss` — **not forked** |

## The SEO phrase is a split, and the owner should confirm it

`doc/SEO-AND-PERFORMANCE.md` gives Halo the row *"skincare clinic / aesthetics website
design UK"* — one row carrying two phrases. Onyx is the same clinical family as Halo, and
two designs must never target one phrase (how-to-work.md §6a), so Onyx takes the
**aesthetics** half and Halo keeps **skincare**.

Nothing in the blocks or the content depends on that choice. Changing it is `demoSlug` in
`template.json` plus the row in the SEO table.

## What is not finished

Stated plainly so none of it is mistaken for done:

- **The photography is placeholder.** `content/media/onyx-hero.webp` and
  `onyx-practitioner.webp` are generated hatched plates, drawn to look exactly like the
  design's own empty-image slots so they cannot be mistaken for clinic photos. They are
  real files rather than empty slots on purpose: an install with no media exercises none
  of the attachment-id remapping, which is the part most likely to break
  (`fixtures/base-three-page/README.md`). Replace both with licensed, compressed images
  before release.
- **The enquiry form has no destination.** Nothing in this design processes a submission
  — a form adapter is separate, explicitly-scoped work
  (`team-template-workflow.md`, "Testing external integrations"). With `action` empty the
  submit button renders disabled and says why, rather than looking like it works and
  dropping every enquiry. Set the endpoint in the block's settings before launch.
- **No webfont files ship.** `theme.json` asks for Archivo and Instrument Sans and falls
  back to a real system stack, so the design is legible with nothing downloaded, but it is
  not yet the drawn typography. Self-host the two woff2 files per `doc/FONTS.md`; never
  point the families at a third-party CDN — `theme-customer-base/inc/assets.php` forbids
  that on a customer site.
- **No `/templates/aesthetics-clinic-website-design/` detail page exists**, so the design
  cannot be published to the catalogue yet — a card would point at a 404
  (`doc/TEMPLATES.md`).

## Blocks

Eleven, all namespaced to this design. Header and footer are **not** among them: they are
the shared shell, rendered from `parts/` and Site Settings.

| Block | Used on | Notes |
|---|---|---|
| `onyx-hero` | home | Owns the homepage H1. Figures render as a `<dl>`. Hero image is eager/LCP. |
| `onyx-pillars` | home | Sticky intro beside linked cards. A card with no URL renders as text, not `<a href="#">`. |
| `onyx-steps` | home | `<ol>` — the sequence is the content. Visible numerals are `aria-hidden`. |
| `onyx-feature` | home | Inverse band. Uses `--fm-inverse-muted`, which clears AA there; canvas muted does not. |
| `onyx-reviews` | home | `<blockquote>` + `<figcaption>`. Stars are decorative; the rating is words. |
| `onyx-cta` | home | Dark closing panel. |
| `onyx-page-hero` | every interior page | Owns each interior H1. `multiple: false` enforces one per page. |
| `onyx-menu` | treatments | A real `<table>` with hidden column headers; stacks to cards under 820px. |
| `onyx-story` | about | Sticky portrait, prose, `<dl>` timeline. Renders no heading — the page hero owns it. |
| `onyx-contact` | contact | Real labels, visually hidden. Policy link sits outside the label on purpose. |
| `onyx-policy` | privacy | Contents list is derived from the sections, never stored twice. |

`components/Repeater.jsx` is a deliberate copy of the marketing site's, not an import of
it — see the note at the top of that file.

## Previewing it

The design is compiled and previewed through the delivery pipeline
(`delivery-operations.md`), not through the legacy `plugin/inc/demo.php` route:

```bash
npm run build
npm run package -- onyx
```

Then upload `dist-themes/onyx/<id>/onyx-delivery.zip` under **Delivery** in WP Admin. It
lands as a **draft**, previewable at `/templates/onyx/demo/` and
`/templates/onyx/demo/<page>/` while logged in as an administrator. Publishing the demo is
a separate, deliberate step and should wait until the unfinished items above are closed.

## Checks run

- `npm run build` — `lint:php` 87 files, `validate:blocks` 16 files, all four Vite targets.
- All five pages parse into registered blocks, contain no raw HTML outside block
  delimiters, and **re-serialise byte-identical**, so the editor does not rewrite them on
  save.
- All five pages at 375 / 820 / 1440px: one `<main>`, exactly one `<h1>`, no horizontal
  overflow, no broken images, no unresolved import tokens, no console errors, every tap
  target at least 44×44px.

**Verified 16 September 2026:** all five existing master pages opened in Gutenberg, saved, and reloaded with every block valid. All 15 responsive preview checks passed with no browser JavaScript errors. No duplicate master or customer project was created.

**Still pending:** an Onyx installation on the customer fixture and its customer-side editor checks. The fixture database was unavailable during this follow-up. Packaging passes, but that does not replace an installation test. Launch items above remain open.
