<?php
/**
 * Halo — packages and memberships.
 *
 * The featured card is darker and lifted. That is colour and position doing the work, so
 * the tier label on it ("Most chosen") carries the same meaning in words — the design's
 * emphasis must not be the only way to know which plan is which (how-to-work.md §8).
 *
 * Price and unit are separate attributes so "£95" can be set large and "/ month" small
 * without splitting the number in the markup, and both are read as one price.
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
$lead    = (string) ($attributes['lead'] ?? '');

$plans = array_values(array_filter(
    (array) ($attributes['plans'] ?? []),
    static fn ($row): bool => is_array($row) && (string) ($row['name'] ?? '') !== ''
));

if ($plans === []) {
    return;
}
?>
<section <?php echo fm_wrapper(['fm-halo-packages', 'fm-halo-band']); ?>>
  <div class="fm-halo-packages__head">
    <?php if ($eyebrow !== '') : ?>
      <span class="fm-halo-eyebrow"><?php echo esc_html($eyebrow); ?></span>
    <?php endif; ?>

    <?php if ($heading !== '') : ?>
      <h2 class="fm-halo-display fm-halo-packages__heading"><?php echo esc_html($heading); ?></h2>
    <?php endif; ?>

    <?php if ($lead !== '') : ?>
      <p class="fm-halo-lead fm-halo-packages__lead"><?php echo esc_html($lead); ?></p>
    <?php endif; ?>
  </div>

  <ul class="fm-halo-packages__grid">
    <?php foreach ($plans as $plan) : ?>
      <?php
      $featured = !empty($plan['featured']);
      $features = array_values(array_filter(
          array_map('strval', (array) ($plan['features'] ?? [])),
          static fn (string $f): bool => trim($f) !== ''
      ));
      $cta_label = (string) ($plan['ctaLabel'] ?? '');
      $cta_url   = (string) ($plan['ctaUrl'] ?? '');
      ?>
      <li class="fm-halo-packages__card <?php echo $featured ? 'is-featured' : ''; ?>">
        <?php if ((string) ($plan['tier'] ?? '') !== '') : ?>
          <span class="fm-halo-packages__tier"><?php echo esc_html((string) $plan['tier']); ?></span>
        <?php endif; ?>

        <h3 class="fm-halo-packages__name"><?php echo esc_html((string) $plan['name']); ?></h3>

        <?php if ((string) ($plan['price'] ?? '') !== '') : ?>
          <p class="fm-halo-packages__price">
            <span class="fm-halo-packages__amount"><?php echo esc_html((string) $plan['price']); ?></span>
            <?php if ((string) ($plan['unit'] ?? '') !== '') : ?>
              <span class="fm-halo-packages__unit"><?php echo esc_html((string) $plan['unit']); ?></span>
            <?php endif; ?>
          </p>
        <?php endif; ?>

        <?php if ($features !== []) : ?>
          <ul class="fm-halo-packages__features">
            <?php foreach ($features as $feature) : ?>
              <li><?php echo esc_html($feature); ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

        <?php
        // Both halves or neither. An unlinked "Choose this plan" on a priced card is the
        // most frustrating dead control on the page.
        if ($cta_label !== '' && $cta_url !== '') :
            ?>
          <a class="fm-halo-packages__cta" href="<?php echo fm_url($cta_url); ?>">
            <?php echo esc_html($cta_label); ?>
            <span aria-hidden="true">&rarr;</span>
          </a>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>
</section>
