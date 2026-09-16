<?php
/**
 * Onyx — the closing call to action.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$heading   = (string) ($attributes['heading'] ?? '');
$lead      = (string) ($attributes['lead'] ?? '');
$cta_label = (string) ($attributes['ctaLabel'] ?? '');
$cta_url   = (string) ($attributes['ctaUrl'] ?? '');

if ($heading === '') {
    return;
}
?>
<section <?php echo fm_wrapper(['fm-onyx-cta']); ?>>
  <div class="fm-onyx-cta__panel">
    <div class="fm-onyx-cta__copy">
      <h2 class="fm-onyx-cta__heading fm-onyx-display"><?php echo nl2br(esc_html($heading), false); ?></h2>

      <?php if ($lead !== '') : ?>
        <p class="fm-onyx-cta__lead"><?php echo esc_html($lead); ?></p>
      <?php endif; ?>
    </div>

    <?php if ($cta_label !== '' && $cta_url !== '') : ?>
      <a class="fm-onyx-button fm-onyx-button--light" href="<?php echo fm_url($cta_url); ?>">
        <?php echo esc_html($cta_label); ?>
      </a>
    <?php endif; ?>
  </div>
</section>
