# Quartz Facialist

An editable facialist template transcribed from `doc/theme/Quartz/Quartz.html` and its two supplied photographs (`first phot.jpg` → hero, `about me photo.jpg` → About), compressed to WebP.

Pages: **Home** (hero, about, before & after, treatments, reviews, booking) and **Privacy policy**. The header and footer are template parts rendered from Site Settings; the menu links point at homepage sections through `{{page:home|url}}#…`, so they work from every page.

Target phrase: **facialist website design UK** (`/templates/facialist-website-design`) — chosen so Quartz does not compete with Halo (skincare clinic) or Onyx (aesthetics clinic). Confirm with the owner and add the row to `doc/SEO-AND-PERFORMANCE.md`.

## Intentional differences from the source HTML

- **Contrast (WCAG AA, how-to-work.md §8).** The source terra `#C06B52` gives 3.5:1 for small text on the ice background and 3.9:1 under white button text. Text and filled buttons use `#A9573F` (4.7:1+); the original terra is kept only for decorative marks (the About corner square, divider lines). Low-opacity white text on the treatments, booking and footer bands was raised to pass AA, and `terra-light` is `#EDB0A2` so prices pass on deep blue.
- **Mobile navigation.** The source hides the menu links under 768px with no replacement; Quartz adds a Menu toggle (`site-header/view.js`). With JavaScript off the links stay visible.
- **Tap targets** are 44×44px (slider arrows and dots, nav and footer links).
- **Treatments row** shows a thin scrollbar and is keyboard-focusable, so the row visibly continues; the source hid the scrollbar.
- **Reviews slider** is a scroll-snap row that works without JavaScript; autoplay is off under `prefers-reduced-motion` and pauses on hover/focus.
- **Before & after** cards accept real photos; with no photo they show the source's colour washes.
- **Footer credit** reads "Website by Foundations Marketing" and links to the studio (SEO strategy backlink).
- The booking panel keeps the source's widget placeholder text; replace it with the customer's Fresha/Acuity/Vagaro/Calendly link via the **Booking link** Site Setting or the block's button URL.

## Before a customer release

Replace the placeholder name, location, email, statistics, treatment prices, reviews and privacy-policy brackets. Confirm permission for all photographs and testimonials. Re-run a fresh Delivery import, Gutenberg save/reload and the 375 / 820 / 1440px checks.
