<?php
/**
 * Onyx — process steps.
 *
 * An <ol>, because the order is the content: "four appointments, one plan" only means
 * anything in sequence. The visible number is aria-hidden — the list already conveys
 * position, and announcing "01" before "one, Consultation" is the same fact twice.
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
    static fn ($row): bool => is_array($row) && (string) ($row['name'] ?? '') !== ''
));

if ($items === []) {
    return;
}
?>
<section <?php echo fm_wrapper(['fm-onyx-steps']); ?>>
  <?php if ($eyebrow !== '' || $heading !== '') : ?>
    <div class="fm-onyx-steps__intro">
      <?php if ($eyebrow !== '') : ?>
        <span class="fm-onyx-eyebrow"><?php echo esc_html($eyebrow); ?></span>
      <?php endif; ?>

      <?php if ($heading !== '') : ?>
        <h2 class="fm-onyx-steps__heading fm-onyx-display"><?php echo esc_html($heading); ?></h2>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <ol class="fm-onyx-steps__list">
    <?php foreach ($items as $item) : ?>
      <?php
      $num  = (string) ($item['num'] ?? '');
      $name = (string) ($item['name'] ?? '');
      $desc = (string) ($item['desc'] ?? '');
      ?>
      <li class="fm-onyx-steps__item">
        <?php if ($num !== '') : ?>
          <span class="fm-onyx-steps__num" aria-hidden="true"><?php echo esc_html($num); ?></span>
        <?php endif; ?>

        <h3 class="fm-onyx-steps__name"><?php echo esc_html($name); ?></h3>

        <?php if ($desc !== '') : ?>
          <p class="fm-onyx-steps__desc"><?php echo esc_html($desc); ?></p>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ol>
</section>
