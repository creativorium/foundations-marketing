<?php
/**
 * Onyx — the practitioner story.
 *
 * The timeline is a <dl>: each year genuinely labels what happened in it. The year is
 * the <dt> and the event the <dd>, which is also the order the design draws them, so no
 * CSS reordering is needed here.
 *
 * This block deliberately renders no heading. The About page's H1 comes from
 * onyx-page-hero above it, and a second heading here would either duplicate it or
 * introduce an H2 the design never draws (how-to-work.md §10).
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$standfirst = (string) ($attributes['standfirst'] ?? '');
$image_id   = (int) ($attributes['imageId'] ?? 0);
$image_alt  = (string) ($attributes['imageAlt'] ?? '');
$note       = (string) ($attributes['imageNote'] ?? '');

$body = array_values(array_filter(
    array_map('strval', (array) ($attributes['body'] ?? [])),
    static fn (string $p): bool => trim($p) !== ''
));

$timeline = array_values(array_filter(
    (array) ($attributes['timeline'] ?? []),
    static fn ($row): bool => is_array($row) && (string) ($row['what'] ?? '') !== ''
));

if ($standfirst === '' && $body === [] && $timeline === []) {
    return;
}
?>
<section <?php echo fm_wrapper(['fm-onyx-story']); ?>>
  <div class="fm-onyx-story__media">
    <div class="fm-onyx-plate fm-onyx-story__plate">
      <?php if ($image_id > 0 && empty($attributes['placeholder'])) : ?>
        <?php echo fm_image($image_id, 'large', ['alt' => esc_attr($image_alt)]); ?>
      <?php elseif ($note !== '') : ?>
        <span class="fm-onyx-plate__label"><?php echo esc_html($note); ?></span>
      <?php endif; ?>
    </div>
  </div>

  <div class="fm-onyx-story__body">
    <?php if ($standfirst !== '' || $body !== []) : ?>
      <div class="fm-onyx-story__prose">
        <?php if ($standfirst !== '') : ?>
          <p class="fm-onyx-story__standfirst"><?php echo esc_html($standfirst); ?></p>
        <?php endif; ?>

        <?php foreach ($body as $paragraph) : ?>
          <p class="fm-onyx-story__para"><?php echo esc_html($paragraph); ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($timeline !== []) : ?>
      <dl class="fm-onyx-story__timeline">
        <?php foreach ($timeline as $entry) : ?>
          <div class="fm-onyx-story__entry">
            <dt class="fm-onyx-story__year"><?php echo esc_html((string) ($entry['year'] ?? '')); ?></dt>
            <dd class="fm-onyx-story__what"><?php echo esc_html((string) ($entry['what'] ?? '')); ?></dd>
          </div>
        <?php endforeach; ?>
      </dl>
    <?php endif; ?>
  </div>
</section>
