document.addEventListener('click', (event) => {
  const dot = event.target.closest('[data-pa-quote]');
  if (dot) {
    const section = dot.closest('[data-pa-part]');
    const data = section?.nextElementSibling;
    if (!data?.matches('template[data-pa-quotes]')) return;
    const quote = JSON.parse(data.content.textContent)[Number(dot.dataset.paQuote)];
    if (!quote) return;
    section.querySelector('[data-pa-quote-text] span').textContent = quote.text;
    section.querySelector('[data-pa-quote-author] span').textContent = quote.author;
    section.querySelectorAll('[data-pa-quote]').forEach((button) => button.setAttribute('aria-pressed', String(button === dot)));
    return;
  }
  const question = event.target.closest('[data-pa-faq]');
  if (!question) return;
  const open = question.getAttribute('aria-expanded') !== 'true';
  question.closest('[data-pa-part]').querySelectorAll('[data-pa-faq]').forEach((button) => {
    const expanded = open && button === question;
    button.setAttribute('aria-expanded', String(expanded));
    button.querySelector('[data-pa-faq-sign]').textContent = expanded ? '−' : '+';
    document.getElementById(button.getAttribute('aria-controls')).hidden = !expanded;
  });
});
