<?php
/**
 * Onyx — patient reviews.
 *
 * Each card is a <blockquote> with its attribution in a <figcaption>, which is the
 * markup a quote actually is — a styled <div> would read as loose prose.
 *
 * The star row is decorative text (★★★★★) and is hidden from assistive technology, with
 * the real rating carried by the `rating` field as words. Five literal stars announce as
 * "black star black star…", which tells a screen-reader user nothing.
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
$meta    = (string) ($attributes['meta'] ?? '');

$items = array_values(array_filter(
    (array) ($attributes['items'] ?? []),
    static fn ($row): bool => is_array($row) && (string) ($row['quote'] ?? '') !== ''
));

if ($items === []) {
    return;
}
?>
<section <?php echo fm_wrapper(['fm-onyx-reviews']); ?>>
  <div class="fm-onyx-reviews__head">
    <div class="fm-onyx-reviews__title">
      <?php if ($eyebrow !== '') : ?>
        <span class="fm-onyx-eyebrow"><?php echo esc_html($eyebrow); ?></span>
      <?php endif; ?>

      <?php if ($heading !== '') : ?>
        <h2 class="fm-onyx-reviews__heading fm-onyx-display"><?php echo esc_html($heading); ?></h2>
      <?php endif; ?>
    </div>

    <?php if ($meta !== '') : ?>
      <p class="fm-onyx-reviews__meta"><?php echo esc_html($meta); ?></p>
    <?php endif; ?>
  </div>

  <ul class="fm-onyx-reviews__grid">
    <?php foreach ($items as $item) : ?>
      <?php
      $quote  = (string) ($item['quote'] ?? '');
      $who    = (string) ($item['who'] ?? '');
      $rating = (string) ($item['rating'] ?? '');
      ?>
      <li class="fm-onyx-reviews__item">
        <figure class="fm-onyx-reviews__card">
          <?php if ($rating !== '') : ?>
            <span class="fm-onyx-reviews__stars" aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
            <span class="screen-reader-text"><?php echo esc_html($rating); ?></span>
          <?php endif; ?>

          <blockquote class="fm-onyx-reviews__quote">
            <p><?php echo esc_html($quote); ?></p>
          </blockquote>

          <?php if ($who !== '') : ?>
            <figcaption class="fm-onyx-reviews__who"><?php echo esc_html($who); ?></figcaption>
          <?php endif; ?>
        </figure>
      </li>
    <?php endforeach; ?>
  </ul>
</section>
