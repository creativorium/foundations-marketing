# Meridian Modern

Reusable acupuncture clinic template based on the supplied Meridian-Modern HTML. The sample business is Wren Acupuncture. `meridian-modern` is a separate design slug because an older `meridian` already exists in the development Delivery catalogue.

## Structure and editing

Five pages: Home, About, Conditions, Contact, Privacy. Thirteen private server-rendered blocks use `foundations/meridian-modern-*`, with dynamic `save: () => null`. Text, links, image selections and ordered repeaters are editable in Gutenberg. Business details, navigation, logo, homepage link, CTA and optional booking URL are managed through Site Settings.

The capsule header and footer intentionally fork the shared shell as private template blocks. They remain in `parts/`, outside page content. Future shared-shell fixes must be reviewed for these two blocks. The mobile menu supports Escape and visible focus; FAQ uses native details; testimonials do not auto-rotate. The horizontally scrolling process cards are keyboard-focusable.

The source overlays become real Contact and Privacy pages. Archivo replaces the source's Bricolage Grotesque to follow the project's two-font rule. Archivo and Instrument Serif are self-hosted as Latin WOFF2 data in `_fonts.scss`, with OFL licenses under `assets/fonts/`. The accent is slightly darker for contrast. Images are compressed local WebP copies of the three Unsplash URLs supplied by the source HTML. Image IDs and page URLs use import tokens; no source-site IDs or development URLs are embedded in starter content.

The form opens an email draft using the email from Site Settings. It never claims that an enquiry was sent. An optional booking URL adds a direct booking action. No mail service, webhook, booking provider or new plugin dependency is bundled.

## Build and preview

Run `npm run build`, then `npm run package -- meridian-modern`. Upload the generated outer delivery ZIP through Delivery on the internal WordPress site. Review the draft while signed into WP Admin. The compiled preview route uses the design slug:

`/templates/meridian-modern/demo/`

Interior routes append `about/`, `conditions/`, `contact/`, or `privacy/`. `demoSlug` retains `acupuncture-website-design` for the separately managed marketing/SEO detail page. The clinic's H1 and copy remain faithful to the supplied design; the catalogue detail page owns the marketing keyword “acupuncture website design UK”.

## Verification

- PHP syntax, 19 block-linter regression checks and block-markup checks pass.
- All five compiled preview pages checked at 375, 820 and 1440 pixels, with one H1 and one main landmark, no horizontal body overflow, missing images or unresolved tokens.
- All five pages saved and reloaded in Gutenberg with separate supported blocks and unchanged attributes.
- Menu, Escape, FAQ, testimonial controls and reduced-motion setting checked in Chromium.
- Packaged theme/plugin and starter content installed into a separate clean WordPress table set with no marketing plugin dependencies. All five pages pass the same responsive checks and Gutenberg save/reload checks.
- Site Owner can manage settings and cannot manage themes or install plugins. No marketing development URLs survive the customer import.
- Export/re-import checks preserve all five pages, three media assets and four image ID references. A shared-runtime limitation remains for URL fields after permalink changes; see below.

Evidence and local test scripts remain in gitignored `artifacts/` and `doc/`. Mobile Lighthouse/PageSpeed has not been measured. Sample clinical claims, testimonials, contact details and policy text come from the supplied mockup and need the customer's content review before a production release.

The clean-site importer resolves URL tokens before it changes the permalink structure and static homepage. Its exporter can then retain old `?page_id=` query strings when matching the new homepage URL. This affects a later customer export/re-import, including homepage anchor links. It is shared runtime work in `plugin-site/inc/tools.php` and `plugin-site/inc/bundle.php`, outside this template PR. The compiled Local draft preview is functional; customer re-delivery should wait for that runtime fix and a repeated URL round-trip test.
