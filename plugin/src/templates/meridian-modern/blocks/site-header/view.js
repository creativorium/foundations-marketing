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
  closeButton?.addEventListener('click', () => close(true));
  header.addEventListener('keydown', event => { if (event.key === 'Escape' && header.hasAttribute('data-open')) close(true); });
  header.querySelectorAll('nav a').forEach(link => link.addEventListener('click', close));
  document.addEventListener('click', event => { if (!header.contains(event.target)) close(); });
  matchMedia('(min-width: 821px)').addEventListener('change', close);
});
