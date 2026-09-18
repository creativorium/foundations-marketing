document.addEventListener('click', (event) => {
  const question = event.target.closest('[data-pk-faq]');
  if (question) {
    const open = question.getAttribute('aria-expanded') !== 'true';
    question.closest('[data-pk-part]').querySelectorAll('[data-pk-faq]').forEach((button) => {
      const expanded = open && button === question;
      button.setAttribute('aria-expanded', String(expanded));
      button.querySelector('[data-pk-faq-sign]').textContent = expanded ? '−' : '+';
      document.getElementById(button.getAttribute('aria-controls')).hidden = !expanded;
    });
    return;
  }

  const form = event.target.closest('[data-pk-inert-form]');
  if (form && event.target.matches('button[type="submit"]')) event.preventDefault();
});

document.addEventListener('submit', (event) => {
  if (event.target.matches('[data-pk-inert-form]')) event.preventDefault();
});
