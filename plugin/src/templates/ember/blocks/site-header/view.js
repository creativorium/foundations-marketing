document.querySelectorAll('[data-ember-nav]').forEach((nav) => {
  const update = () => nav.classList.toggle('is-scrolled', window.scrollY > 60);
  update();
  window.addEventListener('scroll', update, { passive: true });
});
