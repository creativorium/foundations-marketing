# Pulse Atelier reference extraction

The original bundled file is the source of truth. This extraction preceded the fidelity rewrite.
The unpacked website and its application logic were inspected separately from the bundler/runtime.

## Complete evidence

- `doc/client-html/extracted/pulse-atelier-reference/source.html`: complete original DOM, inline styles, fonts and application class.
- `styles.css`: every original style element, including font-face declarations.
- `data.json`: exact data returned by the inspected application class, including sessions, quotes, FAQs, timetable and privacy text.
- `source-spec.json`: every inline style declaration, source hash, section order and interaction audit.
- `browser/{375,820,1440}.json`: actual rendered DOM and computed styles/rectangles for every homepage element, plus all stylesheet rules and header/footer metrics.
- `browser/{375,820,1440}.png`: original-reference full-page screenshots in Chrome at a 900px viewport height.
- The UUID-named JS/font/image files are forensic reference material only; the runtime files are not copied into the implementation.

## Layout findings

The original uses **content-box**, not the Foundations theme's border-box reset.
A 1240px max-width section with 60px left/right padding therefore occupies up to 1360px.
The body uses Hanken Grotesk 300, 16px, normal line height. Display text uses Instrument Serif 400,
with a separate italic face. Hanken Grotesk weights 300/400/500/600 share a variable-font file.

| Component | Exact source geometry/type |
|---|---|
| Header | Fixed top/left/right 0; z-index100; padding20px 40px; blurred cream 0.86; 1px bottom border; 27px serif wordmark; nav gap34px, 12.5px labels, tracking0.14em; button padding11px 20px, radius100px. |
| Hero | Grid1.05fr 1fr; min-height100vh; copy padding150px 60px 70px; heading clamp(52px,6.4vw,104px), line-height0.98, tracking-0.02em; body18px/1.65, max-width440px; photo cover/center, min-height60vh. |
| Credentials | Four equal columns; each padding30px 34px; font12.5px/tracking0.12em; 1px borders. |
| Practice | padding130px 60px; max-width1240px; text grid0.85fr 1fr/gap80; statement clamp30/3.4vw/46, line-height1.24; paragraphs16.5px/1.8, grid1fr 1fr/gap44; gallery1.4fr 1fr/gap22, margin-top80, height440. |
| Teacher | Recessed background; padding130px 60px; inner max-width1240; grid0.9fr 1.1fr/gap80; photo620px; heading clamp38/4.4vw/62, line-height1.06; paragraphs17px/1.85; stats gap40, top padding28, 38px figures. |
| Sessions | padding130px 60px/max-width1240; intro flex/end/wrap, gap20, bottom60; heading clamp38/4.4vw/62, line-height1.06; intro15px/1.7/max300; rows grid0.9fr 1.4fr 0.5fr 0.4fr/gap30, padding34px 10px; title30px, description15.5px/1.7, duration13px, price28px. |
| Process | padding120px 60px; inner max1240; heading clamp34/3.8vw/54, line-height1.1, max640, bottom70; three columns/gap50; top rule/padding26; numbers44px. |
| Audience | padding130px 60px/max1240; grid1fr 1fr/gap80; image540px; heading clamp34/3.8vw/54, line-height1.08, bottom40; list grid24px 1fr/gap18, padding20px 0; 9px dot. |
| Timetable | padding120px 60px; inner max1240; five columns/gap16; cards padding26px 22px/min-height250; days12px, times22px, labels14px; note top34. |
| Testimonials | padding130px 60px/max1100; eyebrow bottom40; italic quote clamp28/3.6vw/46, line-height1.3/bottom34; author12.5px/tracking0.16em; dots margin-top44, gap10, 9x9px. No additional heading. |
| FAQ | padding120px 60px; inner max1000; heading clamp34/3.8vw/54/line-height1.08/bottom54; buttons padding26px 4px, 19px questions/26px signs; answers16px/1.8 with padding0 60px 30px 4px/max760. |
| Newsletter | padding110px 60px; inner max900; grid1fr 1fr/gap60; heading clamp30/3.4vw/44/1.12, bottom16; description16px/1.75; input16px, send12.5px; underline/padding-bottom12; fine print13px/top14. |
| Booking | min-height600px; centered background cover; overlay rgba(34,32,27,0.55); content padding100px 40px; heading clamp42/5.4vw/84/1.02/bottom24; paragraph18px/bottom40; button padding18px 40px/radius100px. |
| Footer | padding90px 60px 34px; inner max1240; grid1.4fr 1fr 1fr 1fr/gap50; serif wordmark34px; meta margin-top60/padding-top26/border1px; footer font13.5px. |

## Colours

Paper #EFEAE0; ink #22201B; olive #5A6046; recessed #E5DFD2; light olive #A8B08C.
All exact opacity variants, borders, radii, margins and image styles are recorded per node in the evidence above.

## Responsive behavior actually present

**There are no screen media queries and no mobile menu.** The only runtime media query is for print.
The source keeps its desktop grids at narrow widths and clips horizontal overflow at the page wrapper.
The fixed navigation overflows on phones. Viewport-dependent clamp(), wrapping flex rows and intrinsic
content determine the remaining changes. The reconstruction retains this behavior for direct 1:1
comparisons, rather than claim an invented mobile layout came from the source.

| Viewport width | Header height | Document height |
|---|---|---|
| 375 | 114 | 12677 |
| 820 | 80 | 9220 |
| 1440 | 80 | 8984 |

## Interaction findings

- Homepage anchors use native smooth scrolling, with no sticky-header offset.
- Contact/privacy links switch in-page views, prevent default and scroll to top. Returning home from
  another view scrolls to the top; the original does not jump to that link's section on the same click.
- Session rows have no arrow. The added arrow in the first implementation was incorrect. Whole rows
  link to #book; hover sets #E5DFD2 instantly (transition-duration0s).
- Testimonials use ordinary buttons, not radios: three 9px dots, immediate replacement, no autoplay,
  and only native Enter/Space button keyboard behavior. Inactive fill is rgba(34,32,27,0.22).
- FAQ starts with item0 open and supports at most one open answer; toggling the open item closes it.
- Newsletter and contact UI have no form element or submission handler. Clicking Send it/Send message
  has no effect in the reference; entering an email does not initiate a request or validation flow.
- Link/button hover values and native focus styles are captured in the DOM/styles evidence. Text fields
  explicitly remove their outlines. pulseUp keyframes are present but never used.

The homepage links require the original contact/privacy **in-page states**. These are restored
inside the same template; no separate contact.html/privacy.html page packages are required.
