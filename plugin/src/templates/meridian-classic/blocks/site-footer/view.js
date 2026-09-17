document.querySelectorAll('[data-meridian-footer]').forEach(footer => {
  let opener = null;
  const overlays = [...footer.querySelectorAll('[data-overlay]')];
  const closeAll = () => {
    overlays.forEach(overlay => { overlay.hidden = true; overlay.setAttribute('aria-hidden', 'true'); });
    document.body.classList.remove('meridian-overlay-open');
  };
  const open = name => {
    const overlay = footer.querySelector(`[data-overlay="${name}"]`);
    if (!overlay) return;
    closeAll(); overlay.hidden = false; overlay.setAttribute('aria-hidden', 'false'); document.body.classList.add('meridian-overlay-open');
    overlay.querySelector('[data-overlay-close]')?.focus();
  };
  footer.querySelectorAll('[data-overlay-open]').forEach(button => button.addEventListener('click', () => { opener = button; open(button.dataset.overlayOpen); }));
  footer.querySelectorAll('[data-overlay-close]').forEach(button => button.addEventListener('click', () => { closeAll(); opener?.focus(); }));
  overlays.forEach(overlay => overlay.addEventListener('click', event => { if (event.target === overlay) { closeAll(); opener?.focus(); } }));
  document.addEventListener('keydown', event => { if (event.key === 'Escape' && overlays.some(overlay => !overlay.hidden)) { closeAll(); opener?.focus(); } });
});
