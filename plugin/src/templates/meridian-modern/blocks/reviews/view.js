document.querySelectorAll('[data-meridian-reviews]').forEach(section => {
  const reviews = [...section.querySelectorAll('[data-review]')];
  const buttons = [...section.querySelectorAll('[data-review-select]')];
  buttons.forEach((button, index) => button.addEventListener('click', () => {
    reviews.forEach((review, i) => { review.hidden = i !== index; });
    buttons.forEach((item, i) => item.setAttribute('aria-pressed', String(i === index)));
  }));
});
