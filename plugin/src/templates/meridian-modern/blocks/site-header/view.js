document.querySelectorAll('[data-meridian-header]').forEach(header => {
  const toggle = header.querySelector('.meridian-menu-toggle');
  const closeButton = header.querySelector('[data-menu-close]');
  if (!toggle) return;
  header.dataset.enhanced = '';
  const close = (restoreFocus = false) => { delete header.dataset.open; toggle.setAttribute('aria-expanded', 'false'); toggle.setAttribute('aria-label', 'Open navigation'); document.body.classList.remove('meridian-overlay-open'); if (restoreFocus) toggle.focus(); };
  toggle.addEventListener('click', () => {
    if (header.hasAttribute('data-open')) close();
    else { header.dataset.open = ''; toggle.setAttribute('aria-expanded', 'true'); toggle.setAttribute('aria-label', 'Close navigation'); document.body.classList.add('meridian-overlay-open'); closeButton?.focus(); }
  });
  // A keyboard-activated close should return focus to the menu button. A pointer or
  // touch activation should not: moving focus back after a tap leaves a conspicuous
  // focus rectangle around the hamburger on some mobile browsers.
  closeButton?.addEventListener('click', event => close(event.detail === 0));
  header.addEventListener('keydown', event => { if (event.key === 'Escape' && header.hasAttribute('data-open')) close(true); });
  header.querySelectorAll('nav a').forEach(link => link.addEventListener('click', close));
  document.addEventListener('click', event => { if (!header.contains(event.target)) close(); });
  matchMedia('(min-width: 821px)').addEventListener('change', close);
});

if (!document.documentElement.hasAttribute('data-meridian-smooth-scroll')) {
  document.documentElement.setAttribute('data-meridian-smooth-scroll', '');
  const duration = 550;
  const ease = progress => progress < .5 ? 4 * progress ** 3 : 1 - ((-2 * progress + 2) ** 3) / 2;

  document.addEventListener('click', event => {
    const link = event.target.closest('a[href*="#"]');
    if (!link) return;
    const url = new URL(link.href, window.location.href);
    if (!url.hash || url.origin !== window.location.origin || url.pathname !== window.location.pathname) return;
    const target = document.getElementById(decodeURIComponent(url.hash.slice(1)));
    if (!target) return;

    event.preventDefault();
    const start = window.scrollY;
    const headerOffset = 96;
    const end = Math.max(0, target.getBoundingClientRect().top + start - headerOffset);
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      window.scrollTo(0, end);
      history.replaceState(null, '', url.hash);
      return;
    }

    const started = performance.now();
    const step = now => {
      const progress = Math.min(1, (now - started) / duration);
      window.scrollTo(0, start + (end - start) * ease(progress));
      if (progress < 1) requestAnimationFrame(step);
      else history.replaceState(null, '', url.hash);
    };
    requestAnimationFrame(step);
  });
}
