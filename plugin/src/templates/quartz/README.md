# Quartz Facialist

An editable facialist template transcribed from `Notes/Approved Templates-20260920T133742Z-1-001/Approved Templates/Quartz/Quartz.html` and its two supplied photographs (`first phot.jpg` → hero, `about me photo.jpg` → About), compressed to WebP.

Pages: **Home** (hero, about, before & after, treatments, reviews, booking) and **Privacy policy**. The header and footer are template parts rendered from Site Settings; the menu links point at homepage sections through `{{page:home|url}}#…`, so they work from every page.

Target phrase: **facialist website design UK** (`/templates/facialist-website-design`) — chosen so Quartz does not compete with Halo (skincare clinic) or Onyx (aesthetics clinic). Confirm with the owner and add the row to `doc/SEO-AND-PERFORMANCE.md`.

## Intentional differences from the source HTML

- **Contrast (WCAG AA, how-to-work.md §8).** The source terra `#C06B52` gives 3.5:1 for small text on the ice background and 3.9:1 under white button text. Most accent text and filled buttons use `#A9573F` (4.7:1+); the original terra is retained for the source booking background, review accents and decorative marks. Low-opacity white text on the treatments, booking and footer bands was raised to pass AA, and `terra-light` is `#EDB0A2` so prices pass on deep blue.
- **Review controls.** Compact 5px dots with 8px gaps and 36px arrow squares match the approved design. Arrow hit areas are enlarged to 46px; swipe and keyboard scrolling are also available.
- **Treatments row** shows a thin scrollbar and is keyboard-focusable, so the row visibly continues; the source hid the scrollbar.
- **Reviews slider** uses manual arrows, dots and swipe only (owner decision). No autoplay or pause button. Reduced motion disables animated scrolling.
- **Before & after** cards accept real photos; with no photo they show the source's colour washes.
- **Footer credit** reads "Website by Foundations Marketing" and links to the studio (SEO strategy backlink).
- The booking panel keeps the source's widget placeholder text; replace it with the customer's Fresha/Acuity/Vagaro/Calendly link via the **Booking link** Site Setting or the block's button URL.

## Kept as the source

- **No mobile menu.** Under 768px the header shows only the centred wordmark and the section links are hidden, as in the source. Visitors on phones reach sections by scrolling; the footer links stay visible.

## Before a customer release

Replace the placeholder name, location, email, statistics, treatment prices, reviews and privacy-policy brackets. Confirm permission for all photographs and testimonials. Re-run a fresh Delivery import, Gutenberg save/reload and the 375 / 820 / 1440px checks.

## PR acceptance (24 September 2026)

Compared with the approved Quartz HTML at 375, 820 and 1440px. The source has empty hero/About image panels; the two supplied photographs are applied in those panels. Source heading line breaks are preserved in editable multiline fields. Before/after photos were not supplied and remain the source's gradient placeholders.

Verified the compiled delivery package on foundations-fixture: both page imports, image remapping, responsive rendering, review arrows and reduced motion, privacy navigation, and Gutenberg content edits saved and reloaded on both pages. No browser script errors. The existing fixture is restored after acceptance; the Quartz master is available separately on the marketing Local site.

## Layout correction (28 September 2026)

Rechecked all homepage sections against the approved HTML at 375, 820 and 1440px. Restored source header spacing, hero line break and mobile height, About label typography, result captions, booking line height and footer spacing. Reviews use the original straight quote mark and compact dots with manual navigation only. The additional privacy link, supplied photographs, readable text contrast and keyboard/touch support remain intentional additions.
