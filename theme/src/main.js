/**
 * Site-wide front-end bundle. Vanilla only — no framework ships to the browser.
 * Blocks bring their own behaviour in plugin/src/blocks/<name>/.
 */
import './styles/main.scss';

// -----------------------------------------------------------------------------
// Page transition — the outgoing half.
//
// The cover is started and the navigation is left alone: the browser holds this
// document on screen until the next one is ready to paint, so the cover plays over
// exactly the wait there is and adds nothing to it.
//
// It waits GRACE milliseconds before covering, because how long a navigation takes is
// not knowable in advance and varies enormously on the same site: measured on dev, the
// same link commits in ~50ms once the speculation rules have prerendered it and ~1100ms
// cold. A cover that starts immediately is cut off part-way through by any navigation
// faster than its own 130ms, which is seen as a black shape flashing across the page
// and reads as a glitch rather than as a transition. Starting late means a navigation
// that beats the grace period takes this document away before anything is drawn — no
// wait, no motion — and only a navigation with a real wait is covered at all.
//
// It used to preventDefault and navigate on a 160ms timer instead. That charged every
// internal link a fixed 160ms — including the ones the speculation rules had already
// prerendered, which would otherwise have been instant — and it had two ways to look
// broken: a navigation slower than the recovery timer uncovered the page halfway
// through, and a navigation that never happened left the reader behind a black screen.
// Both read exactly as "the transition lagged, or stopped".
// -----------------------------------------------------------------------------
const curtain = document.querySelector('.fm-px');
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
// Long enough that a prerendered or cached page is simply instant, short enough that a
// wait the reader would notice is covered before they notice it.
const GRACE = 90;

let recover = 0;
let pending = 0;

if (curtain) {
  const cover = () => {
    window.clearTimeout(pending);
    pending = window.setTimeout(() => {
      curtain.dataset.leaving = '';
      // Only reached when the navigation does not happen after all — a download, a
      // cancelled unload, an extension swallowing the click. The page is never left
      // covered, and because nothing waits on this timer it can afford to be patient.
      window.clearTimeout(recover);
      recover = window.setTimeout(() => delete curtain.dataset.leaving, 4000);
    }, GRACE);
  };

  document.addEventListener('click', event => {
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || reducedMotion.matches) return;
    const link = event.target.closest('a[href]');
    if (!link || link.hasAttribute('download') || (link.target && link.target !== '_self')) return;
    const url = new URL(link.href, location.href);
    if (url.origin !== location.origin || !/^https?:$/.test(url.protocol) || url.pathname.startsWith('/wp-admin') || url.searchParams.has('add-to-cart')) return;
    // Same document: an in-page anchor is not a navigation and must not be covered.
    if (url.pathname === location.pathname && url.search === location.search) return;
    cover();
  });

  // Back from the bfcache: the reveal animation has already finished on this document
  // and will not run again, so the cover has to be taken off by hand.
  window.addEventListener('pageshow', () => {
    window.clearTimeout(pending);
    window.clearTimeout(recover);
    delete curtain.dataset.leaving;
  });
}

// -----------------------------------------------------------------------------
// Mobile nav — a right-hand drawer. Markup lives in theme/header.php, geometry and
// motion in styles/_header.scss. This file owns state only: it flips `data-open`
// and lets CSS decide what that looks like.
// -----------------------------------------------------------------------------
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
