<?php
/**
 * Onyx — the opening of an interior page.
 *
 * SEO: this block owns the H1 on every page that is not the homepage (how-to-work.md
 * §10). `multiple: false` in block.json is the mechanical half of "exactly one H1 per
 * page" — the editor will not let a second one onto the same page.
 *
 * `note` is the boxed aside the privacy page needs ("replace the bracketed details…").
 * It is part of this block rather than a block of its own because it is one paragraph
 * that only ever appears directly under a page opening.
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
$layout  = (string) ($attributes['layout'] ?? 'stacked');
$layout  = in_array($layout, ['stacked', 'split'], true) ? $layout : 'stacked';

if ($heading === '') {
    return;
}
?>
<section <?php echo fm_wrapper(['fm-onyx-page-hero', 'fm-onyx-page-hero--' . sanitize_html_class($layout)]); ?>>
  <div class="fm-onyx-page-hero__title">
    <?php if ($eyebrow !== '') : ?>
      <span class="fm-onyx-eyebrow"><?php echo esc_html($eyebrow); ?></span>
    <?php endif; ?>

    <h1 class="fm-onyx-page-hero__heading fm-onyx-display"><?php echo esc_html($heading); ?></h1>
  </div>

  <?php if ($lead !== '' || $note !== '') : ?>
    <div class="fm-onyx-page-hero__aside">
      <?php if ($lead !== '') : ?>
        <p class="fm-onyx-page-hero__lead"><?php echo esc_html($lead); ?></p>
      <?php endif; ?>

      <?php if ($note !== '') : ?>
        <p class="fm-onyx-page-hero__note"><?php echo esc_html($note); ?></p>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</section>
