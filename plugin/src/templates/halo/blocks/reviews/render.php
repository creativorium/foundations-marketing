<?php
/**
 * Halo — client reviews.
 *
 * <blockquote> with a <figcaption> naming the speaker, not a styled paragraph: these are
 * quotations from other people, and the markup should say whose words they are.
 *
 * No star ratings and no review schema. Review markup on a page the business controls is
 * self-serving structured data, which search engines discount and sometimes penalise, and
 * a template cannot verify that any of these were ever said. The quotes are presented as
 * what they are — the customer's own selection.
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

$quotes = array_values(array_filter(
    (array) ($attributes['quotes'] ?? []),
    static fn ($row): bool => is_array($row) && (string) ($row['quote'] ?? '') !== ''
));

if ($quotes === []) {
    return;
}
?>
<section <?php echo fm_wrapper(['fm-halo-reviews', 'fm-halo-band']); ?>>
  <div class="fm-halo-reviews__head">
    <?php if ($eyebrow !== '') : ?>
      <span class="fm-halo-eyebrow"><?php echo esc_html($eyebrow); ?></span>
    <?php endif; ?>

    <?php if ($heading !== '') : ?>
      <h2 class="fm-halo-display fm-halo-reviews__heading"><?php echo esc_html($heading); ?></h2>
    <?php endif; ?>
  </div>

  <div class="fm-halo-reviews__grid">
    <?php foreach ($quotes as $quote) : ?>
      <figure class="fm-halo-reviews__item">
        <blockquote class="fm-halo-reviews__quote">
          <p><?php echo esc_html((string) $quote['quote']); ?></p>
        </blockquote>

        <?php if ((string) ($quote['by'] ?? '') !== '') : ?>
          <figcaption class="fm-halo-reviews__by"><?php echo esc_html((string) $quote['by']); ?></figcaption>
        <?php endif; ?>
      </figure>
    <?php endforeach; ?>
  </div>
</section>
