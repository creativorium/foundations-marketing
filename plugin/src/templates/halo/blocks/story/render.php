<?php
/**
 * Halo — the practitioner introduction.
 *
 * `body` is one textarea, split into paragraphs on blank lines. That is deliberate: this
 * is the one section a customer rewrites entirely in their own words, and asking them to
 * manage a repeater of paragraphs to do it would be a worse tool than a text box.
 *
 * The portrait is arched with a border-radius rather than a clip-path, so it is still a
 * plain photograph when styles fail and never crops a face to a shape nobody can see.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$eyebrow   = (string) ($attributes['eyebrow'] ?? '');
$heading   = (string) ($attributes['heading'] ?? '');
$body      = (string) ($attributes['body'] ?? '');
$image_id  = (int) ($attributes['imageId'] ?? 0);
$image_alt = (string) ($attributes['imageAlt'] ?? '');
$note      = (string) ($attributes['imageNote'] ?? '');

$facts = array_values(array_filter(
    (array) ($attributes['facts'] ?? []),
    static fn ($row): bool => is_array($row) && (string) ($row['v'] ?? '') !== ''
));

// Blank lines separate paragraphs; a single newline inside one is just wrapping.
$paragraphs = array_values(array_filter(
    array_map('trim', preg_split('/\R{2,}/', $body) ?: []),
    static fn (string $p): bool => $p !== ''
));

if ($heading === '' && $paragraphs === []) {
    return;
}
?>
<section <?php echo fm_wrapper(['fm-halo-story', 'fm-halo-band']); ?>>
  <div class="fm-halo-story__grid">
    <div class="fm-halo-story__media">
      <div class="fm-halo-plate fm-halo-story__plate">
        <?php if ($image_id > 0 && empty($attributes['placeholder'])) : ?>
          <?php echo fm_image($image_id, 'large', ['alt' => esc_attr($image_alt)]); ?>
        <?php elseif ($note !== '') : ?>
          <span class="fm-halo-plate__label"><?php echo esc_html($note); ?></span>
        <?php endif; ?>
      </div>
    </div>

    <div class="fm-halo-story__copy">
      <?php if ($eyebrow !== '') : ?>
        <span class="fm-halo-eyebrow"><?php echo esc_html($eyebrow); ?></span>
      <?php endif; ?>

      <?php if ($heading !== '') : ?>
        <h2 class="fm-halo-display fm-halo-story__heading"><?php echo esc_html($heading); ?></h2>
      <?php endif; ?>

      <?php foreach ($paragraphs as $paragraph) : ?>
        <p class="fm-halo-story__body"><?php echo nl2br(esc_html($paragraph), false); ?></p>
      <?php endforeach; ?>

      <?php if ($facts !== []) : ?>
        <dl class="fm-halo-story__facts">
          <?php foreach ($facts as $fact) : ?>
            <div class="fm-halo-story__fact">
              <dt class="fm-halo-story__fact-key"><?php echo esc_html((string) ($fact['k'] ?? '')); ?></dt>
              <dd class="fm-halo-story__fact-value"><?php echo esc_html((string) ($fact['v'] ?? '')); ?></dd>
            </div>
          <?php endforeach; ?>
        </dl>
      <?php endif; ?>
    </div>
  </div>
</section>
