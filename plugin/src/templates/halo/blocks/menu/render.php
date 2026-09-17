<?php
/**
 * Halo — the treatment menu.
 *
 * A list, not a table. The rows share a shape but each one is a self-contained offer
 * rather than a cell in a grid of values, and a table would promise a column header
 * relationship that is not there.
 *
 * The row number is decorative — it is the design's counting, not information — so it is
 * drawn by CSS from the list itself and never read aloud. Duration and price ARE
 * information, so both are real text inside the row.
 *
 * CONTRAST: this band is ink with on-inverse text, and the muted body uses
 * --fm-inverse-muted rather than --fm-muted. Muted ink on a dark band fails AA; the
 * inverse token is what the palette provides for exactly this (how-to-work.md §8).
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
$note    = (string) ($attributes['note'] ?? '');

$items = array_values(array_filter(
    (array) ($attributes['items'] ?? []),
    static fn ($row): bool => is_array($row) && (string) ($row['name'] ?? '') !== ''
));

if ($items === []) {
    return;
}
?>
<section <?php echo fm_wrapper(['fm-halo-menu', 'fm-halo-band']); ?>>
  <div class="fm-halo-menu__head">
    <div>
      <?php if ($eyebrow !== '') : ?>
        <span class="fm-halo-eyebrow fm-halo-menu__eyebrow"><?php echo esc_html($eyebrow); ?></span>
      <?php endif; ?>

      <?php if ($heading !== '') : ?>
        <h2 class="fm-halo-display fm-halo-menu__heading"><?php echo esc_html($heading); ?></h2>
      <?php endif; ?>
    </div>

    <?php if ($note !== '') : ?>
      <p class="fm-halo-menu__note"><?php echo esc_html($note); ?></p>
    <?php endif; ?>
  </div>

  <ol class="fm-halo-menu__list">
    <?php foreach ($items as $item) : ?>
      <li class="fm-halo-menu__row">
        <span class="fm-halo-menu__name"><?php echo esc_html((string) ($item['name'] ?? '')); ?></span>

        <?php if ((string) ($item['body'] ?? '') !== '') : ?>
          <span class="fm-halo-menu__body"><?php echo esc_html((string) $item['body']); ?></span>
        <?php endif; ?>

        <?php if ((string) ($item['duration'] ?? '') !== '') : ?>
          <span class="fm-halo-menu__duration"><?php echo esc_html((string) $item['duration']); ?></span>
        <?php endif; ?>

        <?php if ((string) ($item['price'] ?? '') !== '') : ?>
          <span class="fm-halo-menu__price"><?php echo esc_html((string) $item['price']); ?></span>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ol>
</section>
