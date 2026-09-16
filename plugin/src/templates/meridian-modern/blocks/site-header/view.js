document.querySelectorAll('[data-meridian-header]').forEach(header => {
  const toggle = header.querySelector('.meridian-menu-toggle');
  if (!toggle) return;
  header.dataset.enhanced = '';
  const close = () => { delete header.dataset.open; toggle.setAttribute('aria-expanded', 'false'); toggle.setAttribute('aria-label', 'Open navigation'); };
  toggle.addEventListener('click', () => {
    if (header.hasAttribute('data-open')) close();
    else { header.dataset.open = ''; toggle.setAttribute('aria-expanded', 'true'); toggle.setAttribute('aria-label', 'Close navigation'); }
  });
  header.addEventListener('keydown', event => { if (event.key === 'Escape' && header.hasAttribute('data-open')) { close(); toggle.focus(); } });
  header.querySelectorAll('nav a').forEach(link => link.addEventListener('click', close));
  document.addEventListener('click', event => { if (!header.contains(event.target)) close(); });
  matchMedia('(min-width: 821px)').addEventListener('change', close);
});
