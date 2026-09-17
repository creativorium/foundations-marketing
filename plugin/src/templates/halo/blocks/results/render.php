<?php
/**
 * Halo — before and after.
 *
 * Each pair is a <figure> with a <figcaption>, because the caption describes the images
 * rather than merely sitting near them.
 *
 * CONSENT: client photographs are personal data, and in this niche often special
 * category data — the privacy policy this template ships says so. `consentNote` is
 * printed under the row as a standing reminder that every photograph here needs written
 * consent before it goes live. It is copy, not a control: nothing technical can enforce
 * consent, so the honest thing is to put the obligation where the customer sees it while
 * they are choosing the pictures.
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
$consent = (string) ($attributes['consentNote'] ?? '');

$items = array_values(array_filter(
    (array) ($attributes['items'] ?? []),
    static fn ($row): bool => is_array($row)
        && ((string) ($row['label'] ?? '') !== '' || (string) ($row['caption'] ?? '') !== '')
));

if ($items === []) {
    return;
}

/**
 * One half of a pair: the photograph if there is one, the briefing note if there is not.
 *
 * @param array<string, mixed> $item
 */
$shot = static function (array $item, string $side): void {
    $id   = (int) ($item[$side . 'Id'] ?? 0);
    $alt  = (string) ($item[$side . 'Alt'] ?? '');
    $note = (string) ($item[$side . 'Note'] ?? '');
    ?>
    <div class="fm-halo-plate fm-halo-results__shot">
      <?php if ($id > 0) : ?>
        <?php echo fm_image($id, 'medium_large', ['alt' => esc_attr($alt)]); ?>
      <?php elseif ($note !== '') : ?>
        <span class="fm-halo-plate__label"><?php echo esc_html($note); ?></span>
      <?php endif; ?>
    </div>
    <?php
};
?>
<section <?php echo fm_wrapper(['fm-halo-results', 'fm-halo-band']); ?>>
  <?php if ($eyebrow !== '') : ?>
    <span class="fm-halo-eyebrow"><?php echo esc_html($eyebrow); ?></span>
  <?php endif; ?>

  <?php if ($heading !== '') : ?>
    <h2 class="fm-halo-display fm-halo-results__heading"><?php echo esc_html($heading); ?></h2>
  <?php endif; ?>

  <div class="fm-halo-results__grid">
    <?php foreach ($items as $item) : ?>
      <figure class="fm-halo-results__item">
        <div class="fm-halo-results__pair">
          <?php $shot($item, 'before'); ?>
          <?php $shot($item, 'after'); ?>
        </div>

        <figcaption class="fm-halo-results__caption">
          <?php if ((string) ($item['label'] ?? '') !== '') : ?>
            <span class="fm-halo-results__label"><?php echo esc_html((string) $item['label']); ?></span>
          <?php endif; ?>
          <?php if ((string) ($item['caption'] ?? '') !== '') : ?>
            <span class="fm-halo-results__text"><?php echo esc_html((string) $item['caption']); ?></span>
          <?php endif; ?>
        </figcaption>
      </figure>
    <?php endforeach; ?>
  </div>

  <?php if ($consent !== '') : ?>
    <p class="fm-halo-results__consent"><?php echo esc_html($consent); ?></p>
  <?php endif; ?>
</section>
