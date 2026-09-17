<?php
/**
 * Onyx — treatment pillars.
 *
 * Each card is a real <a>, not a clickable <div> (how-to-work.md §8): the whole card is
 * the target, so it has to be focusable and announced as a link. The card heading is an
 * H3 under the section's H2 — the hero owns the page's only H1 (§10).
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
$lead       = (string) ($attributes['lead'] ?? '');
$link_label = (string) ($attributes['linkLabel'] ?? '');
$link_url   = (string) ($attributes['linkUrl'] ?? '');

$items = array_values(array_filter(
    (array) ($attributes['items'] ?? []),
    static fn ($row): bool => is_array($row) && (string) ($row['name'] ?? '') !== ''
));

if ($heading === '' && $items === []) {
    return;
}
?>
<section <?php echo fm_wrapper(['fm-onyx-pillars']); ?>>
  <div class="fm-onyx-pillars__intro">
    <?php if ($eyebrow !== '') : ?>
      <span class="fm-onyx-eyebrow"><?php echo esc_html($eyebrow); ?></span>
    <?php endif; ?>

    <?php if ($heading !== '') : ?>
      <h2 class="fm-onyx-pillars__heading fm-onyx-display"><?php echo esc_html($heading); ?></h2>
    <?php endif; ?>

    <?php if ($lead !== '') : ?>
      <p class="fm-onyx-pillars__lead"><?php echo esc_html($lead); ?></p>
    <?php endif; ?>

    <?php if ($link_label !== '' && $link_url !== '') : ?>
      <a class="fm-onyx-textlink" href="<?php echo fm_url($link_url); ?>">
        <?php echo esc_html($link_label); ?>
        <span aria-hidden="true">&rarr;</span>
      </a>
    <?php endif; ?>
  </div>

  <?php if ($items !== []) : ?>
    <ul class="fm-onyx-pillars__grid">
      <?php foreach ($items as $item) : ?>
        <?php
        $name = (string) ($item['name'] ?? '');
        $url  = (string) ($item['url'] ?? '');
        $num  = (string) ($item['num'] ?? '');
        $desc = (string) ($item['desc'] ?? '');
        $from = (string) ($item['from'] ?? '');
        ?>
        <li class="fm-onyx-pillars__item">
          <?php
          // Without a destination the card is still content worth reading — it just
          // stops being a link. An <a href="#"> here would be a keyboard trap that
          // goes nowhere.
          $tag = $url !== '' ? 'a' : 'div';
          ?>
          <<?php echo esc_attr($tag); ?>
            class="fm-onyx-pillars__card"
            <?php if ($url !== '') : ?>href="<?php echo fm_url($url); ?>"<?php endif; ?>
          >
            <?php if ($num !== '') : ?>
              <span class="fm-onyx-pillars__num" aria-hidden="true"><?php echo esc_html($num); ?></span>
            <?php endif; ?>

            <h3 class="fm-onyx-pillars__name"><?php echo esc_html($name); ?></h3>

            <?php if ($desc !== '') : ?>
              <p class="fm-onyx-pillars__desc"><?php echo esc_html($desc); ?></p>
            <?php endif; ?>

            <?php if ($from !== '') : ?>
              <span class="fm-onyx-pillars__from"><?php echo esc_html($from); ?></span>
            <?php endif; ?>
          </<?php echo esc_attr($tag); ?>>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</section>
