<?php
/**
 * Onyx — the practitioner band.
 *
 * CONTRAST: this band inverts to ink-on-light. Body copy uses --fm-inverse-muted, which
 * is set light enough in theme.json to clear WCAG AA on the inverse background — the
 * canvas muted tone does not, and reusing it here is the mistake how-to-work.md §8 calls
 * out about muted on an accent band.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$eyebrow    = (string) ($attributes['eyebrow'] ?? '');
$heading    = (string) ($attributes['heading'] ?? '');
$link_label = (string) ($attributes['linkLabel'] ?? '');
$link_url   = (string) ($attributes['linkUrl'] ?? '');
$image_id   = (int) ($attributes['imageId'] ?? 0);
$image_alt  = (string) ($attributes['imageAlt'] ?? '');
$note       = (string) ($attributes['imageNote'] ?? '');

$body = array_values(array_filter(
    array_map('strval', (array) ($attributes['body'] ?? [])),
    static fn (string $p): bool => trim($p) !== ''
));

$credentials = array_values(array_filter(
    array_map('strval', (array) ($attributes['credentials'] ?? [])),
    static fn (string $c): bool => trim($c) !== ''
));

if ($heading === '' && $body === []) {
    return;
}
?>
<section <?php echo fm_wrapper(['fm-onyx-feature']); ?>>
  <div class="fm-onyx-feature__media">
    <div class="fm-onyx-plate fm-onyx-feature__plate">
      <?php if ($image_id > 0 && empty($attributes['placeholder'])) : ?>
        <?php echo fm_image($image_id, 'large', ['alt' => esc_attr($image_alt)]); ?>
      <?php elseif ($note !== '') : ?>
        <span class="fm-onyx-plate__label"><?php echo esc_html($note); ?></span>
      <?php endif; ?>
    </div>
  </div>

  <div class="fm-onyx-feature__body">
    <?php if ($eyebrow !== '') : ?>
      <span class="fm-onyx-eyebrow fm-onyx-feature__eyebrow"><?php echo esc_html($eyebrow); ?></span>
    <?php endif; ?>

    <?php if ($heading !== '') : ?>
      <h2 class="fm-onyx-feature__heading fm-onyx-display"><?php echo esc_html($heading); ?></h2>
    <?php endif; ?>

    <?php foreach ($body as $paragraph) : ?>
      <p class="fm-onyx-feature__para"><?php echo esc_html($paragraph); ?></p>
    <?php endforeach; ?>

    <?php if ($credentials !== []) : ?>
      <ul class="fm-onyx-feature__creds">
        <?php foreach ($credentials as $credential) : ?>
          <li class="fm-onyx-feature__cred"><?php echo esc_html($credential); ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <?php if ($link_label !== '' && $link_url !== '') : ?>
      <a class="fm-onyx-feature__link" href="<?php echo fm_url($link_url); ?>">
        <?php echo esc_html($link_label); ?>
        <span aria-hidden="true">&rarr;</span>
      </a>
    <?php endif; ?>
  </div>
</section>
