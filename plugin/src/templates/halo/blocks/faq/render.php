<?php
/**
 * Halo — questions before booking.
 *
 * <details>/<summary>, not a JavaScript accordion. The browser gives the open and closed
 * states, the keyboard handling and the announcement for free, it works before any script
 * runs, and it costs nothing against the 85+ mobile target (how-to-work.md §9). A hand-
 * built accordion here would be more code doing the same job worse.
 *
 * The first question opens by default so the section is not a wall of closed bars, and
 * so the reader can see what an opened answer looks like without clicking.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$eyebrow = (string) ($attributes['eyebrow'] ?? '');
$heading = (string) ($attributes['heading'] ?? '');

$items = array_values(array_filter(
    (array) ($attributes['items'] ?? []),
    static fn ($row): bool => is_array($row) && (string) ($row['q'] ?? '') !== ''
));

if ($items === []) {
    return;
}
?>
<section <?php echo fm_wrapper(['fm-halo-faq', 'fm-halo-band']); ?>>
  <div class="fm-halo-faq__grid">
    <div class="fm-halo-faq__head">
      <?php if ($eyebrow !== '') : ?>
        <span class="fm-halo-eyebrow"><?php echo esc_html($eyebrow); ?></span>
      <?php endif; ?>

      <?php if ($heading !== '') : ?>
        <h2 class="fm-halo-display fm-halo-faq__heading"><?php echo esc_html($heading); ?></h2>
      <?php endif; ?>
    </div>

    <div class="fm-halo-faq__list">
      <?php foreach ($items as $index => $item) : ?>
        <details class="fm-halo-faq__item" <?php echo $index === 0 ? 'open' : ''; ?>>
          <summary class="fm-halo-faq__question"><?php echo esc_html((string) $item['q']); ?></summary>
          <?php if ((string) ($item['a'] ?? '') !== '') : ?>
            <p class="fm-halo-faq__answer"><?php echo esc_html((string) $item['a']); ?></p>
          <?php endif; ?>
        </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
