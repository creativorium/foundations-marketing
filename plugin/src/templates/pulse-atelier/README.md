# Pulse Atelier

A separate `pulse-atelier` template. The existing Pulse template and catalogue entry are unchanged.

The fidelity revision uses the decoded **Pulse Atelier Reference.html** as the source of truth. See [REFERENCE-AUDIT.md](REFERENCE-AUDIT.md) for extracted dimensions and behavior, [VERIFICATION.md](VERIFICATION.md) for comparison results, and [FILES.md](FILES.md) for the complete inventory.

## Architecture and editing

- `content.html` is a Gutenberg composition, not a standalone HTML document.
- `foundations/pulse-atelier` provides the reference header/footer and its in-page contact/privacy navigation states. These states are needed by homepage links; no separate contact.html or privacy.html is created.
- `foundations/pulse-atelier-section` provides twelve selectable, individually reorderable reference layouts. Text, links, images, sessions, timetable, testimonials and FAQ are editable through the block settings. Lists use the existing `Repeater.jsx`; images use the Gutenberg Media Library. Source text is escaped on output and layout variants are allowlisted.
- Both blocks use the existing PHP block discovery, shared editor bundle and shared frontend bundle. Reused utilities: `fm_wrapper()`, `Repeater.jsx`, Gutenberg `InnerBlocks`, `InspectorControls`, `MediaUpload` and `ServerSideRender`.
- `theme/page-templates/pulse-atelier.php` is a dedicated WordPress page shell. It retains WordPress head/footer hooks, the main landmark and skip link, while allowing the editable reference header/footer to render once. Select this page template for a matching preview.
- `_reference.scss` contains the extracted, scoped styles. The theme reset is neutralized only inside `.fm-pulse-atelier`. Other templates retain their styles and headers.
- The earlier seven editorial blocks remain in the working tree but are not used by this composition.

The image backgrounds are the six original JPEGs, without recompression. Empty image URLs use portable plugin-relative defaults; Media Library selections store their URLs. Migrate uploaded media URLs when moving a customized copy to another installation.

Fonts are the supplied Latin WOFF2 files: **Hanken Grotesk** (300/400/500/600) and **Instrument Serif** (400 normal and italic). Private CSS family aliases prevent collisions with the main site. The font directory includes the upstream SIL Open Font License notices. No artifact JavaScript or bundler runtime ships with the template.

## Behavior and reference limitations

Header/section anchors use native smooth scrolling with no added header offset. Testimonials switch immediately on ordinary buttons, with native Enter/Space activation. FAQ opens one answer at a time; the first starts open and clicking the open question closes it. Added accessible names and expanded/pressed states do not change the appearance.

The original newsletter and contact controls do **not** submit, validate, send email or show success messages. This implementation preserves that behavior. There is no booking integration or mailing backend. Contact/privacy links switch in-page views exactly as in the reference.

The source defines no mobile breakpoints and no mobile menu. Its desktop grids and fixed navigation remain clipped on narrow screens. This fidelity revision retains that limitation rather than presenting an invented mobile design as a reproduction.

## Preview locally

The generated isolated preview is `%TEMP%/pulse-atelier-review/index.html`. Open it in Chrome. It includes the actual PHP-rendered blocks, local images/fonts and compiled theme/block styles.

To regenerate from the repository root:

```powershell
npm run build
node plugin/src/templates/pulse-atelier/tests/browser-smoke.mjs --php "C:/Users/detarohila/AppData/Roaming/Local/lightning-services/php-8.2.29+0/bin/win64/php.exe" --wordpress "C:/Users/detarohila/Local Sites/foundations-marketing/app/public" --chrome "C:/Program Files/Google/Chrome/Application/chrome.exe" --reference "C:/Users/detarohila/Downloads/Theme/Pulse Atelier Reference.html"
```

Add `--screenshot` to refresh the 1200x900 catalogue image.

For WordPress: start **foundations-marketing** in Local, activate Foundations and Foundations Blocks, and open a dedicated draft page at `http://foundations-marketing.local/wp-admin/`. Select the **Pulse Atelier** page template. Paste `content.html` into Gutenberg's Code editor, then return to Visual editor. Use List View to select sections and their block settings to edit content. Save and Preview. Do not paste the standalone preview HTML into Gutenberg.

For ongoing changes use `npm run watch:editor` and `npm run watch:frontend` in separate terminals.

## Catalogue handoff

`template.json` describes a separate **Pulse Atelier** `site_template` record, slug `pulse-atelier`, with its own image, copy and demo URL. The site's catalogue queries database records; it does not import this JSON automatically. No database record has been created or published by this revision. The owner handoff from the first phase remains outstanding: create the separate demo page, create the separate catalogue record from `template.json`, attach `screenshot.webp`, and set the demo's absolute URL. Do not replace the existing Pulse entry. The target SEO phrase is **boutique pilates website template UK**.

## Tests

- `render-smoke.php <wordpress-directory>` uses the installed WordPress parser and real block PHP with offline service adapters; validates schemas, rendering, unique IDs and escaping.
- `inspect-reference.mjs --reference <file> --chrome <exe> --output <folder>` captures source DOM, computed styles and screenshots at 1440/820/375.
- `compare-reference.mjs --preview <index.html> --chrome <exe> --baseline <reference-folder> --output <folder>` compares section/element geometry and styles against those captures.
- `browser-smoke.mjs` runs the direct reference interaction comparison and writes evidence under `%TEMP%/pulse-atelier-review/`.
- `pixel-diff.php <reference.png> <actual.png> [difference.png]` compares every pixel; requires PHP GD and `memory_limit=512M` for full-page screenshots.

These checks do not replace authenticated WordPress editor/save/reload testing or cross-browser checks. No Lighthouse score is claimed.
