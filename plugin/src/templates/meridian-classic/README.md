# Meridian Classic

Reusable massage studio template based on `doc/theme/Meridian-Classic.html`. The sample business is Alder & Fern in Bath.

## Structure

The template contains Home, About, Conditions, Contact and Privacy pages. Thirteen private server-rendered blocks use the `foundations/meridian-classic-*` namespace. Page copy, links, images and repeaters remain editable in Gutenberg; business details and navigation come from Site Settings.

The custom header, mobile menu, footer, Contact overlay and Privacy overlay live in the header/footer template parts. Same-page navigation and booking CTAs use the shared smooth-scroll behavior and respect reduced-motion preferences. FAQ uses native details elements, and testimonials do not auto-rotate.

The design uses the supplied cream, charcoal and sage palette with a restrained editorial layout. Archivo and Instrument Serif replace the reference's external Google fonts to follow the project's self-hosted two-font rule. The three local WebP assets are sourced from the Unsplash URLs in the supplied HTML.

The enquiry form opens an email draft using the address in Site Settings and never claims that a request was sent.

## Build and preview

Run `npm run build`, then `npm run package -- meridian-classic`. The local Delivery preview route is:

`/templates/meridian-classic/demo/`

Interior pages append `about/`, `conditions/`, `contact/`, or `privacy/`.

## Verification

The required PHP lint, block-linter regression tests, block markup validation, Vite builds and Delivery packaging pass. The packaged template imports successfully into the Local Delivery site and the homepage renders with one H1, working section anchors, FAQ controls, testimonial controls and the booking form.
