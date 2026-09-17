# Verification - 17 September 2026

## Scope and environment

Compared the actual original bundled reference with the PHP-rendered Gutenberg composition in the same installed headless Chrome, on Windows, device scale factor 1, viewport height 900px. The preview loads the real compiled theme and block styles, original JPEGs and supplied fonts. No mock layout was substituted.

Source SHA-256: `cb2813020c0fb5f26a0c6a6fc20bf26de10652f304f427eff7d040126f3d63bc`.

## Full homepage comparison

| Width | Reference height | Implementation height | Differing pixels | Geometry/style differences |
|---|---:|---:|---:|---:|
| 375 | 12677 | 12677 | 0 | 0 |
| 820 | 9220 | 9220 | 0 | 0 |
| 1440 | 8984 | 8984 | 0 | 0 |

Every screenshot pixel was compared with PHP GD. The geometry/style audit compares all twelve sections, visible descendant elements (excluding runtime-only interpolation spans), header and footer. It includes text decoration, borders, spacing, grid sizes, typography, colors and transitions. The direct image comparison also covers the complete header/footer content, image crops and glyph rendering.

## Interaction comparison

The full suite passed **29 states per viewport, 87 total**:

- Initial content, all three testimonial selections and native Enter activation.
- Each FAQ, all answers closed, and native Space activation.
- Session row, header CTA, hero CTA and booking CTA hover.
- Newsletter input focus and the reference's no-op button behavior.
- Privacy/contact view changes, returning home and all five navigation section anchors.
- Contact's no-op button behavior and footer navigation.

The screenshot audit found and corrected theme hover underlines and testimonial glyph kerning caused by replacing interpolation spans. The affected nine states per viewport were then rerun against the source: **27 targeted state checks passed**. All **48 paired interaction screenshots** now have **zero differing pixels**, including testimonial selections, FAQ answers, CTA/session hovers, newsletter UI, contact and privacy views.

## Build and code checks

- `npm run build`: passed all Vite targets and generated error pages.
- Updated editor/frontend targets rebuilt after the final style/interaction fixes; no build errors or warnings.
- PHP lint: all 22 new/revised PHP files passed.
- Offline render smoke test: passed with WordPress's installed block parser and real rendering code; checks schemas, twelve sections, header/footer, FAQ/testimonial controls, unique IDs, escaped text, safe URL/media handling and the layout allowlist.
- `git diff --check`: passed (Git only reports the existing CRLF-to-LF normalization notice for frontend.js).

## Exact fonts and assets

The original six JPEG images are copied without recompression. The supplied Latin WOFF2 files are used under private family aliases to avoid changing other pages:

- Hanken Grotesk variable normal, weights 300/400/500/600 (`Pulse Atelier Hanken`).
- Instrument Serif normal 400 (`Pulse Atelier Instrument`).
- Instrument Serif italic 400 (`Pulse Atelier Instrument`).

All three font faces loaded in every browser comparison. Upstream SIL Open Font License notices are included alongside the assets.

## Remaining differences and limits

No remaining visual or behavioral discrepancy was detected in the verified isolated Chrome states. This is a statement about these measured cases, not a guarantee across every browser or WordPress installation.

Nonvisual implementation differences: server-rendered native blocks replace the artifact runtime; closed FAQ answers and inactive views use `hidden` instead of being unmounted. Accessible button names, pressed/expanded states and input names were added. No bundler/runtime is shipped.

The original has no mobile breakpoints or mobile menu; its clipped narrow-screen layouts remain identical. Newsletter/contact controls still do not submit because the original has no handler or backend. No service integration was invented.

Local now responds at `http://foundations-marketing.local`. Authenticated Gutenberg editor/save/reload checks and catalogue database activation were not performed; the owner handoff is documented in README.md. No cross-browser, print, Lighthouse or production deployment result is claimed. Nothing was committed or pushed.

## Evidence and preview

- Isolated preview: `%TEMP%/pulse-atelier-review/index.html`.
- Full-page screenshots and measured styles: `%TEMP%/pulse-atelier-review/comparison/`.
- Pixel metrics: `%TEMP%/pulse-atelier-review/pixels.json`.
- Interaction screenshots: `%TEMP%/pulse-atelier-review/interactions/`.
- Interaction reports: `interactions.json`, `interactions-visual.json`, `interaction-pixels.json` in the same temporary review folder.
- Original decoded source and baseline captures: `doc/client-html/extracted/pulse-atelier-reference/` (gitignored forensic material only).
