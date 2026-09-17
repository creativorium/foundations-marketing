<?php
/**
 * Halo — the skin concerns grid.
 *
 * Each concern is a real <a> inside a list, never a styled <div> with a click handler:
 * they are navigation, so they must be reachable by keyboard, announced as links, and
 * openable in a new tab like any other link (how-to-work.md §8).
 *
 * A concern with no destination still renders — as plain text rather than a link. The
 * customer may not have a page for every concern on day one, and a link to nowhere is
 * worse than a label.
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

$items = array_values(array_filter(
    (array) ($attributes['items'] ?? []),
    static fn ($row): bool => is_array($row) && (string) ($row['label'] ?? '') !== ''
));

if ($heading === '' && $items === []) {
    return;
}
?>
<section <?php echo fm_wrapper(['fm-halo-concerns', 'fm-halo-band']); ?>>
  <div class="fm-halo-concerns__grid">
    <div class="fm-halo-concerns__intro">
      <?php if ($eyebrow !== '') : ?>
        <span class="fm-halo-eyebrow"><?php echo esc_html($eyebrow); ?></span>
      <?php endif; ?>

      <?php if ($heading !== '') : ?>
        <h2 class="fm-halo-display fm-halo-concerns__heading"><?php echo esc_html($heading); ?></h2>
      <?php endif; ?>

      <?php if ($lead !== '') : ?>
        <p class="fm-halo-lead fm-halo-concerns__lead"><?php echo esc_html($lead); ?></p>
      <?php endif; ?>
    </div>

    <?php if ($items !== []) : ?>
      <ul class="fm-halo-concerns__list">
        <?php foreach ($items as $item) : ?>
          <?php
          $label = (string) ($item['label'] ?? '');
          $url   = (string) ($item['url'] ?? '');
          ?>
          <li class="fm-halo-concerns__item">
            <?php if ($url !== '') : ?>
              <a class="fm-halo-concerns__link" href="<?php echo fm_url($url); ?>"><?php echo esc_html($label); ?></a>
            <?php else : ?>
              <span class="fm-halo-concerns__link fm-halo-concerns__link--static"><?php echo esc_html($label); ?></span>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>
