# Ember

Ember reconstructs the supplied `Ember.html` massage therapist reference as a portable,
server-rendered Gutenberg design. It uses five editable page blocks plus namespaced
header and footer blocks. Source: `Notes/Approved Templates-20260920T133742Z-1-001/Approved Templates/Ember/Ember.html`.
The hero retains the source's gradients, texture and botanical line art. The separately
supplied `about me_.jpg` is compressed to WebP and included in the About frame, with a
native image picker and portable attachment references.

Fonts are self-hosted Cormorant Garamond and DM Sans subsets under `assets/fonts/`.

Build and package:

```powershell
npm run build
npm run package -- ember
```

After importing the generated archive in Foundations Delivery, open:
`/templates/ember/demo/` (using the imported design slug shown by Delivery).

## Editable settings and customer setup

Page text, treatments, testimonials and the About photograph are editable in Gutenberg.
Site Settings controls the wordmark/logo, navigation, email, address, hours, cancellation
notice and optional booking calendar URL. Setting a booking URL adds a booking button;
the default empty state retains the approved widget placeholder. Update its explanatory
copy for the customer's actual booking service. Palette and font choices resolve through
`theme.json`. The supplied design has no privacy-page content; supply the customer's
approved policy and add its link before customer handover.

## Verification — 28 September 2026

Compared every section with the approved HTML at 375, 820 and 1440px, including the
source's paragraph cascade, botanical hero, treatments and testimonials. No horizontal
overflow or broken images. Intentional additions are the supplied About photo, visible
keyboard focus, reduced-motion support, fixed-header anchor clearance and functional
booking/settings controls. No source popup or third-party frontend dependency is added.

Built the independent delivery ZIP and imported it on the existing customer fixture.
Verified Gutenberg edit/save/reload, imported image IDs, and a customer export/import
round trip with a new attachment ID. The temporary fixture content is removed afterward
and the original fixture restored. Marketing Local retains the Ember master for review.
