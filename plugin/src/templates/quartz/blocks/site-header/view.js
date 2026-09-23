// Quartz mobile menu. The toggle only exists once this runs, so no-JS visitors keep the links.
document.querySelectorAll('[data-quartz-header]').forEach(header => {
  const button = header.querySelector('.quartz-header__toggle');
  const menu = header.querySelector('.quartz-header__menu');
  if (!button || !menu) return;

  const close = () => { header.removeAttribute('data-open'); button.setAttribute('aria-expanded', 'false'); };
  header.setAttribute('data-js', '');
  button.hidden = false;
  button.addEventListener('click', () => {
    if (header.hasAttribute('data-open')) { close(); return; }
    header.setAttribute('data-open', '');
    button.setAttribute('aria-expanded', 'true');
  });
  menu.querySelectorAll('a').forEach(link => link.addEventListener('click', close));
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && header.hasAttribute('data-open')) { close(); button.focus(); }
  });
  matchMedia('(min-width: 769px)').addEventListener('change', close);
});
