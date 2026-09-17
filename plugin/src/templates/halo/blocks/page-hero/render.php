<?php
/**
 * Halo — the interior page hero.
 *
 * SEO: owns this page's ONE H1 (how-to-work.md §10). `multiple: false` is what stops a
 * second one being dropped on the same page by accident.
 *
 * `note` is addressed to whoever is editing the page, not to its readers — on the privacy
 * policy it says to replace the bracketed details and have the result reviewed. It is
 * styled as an aside rather than as body copy so it never reads as part of the policy,
 * and it is deleted by clearing the field once the page is finished.
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
$note    = (string) ($attributes['note'] ?? '');

if ($heading === '') {
    return;
}
?>
<section <?php echo fm_wrapper(['fm-halo-page-hero']); ?>>
  <?php if ($eyebrow !== '') : ?>
    <span class="fm-halo-eyebrow"><?php echo esc_html($eyebrow); ?></span>
  <?php endif; ?>

  <h1 class="fm-halo-display fm-halo-page-hero__heading"><?php echo esc_html($heading); ?></h1>

  <?php if ($lead !== '') : ?>
    <p class="fm-halo-lead fm-halo-page-hero__lead"><?php echo esc_html($lead); ?></p>
  <?php endif; ?>

  <?php if ($note !== '') : ?>
    <aside class="fm-halo-page-hero__note"><?php echo esc_html($note); ?></aside>
  <?php endif; ?>
</section>
