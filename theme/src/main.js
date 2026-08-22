/**
 * Site-wide front-end bundle. Vanilla only — no framework ships to the browser.
 * Blocks bring their own behaviour in plugin/src/blocks/<name>/.
 */
import './styles/main.scss';

// Mobile nav toggle. The header markup lives in theme/header.php.
const nav = document.querySelector('[data-fm-nav]');
const toggle = document.querySelector('[data-fm-nav-toggle]');

if (nav && toggle) {
  toggle.addEventListener('click', () => {
    const open = nav.getAttribute('data-open') === 'true';
    nav.setAttribute('data-open', String(!open));
    toggle.setAttribute('aria-expanded', String(!open));
  });
}
