/**
 * Site-wide front-end bundle. Vanilla only — no framework ships to the browser.
 * Blocks bring their own behaviour in plugin/src/blocks/<name>/.
 */
import './styles/main.scss';

// -----------------------------------------------------------------------------
// Template demos open in their own tab, and open it with a script.
//
// The markup already says target="_blank", so this changes nothing a reader can see.
// What it changes is who opened the tab: a tab opened by a link may not close itself,
// while one opened by window.open() may. That is what lets the demo's "Back to
// Foundations Marketing" hand the reader back to the catalogue tab they still have
// open — same scroll position, same filter — instead of loading the catalogue a
// second time.
//
// Modified clicks are left alone, so "open in new window" and middle-click still mean
// what they always meant, and with no JavaScript the anchor behaves as before.
// -----------------------------------------------------------------------------
document.addEventListener('click', event => {
  if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
  const link = event.target.closest('a[target="_blank"][data-fm-demo]');
  if (!link) return;
  // Same origin only: window.open without noopener hands the new tab a reference back
  // to this one, which is fine for our own demo and not fine for anywhere else.
  if (new URL(link.href, location.href).origin !== location.origin) return;
  const tab = window.open(link.href, '_blank');
  // A blocked pop-up returns null. Let the anchor do its normal job rather than
  // swallowing the click and opening nothing.
  if (tab) event.preventDefault();
});

// -----------------------------------------------------------------------------
// The old full-screen pixel curtain remains retired in _pixels.scss. The transition
// below keeps the current page visible while showing a small progress cue.
// -----------------------------------------------------------------------------

// -----------------------------------------------------------------------------
// Mobile nav — a right-hand drawer. Markup lives in theme/header.php, geometry and
// motion in styles/_header.scss. This file owns state only: it flips `data-open`
// and lets CSS decide what that looks like.
// -----------------------------------------------------------------------------
// Page navigation keeps the old document visible and adds a slim progress cue. The
// watchdog removes it if navigation is cancelled or the request is slow.
let pageNavigationPending = false;
let pageNavigationTimer;

const clearPageTransition = () => {
  pageNavigationPending = false;
  clearTimeout(pageNavigationTimer);
  document.body.classList.remove('fm-page-leaving');
  document.body.removeAttribute('aria-busy');
};

window.addEventListener('pageshow', clearPageTransition);

document.addEventListener('click', event => {
  if (pageNavigationPending || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
  const link = event.target.closest('a[href]');
  if (!link || link.hasAttribute('download') || link.matches('[target]:not([target="_self"]), [data-fm-demo]')) return;

  const url = new URL(link.href, location.href);
  if (!['http:', 'https:'].includes(url.protocol) || url.origin !== location.origin) return;
  if (url.pathname === location.pathname && url.search === location.search && url.hash) return;

  event.preventDefault();
  pageNavigationPending = true;
  document.body.classList.add('fm-page-leaving');
  document.body.setAttribute('aria-busy', 'true');

  const navigate = () => location.assign(url.href);
  if (matchMedia('(prefers-reduced-motion: reduce)').matches) navigate();
  else setTimeout(navigate, 140);

  pageNavigationTimer = setTimeout(clearPageTransition, 1000);
});

const nav = document.querySelector('[data-fm-nav]');
const toggle = document.querySelector('[data-fm-nav-toggle]');
const scrim = document.querySelector('[data-fm-nav-scrim]');

if (nav && toggle) {
  const isOpen = () => nav.getAttribute('data-open') === 'true';
  const firstLink = () => nav.querySelector('a');

  const setOpen = (open) => {
    nav.setAttribute('data-open', String(open));
    toggle.setAttribute('aria-expanded', String(open));
    if (scrim) {
      scrim.setAttribute('data-open', String(open));
    }
    // Stops the page scrolling behind the drawer under the reader's thumb.
    document.body.classList.toggle('fm-nav-is-open', open);
  };

  const close = ({ refocus = false } = {}) => {
    if (!isOpen()) {
      return;
    }
    setOpen(false);
    if (refocus) {
      toggle.focus();
    }
  };

  toggle.addEventListener('click', () => {
    const opening = !isOpen();
    setOpen(opening);
    if (opening) {
      // Send the keyboard where the eye already went.
      firstLink()?.focus();
    }
  });

  scrim?.addEventListener('click', () => close());

  // A link in the drawer navigates away; do not leave it open behind the new page.
  nav.addEventListener('click', (event) => {
    if (event.target.closest('a')) {
      close();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      close({ refocus: true });
    }
  });

  // Focus containment. The toggle doubles as the close button and sits outside the
  // <nav>, so it counts as inside the drawer for this purpose — tabbing off the end
  // of the list wraps back to the first link rather than walking into the page
  // underneath, which is hidden behind the scrim and cannot be seen.
  document.addEventListener('focusin', (event) => {
    if (!isOpen()) {
      return;
    }
    if (nav.contains(event.target) || toggle.contains(event.target)) {
      return;
    }
    firstLink()?.focus();
  });

  // Above the drawer breakpoint the nav is a row again. A state left open here
  // would keep <body> scroll-locked with no visible drawer to explain it.
  const wide = window.matchMedia('(min-width: 1025px)');
  wide.addEventListener('change', (event) => {
    if (event.matches) {
      close();
    }
  });
}
