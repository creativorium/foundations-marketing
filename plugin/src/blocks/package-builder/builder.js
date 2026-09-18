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

const NEXT_LABELS = ['Add extras', 'Review order', 'Continue to checkout'];

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
  const quickPayBtn = root.querySelector('[data-fm-quick-pay]');
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

    if (quickPayBtn) {
      quickPayBtn.hidden = step !== 3;
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

  // Preview size. The bezel changes shape in CSS; what changes here is the width the
  // demo is actually rendered at, so the template's own breakpoints do the work.
  const preview = root.querySelector('[data-fm-preview]');
  const screenEl = root.querySelector('[data-fm-screen]');

  let widths = { desktop: 1280, tablet: 834, mobile: 390 };

  try {
    widths = { ...widths, ...JSON.parse(frame?.dataset.fmWidths || '{}') };
  } catch {
    // A malformed attribute is not worth a broken preview; the defaults above match
    // the ones render.php ships.
  }

  const fitPreview = () => {
    if (!preview || !screenEl) {
      return;
    }

    const box = screenEl.getBoundingClientRect();

    // Zero while the aside is still laying out, or while the panel is hidden — a
    // scale of 0 would blank the preview, so leave the last good one in place.
    if (!box.width || !box.height) {
      return;
    }

    const width = widths[frame.dataset.device] || widths.desktop;
    const scale = box.width / width;

    preview.style.setProperty('--fm-preview-w', String(width));
    preview.style.setProperty('--fm-preview-h', String(Math.round(box.height / scale)));
    preview.style.setProperty('--fm-preview-scale', String(scale));
  };

  root.querySelectorAll('[data-fm-device]').forEach((btn) => {
    btn.addEventListener('click', () => {
      root.querySelectorAll('[data-fm-device]').forEach((other) => {
        other.setAttribute('aria-pressed', String(other === btn));
      });
      if (frame) {
        frame.dataset.device = btn.dataset.fmDevice || 'desktop';
        fitPreview();
      }
    });
  });

  if (preview && screenEl && 'ResizeObserver' in window) {
    // The bezel is a percentage of the column, so it also changes on rotate, on a
    // window resize, and once the aside's own fonts have loaded.
    new ResizeObserver(fitPreview).observe(screenEl);
  }

  fitPreview();

  // --- the preview's own loading state ---------------------------------------
  // The preview is a second WordPress page, so it arrives when a page arrives. The
  // stage carries the state and CSS does the rest; this only says which state it is
  // in. Server-rendered as "loading", so the bezel is never briefly empty before the
  // script runs.
  const stage = root.querySelector('[data-fm-stage]');

  if (preview && stage) {
    let settled = false;

    const settle = (state) => {
      if (settled) {
        return;
      }
      settled = true;
      stage.dataset.fmStage = state;
      // Re-fit once the document inside is real: its height is what the scale was
      // guessing at while the frame was still empty.
      fitPreview();
    };

    /*
     * Did a real page arrive, or is this the empty document every iframe starts life
     * with? An iframe that has not navigated yet reports readyState "complete" on its
     * blank document and fires `load` for a refused request, so "it loaded" is not the
     * same question as "there is something in it". Both are answered by looking.
     *
     * The preview is same-origin by construction — render.php builds its URL from this
     * site's own home_url() — so the document is always readable when it is ours.
     * Being unable to read it means what is in the frame is NOT ours: the browser's own
     * error page, which is exactly the case this is here to catch.
     */
    const hasContent = () => {
      try {
        const doc = preview.contentDocument;

        return !!doc && doc.location.href !== 'about:blank' && (doc.body?.childElementCount ?? 0) > 0;
      } catch {
        return false;
      }
    };

    preview.addEventListener('load', () => settle(hasContent() ? 'ready' : 'failed'));
    preview.addEventListener('error', () => settle('failed'));

    // A preview that has not arrived in fifteen seconds is not going to. Say so rather
    // than spinning for ever — the buyer can still choose the template, and the
    // "Open full demo" link beside the heading still works.
    window.setTimeout(() => settle('failed'), 15000);

    // A cached preview can beat the bundle to it, in which case there is no `load`
    // event left to hear.
    if (preview.contentDocument?.readyState === 'complete' && hasContent()) {
      settle('ready');
    }
  }

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
