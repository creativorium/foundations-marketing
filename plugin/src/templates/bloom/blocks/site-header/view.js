document.querySelectorAll('[data-bloom-header]').forEach(shell => {
  const button = shell.querySelector('.bloom-menu-toggle');
  const nav = shell.querySelector('.bloom-sidenav');
  if (!button || !nav) return;
  const close = () => { shell.removeAttribute('data-open'); button.setAttribute('aria-expanded', 'false'); document.body.classList.remove('bloom-menu-open'); };
  button.addEventListener('click', () => { const open = shell.hasAttribute('data-open'); if (open) close(); else { shell.setAttribute('data-open', ''); button.setAttribute('aria-expanded', 'true'); document.body.classList.add('bloom-menu-open'); } });
  nav.querySelectorAll('a').forEach(link => link.addEventListener('click', close));
  document.addEventListener('keydown', event => { if (event.key === 'Escape') { close(); button.focus(); } });
  matchMedia('(min-width: 901px)').addEventListener('change', close);
});
