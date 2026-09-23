// Quartz review slider: arrows, dots and a slow autoplay on top of a scroll-snap row.
// Autoplay never runs under reduced motion and pauses while the slider is hovered or focused.
document.querySelectorAll('[data-quartz-slider]').forEach(slider => {
  const track = slider.querySelector('.quartz-slider__track');
  const controls = slider.querySelector('.quartz-slider__controls');
  const dots = [...slider.querySelectorAll('[data-quartz-dot]')];
  const total = track ? track.children.length : 0;
  if (!track || !controls || total < 2) return;

  const reduced = matchMedia('(prefers-reduced-motion: reduce)');
  let current = 0;
  let timer = 0;
  let paused = false;

  const mark = index => dots.forEach((dot, i) => dot.setAttribute('aria-current', i === index ? 'true' : 'false'));
  const goTo = index => {
    current = (index + total) % total;
    track.scrollTo({ left: current * track.clientWidth, behavior: reduced.matches ? 'auto' : 'smooth' });
    mark(current);
  };
  const stop = () => { clearInterval(timer); timer = 0; };
  const start = () => {
    stop();
    if (!reduced.matches && !paused && !document.hidden) timer = setInterval(() => goTo(current + 1), 4500);
  };

  controls.hidden = false;
  slider.querySelector('[data-quartz-prev]').addEventListener('click', () => { goTo(current - 1); start(); });
  slider.querySelector('[data-quartz-next]').addEventListener('click', () => { goTo(current + 1); start(); });
  dots.forEach(dot => dot.addEventListener('click', () => { goTo(Number(dot.dataset.quartzDot)); start(); }));

  // Keep the dots honest when the row is swiped or scrolled by hand.
  let settle = 0;
  track.addEventListener('scroll', () => {
    clearTimeout(settle);
    settle = setTimeout(() => { current = Math.round(track.scrollLeft / track.clientWidth); mark(current); }, 80);
  }, { passive: true });

  const pause = () => { paused = true; stop(); };
  const resume = () => { paused = false; start(); };
  slider.addEventListener('mouseenter', pause);
  slider.addEventListener('mouseleave', () => { if (!slider.contains(document.activeElement)) resume(); });
  slider.addEventListener('focusin', pause);
  slider.addEventListener('focusout', event => { if (!slider.contains(event.relatedTarget)) resume(); });
  document.addEventListener('visibilitychange', start);
  reduced.addEventListener('change', start);
  start();
});
