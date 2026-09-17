/** The website's small interaction layer, independently implemented without its artifact runtime. */
export default function initPulseAtelier() {
  document.querySelectorAll('[data-pa-root]').forEach(root => {
    if (root.dataset.paReady) return;
    root.dataset.paReady = 'true';
    let currentView = 'home';
    root.addEventListener('click', event => {
      const navigation = event.target.closest('[data-pa-nav]');
      if (navigation && root.contains(navigation)) {
        const target = navigation.dataset.paNav;
        // Edited external URLs retain normal browser navigation.
        if (!navigation.getAttribute('href')?.startsWith('#')) return;
        if (target === 'home' && currentView === 'home') return;
        event.preventDefault();
        root.querySelectorAll('[data-pa-view]').forEach(view => {
          view.hidden = view.dataset.paView !== target;
          if (view.hidden) view.querySelectorAll('input, textarea').forEach(input => { input.value = ''; });
        });
        currentView = target;
        window.scrollTo(0, 0);
        return;
      }
      const dot = event.target.closest('[data-pa-quote]');
      if (dot) {
        const section = dot.closest('[data-pa-part]');
        const data = section.nextElementSibling;
        if (!data?.matches('template[data-pa-quotes]')) return;
        const quotes = JSON.parse(data.content.textContent);
        const quote = quotes[Number(dot.dataset.paQuote)];
        if (!quote) return;
        section.querySelector('[data-pa-quote-text] span').textContent = quote.text;
        section.querySelector('[data-pa-quote-author] span').textContent = quote.author;
        section.querySelectorAll('[data-pa-quote]').forEach(button => button.setAttribute('aria-pressed', String(button === dot)));
        return;
      }
      const question = event.target.closest('[data-pa-faq]');
      if (question) {
        const open = question.getAttribute('aria-expanded') !== 'true';
        question.closest('[data-pa-part]').querySelectorAll('[data-pa-faq]').forEach(button => {
          const expanded = open && button === question;
          button.setAttribute('aria-expanded', String(expanded));
          button.querySelector('[data-pa-faq-sign]').textContent = expanded ? '−' : '+';
          document.getElementById(button.getAttribute('aria-controls')).hidden = !expanded;
        });
      }
      // The reference has no newsletter/contact submit handler, validation or success UI.
    });
  });
}
