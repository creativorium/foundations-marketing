# Halo Skin Studio

A sellable site template: a one-room skincare studio, built from the client's two canvas
files (`doc/client-html/theme/Halo Template (standalone) (2).html` and
`Halo Privacy Policy (standalone).html`).

Owns the SEO phrase **skincare clinic website design UK** (doc/SEO-AND-PERFORMANCE.md).
Onyx owns the aesthetics half of what used to be one row, so the two clinical designs
never compete for a phrase.

## What is here

- **Two pages.** `home` and `privacy`. About, Treatments and Packages are sections of the
  homepage in this design, reached by in-page anchors, not separate pages.
- **Fourteen blocks**, all namespaced `foundations/halo-*` and carried by this folder
  alone (how-to-work.md §2.1b).
- **Both webfonts**, Latin subsets, extracted from the canvas — see `assets/fonts/`.

## What is NOT here, and must be before release

- **Photography.** `content/media/` holds two drawn placeholders, not pictures. The
  before-and-after slots ship empty on purpose: client photographs are special category
  data, and each needs written consent before it is published.
- **A form endpoint.** `halo-contact` posts nowhere until one is set, and says so with the
  submit button disabled. Form adapters are out of a template's scope
  (team-template-workflow.md).
- **A `/templates/skincare-clinic-website-design/` detail page** on the marketing site.
  A template with no detail page cannot be published — catalogue cards would 404.
