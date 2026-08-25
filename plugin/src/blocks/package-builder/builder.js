/**
 * Checkout behaviour: move between steps, toggle extras, keep the totals honest.
 *
 * Everything this touches is already in the DOM — server-rendered by render.php. This
 * file shows, hides and adds up; it never builds the page. With scripting off all three
 * steps stay visible and the form still submits, which is why the panels are hidden here
 * at runtime rather than in the markup.
 *
 * The numbers shown are a courtesy. The order is priced server-side from the block's own
 * attributes (plugin/inc/checkout.php) — nothing here is trusted with money.
 */

const NEXT_LABELS = ['Add extras', 'Review order', 'Pay and start'];

export default function initPackageBuilder() {
  const root = document.querySelector('[data-fm-builder]');

  if (!root) {
    return;
  }

  const form = root.querySelector('.fm-builder__form');
  const panels = [...root.querySelectorAll('[data-fm-panel]')];
  // Only the bar's own buttons. The "Change" link in the order summary also
  // carries data-fm-step, and it must not pick up aria-current as though it
  // were one of the three step controls.
  const stepBtns = [...root.querySelectorAll('.fm-builder__step-btn[data-fm-step]')];
  const nextBtn = root.querySelector('[data-fm-next]');
  const totals = [...root.querySelectorAll('[data-fm-total]')];
  const linesEl = root.querySelector('[data-fm-lines]');
  const frame = root.querySelector('[data-fm-frame]');

  const basePrice = Number(root.dataset.basePrice || 0);
  const currency = root.dataset.currency || '£';

  // Only now do the later panels disappear: before this runs they are all readable.
  panels.forEach((panel) => {
    panel.hidden = panel.dataset.fmPanel !== '1';
  });

  let step = 1;

  const money = (n) => currency + n.toLocaleString('en-GB');

  const chosen = () =>
    [...root.querySelectorAll('[data-fm-addon][aria-pressed="true"], [data-fm-page][aria-pressed="true"]')];

  const total = () =>
    chosen().reduce((sum, el) => sum + Number(el.dataset.price || 0), basePrice);

  const paint = () => {
    const value = total();
    totals.forEach((el) => {
      el.textContent = money(value);
    });

    if (!linesEl) {
      return;
    }

    // The base line and the free lines are server-rendered and stay put; only the
    // chosen extras are inserted between them, so nothing the server said is lost.
    [...linesEl.querySelectorAll('[data-fm-line-extra]')].forEach((el) => el.remove());

    const anchor = linesEl.querySelector('.fm-builder__line--free');

    chosen().forEach((el) => {
      const li = document.createElement('li');
      li.className = 'fm-builder__line';
      li.setAttribute('data-fm-line-extra', '');

      const label = document.createElement('span');
      label.className = 'fm-builder__line-label';
      label.textContent = el.dataset.name || '';

      const amount = document.createElement('span');
      amount.className = 'fm-builder__line-amount';
      amount.textContent = money(Number(el.dataset.price || 0));

      li.append(label, amount);
      linesEl.insertBefore(li, anchor);
    });
  };

  const pane = root.querySelector('.fm-builder__pane');
  const still = window.matchMedia('(prefers-reduced-motion: reduce)');

  const show = (n, { scroll = false } = {}) => {
    step = Math.min(3, Math.max(1, n));

    panels.forEach((panel) => {
      panel.hidden = Number(panel.dataset.fmPanel) !== step;
    });

    stepBtns.forEach((btn) => {
      const isCurrent = Number(btn.dataset.fmStep) === step;
      btn.toggleAttribute('aria-current', isCurrent);
      if (isCurrent) {
        btn.setAttribute('aria-current', 'step');
      }
      btn.closest('.fm-builder__step')?.classList.toggle('is-done', Number(btn.dataset.fmStep) < step);
    });

    if (nextBtn) {
      nextBtn.textContent = `${NEXT_LABELS[step - 1]} →`;
      // On the last step the sticky button would duplicate the real submit sitting a
      // few centimetres below it, so it stands down rather than competing with it.
      nextBtn.hidden = step === 3;
    }

    paint();

    // Changing step swaps the whole right column, so without this the reader is
    // left looking at wherever the last step happened to be scrolled to — often
    // the middle of a list, or the new heading tucked under the sticky bar.
    // Never on first paint: nobody asked to be moved on arrival.
    if (scroll && pane) {
      pane.scrollIntoView({ behavior: still.matches ? 'auto' : 'smooth', block: 'start' });
    }
  };

  stepBtns.forEach((btn) => {
    btn.addEventListener('click', () => show(Number(btn.dataset.fmStep), { scroll: true }));
  });

  nextBtn?.addEventListener('click', () => show(step + 1, { scroll: true }));

  // "Change" on the base line: back to the preview, same as tapping step 1.
  root.querySelectorAll('.fm-builder__change[data-fm-step]').forEach((btn) => {
    btn.addEventListener('click', () => show(Number(btn.dataset.fmStep), { scroll: true }));
  });

  // Extras. aria-pressed is the state — the styling and the sums both read from it,
  // so there is only ever one source of truth and it is the accessible one.
  root.querySelectorAll('[data-fm-addon], [data-fm-page]').forEach((btn) => {
    btn.addEventListener('click', () => {
      btn.setAttribute('aria-pressed', btn.getAttribute('aria-pressed') === 'true' ? 'false' : 'true');
      paint();
    });
  });

  // Preview size.
  root.querySelectorAll('[data-fm-device]').forEach((btn) => {
    btn.addEventListener('click', () => {
      root.querySelectorAll('[data-fm-device]').forEach((other) => {
        other.setAttribute('aria-pressed', String(other === btn));
      });
      if (frame) {
        frame.dataset.device = btn.dataset.fmDevice || 'desktop';
      }
    });
  });

  // What actually gets posted. Hidden inputs are written at submit time rather than
  // kept in sync on every click — one place to be wrong instead of many.
  form?.addEventListener('submit', () => {
    [...form.querySelectorAll('[data-fm-posted]')].forEach((el) => el.remove());

    chosen().forEach((el) => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.setAttribute('data-fm-posted', '');
      input.name = el.dataset.fmAddon ? 'fm_addons[]' : 'fm_pages[]';
      input.value = el.dataset.fmAddon || el.dataset.fmPage || '';
      form.append(input);
    });
  });

  show(1);
}
