<?php
/**
 * Onyx — hero.
 *
 * SEO: this block owns the page's ONE H1 (how-to-work.md §10), which is why `level` is
 * not an attribute here — there is nothing to choose. Every other Onyx section is H2 or
 * lower. `highlight` is the accent-coloured tail of the headline, inside the same H1, so
 * the heading text stays one sentence to a crawler and to a screen reader.
 *
 * SPEED: the hero image is the LCP element, so it is eager with fetchpriority high
 * (§9) — fm_image() sets width and height, which is what stops the stats row jumping
 * when it loads.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$heading   = (string) ($attributes['heading'] ?? '');
$highlight = (string) ($attributes['highlight'] ?? '');
$lead      = (string) ($attributes['lead'] ?? '');
$cta_label = (string) ($attributes['ctaLabel'] ?? '');
$cta_url   = (string) ($attributes['ctaUrl'] ?? '');
$image_id  = (int) ($attributes['imageId'] ?? 0);
$image_alt = (string) ($attributes['imageAlt'] ?? '');
$note      = (string) ($attributes['imageNote'] ?? '');

$stats = array_values(array_filter(
    (array) ($attributes['stats'] ?? []),
    static fn ($row): bool => is_array($row) && (string) ($row['v'] ?? '') !== ''
));

// A hero with no headline is a half-configured block, not a design decision. Rendering
// an empty band would look like a broken page; rendering nothing reads as "not filled in".
if ($heading === '') {
    return;
}
?>
<section <?php echo fm_wrapper(['fm-onyx-hero']); ?>>
  <div class="fm-onyx-hero__top">
    <h1 class="fm-onyx-hero__heading fm-onyx-display">
      <?php echo nl2br(esc_html($heading), false); ?>
      <?php if ($highlight !== '') : ?>
        <span class="fm-onyx-hero__highlight"><?php echo esc_html($highlight); ?></span>
      <?php endif; ?>
    </h1>

    <?php if ($lead !== '' || ($cta_label !== '' && $cta_url !== '')) : ?>
      <div class="fm-onyx-hero__aside">
        <?php if ($lead !== '') : ?>
          <p class="fm-onyx-hero__lead"><?php echo esc_html($lead); ?></p>
        <?php endif; ?>

        <?php
        // Both halves or neither: a labelled link with no destination is a dead control
        // the customer cannot see is broken.
        if ($cta_label !== '' && $cta_url !== '') :
            ?>
          <a class="fm-onyx-textlink" href="<?php echo fm_url($cta_url); ?>">
            <?php echo esc_html($cta_label); ?>
            <span aria-hidden="true">&rarr;</span>
          </a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <?php if ($stats !== []) : ?>
    <dl class="fm-onyx-hero__stats">
      <?php foreach ($stats as $stat) : ?>
        <div class="fm-onyx-hero__stat">
          <dt class="fm-onyx-hero__stat-key"><?php echo esc_html((string) ($stat['k'] ?? '')); ?></dt>
          <dd class="fm-onyx-hero__stat-value"><?php echo esc_html((string) ($stat['v'] ?? '')); ?></dd>
        </div>
      <?php endforeach; ?>
    </dl>
  <?php endif; ?>

  <div class="fm-onyx-hero__media">
    <div class="fm-onyx-plate fm-onyx-hero__plate">
      <?php if ($image_id > 0 && empty($attributes['placeholder'])) : ?>
        <?php echo fm_image($image_id, 'full', ['alt' => esc_attr($image_alt)], true); ?>
      <?php elseif ($note !== '') : ?>
        <span class="fm-onyx-plate__label"><?php echo esc_html($note); ?></span>
      <?php endif; ?>
    </div>
  </div>
</section>
