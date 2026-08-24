/**
 * Site-wide front-end bundle. Vanilla only — no framework ships to the browser.
 * Blocks bring their own behaviour in plugin/src/blocks/<name>/.
 */
import './styles/main.scss';

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
