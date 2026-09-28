// Manual navigation: arrows, dots, keyboard scrolling and touch swipe.
document.querySelectorAll('[data-quartz-slider]').forEach(slider => {
  const track = slider.querySelector('.quartz-slider__track');
  const controls = slider.querySelector('.quartz-slider__controls');
  const dots = [...slider.querySelectorAll('[data-quartz-dot]')];
  const total = track ? track.children.length : 0;
  if (!track || !controls || total < 2) return;
  const reduced = matchMedia('(prefers-reduced-motion: reduce)');
  let current = 0;
  const mark = () => dots.forEach((dot, i) => dot.setAttribute('aria-current', i === current ? 'true' : 'false'));
  const goTo = index => {
    current = (index + total) % total;
    track.scrollTo({ left: current * track.clientWidth, behavior: reduced.matches ? 'auto' : 'smooth' });
    mark();
  };
  controls.hidden = false;
  slider.querySelector('[data-quartz-prev]').addEventListener('click', () => goTo(current - 1));
  slider.querySelector('[data-quartz-next]').addEventListener('click', () => goTo(current + 1));
  dots.forEach(dot => dot.addEventListener('click', () => goTo(Number(dot.dataset.quartzDot))));
  let settle = 0;
  track.addEventListener('scroll', () => {
    clearTimeout(settle);
    settle = setTimeout(() => {
      current = Math.max(0, Math.min(total - 1, Math.round(track.scrollLeft / track.clientWidth)));
      mark();
    }, 120);
  }, { passive: true });
});
