document.querySelectorAll('[data-meridian-reviews]').forEach(section => {
  const reviews = [...section.querySelectorAll('[data-review]')];
  const counter = section.querySelector('[data-review-counter]');
  let active = 0;

  const show = index => {
    active = (index + reviews.length) % reviews.length;
    reviews.forEach((review, i) => { review.hidden = i !== active; });
    if (counter) counter.textContent = `${active + 1} / ${reviews.length}`;
  };

  section.querySelector('[data-review-prev]')?.addEventListener('click', () => show(active - 1));
  section.querySelector('[data-review-next]')?.addEventListener('click', () => show(active + 1));
});
