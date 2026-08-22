/**
 * Template library filtering.
 *
 * Progressive enhancement only: every card is already in the page and this simply
 * hides the ones that do not match. If this never runs, the whole library is still
 * there — which is the behaviour we want for search engines too.
 *
 * Hiding uses the `hidden` attribute rather than a class, so filtered-out cards leave
 * the accessibility tree as well as the layout.
 */
export default function initTemplateLibrary() {
  const libraries = document.querySelectorAll('[data-fm-library]');

  libraries.forEach((library) => {
    const buttons = library.querySelectorAll('[data-fm-filter]');
    const items = library.querySelectorAll('[data-fm-category]');
    const status = library.querySelector('[data-fm-library-status]');

    if (!buttons.length || !items.length) {
      return;
    }

    const apply = (active) => {
      let shown = 0;

      items.forEach((item) => {
        const match = active === '' || item.dataset.fmCategory === active;
        item.hidden = !match;

        if (match) {
          shown += 1;
        }
      });

      buttons.forEach((button) => {
        button.setAttribute('aria-pressed', String(button.dataset.fmFilter === active));
      });

      if (status) {
        // Screen readers get told the result; sighted users can see it.
        status.textContent = `${shown} of ${items.length} templates shown`;
      }
    };

    buttons.forEach((button) => {
      button.addEventListener('click', () => apply(button.dataset.fmFilter));
    });
  });
}
