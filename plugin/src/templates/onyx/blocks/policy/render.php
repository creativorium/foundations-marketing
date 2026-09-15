<?php
/**
 * Onyx — policy sections.
 *
 * The contents list is DERIVED from the sections rather than stored twice. Two lists kept
 * in step by hand drift the first time someone renames a heading, and a table of contents
 * pointing at an anchor that no longer exists is worse than no contents at all.
 *
 * Anchors are generated with sanitize_title() from the heading, so renaming a section
 * changes both the link and its target together. An explicit `id` per section is still
 * accepted for a heading whose slug would collide.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$contents_label = (string) ($attributes['contentsLabel'] ?? '');

$sections = array_values(array_filter(
    (array) ($attributes['sections'] ?? []),
    static fn ($row): bool => is_array($row) && (string) ($row['title'] ?? '') !== ''
));

if ($sections === []) {
    return;
}

// Resolve every anchor once, up front, so the contents list and the sections cannot
// disagree — and so a duplicate slug is made unique rather than silently linking to
// whichever one the browser reaches first.
$seen = [];

foreach ($sections as $index => $section) {
    $anchor = sanitize_title((string) ($section['id'] ?? ''));

    if ($anchor === '') {
        $anchor = sanitize_title((string) $section['title']);
    }

    if ($anchor === '') {
        $anchor = 'section';
    }

    if (isset($seen[$anchor])) {
        $seen[$anchor]++;
        $anchor .= '-' . $seen[$anchor];
    } else {
        $seen[$anchor] = 1;
    }

    $sections[$index]['anchor'] = $anchor;
}
?>
<section <?php echo fm_wrapper(['fm-onyx-policy']); ?>>
  <nav class="fm-onyx-policy__contents" aria-labelledby="<?php echo esc_attr(wp_unique_id('fm-onyx-contents-')); ?>">
    <h2 class="fm-onyx-policy__contents-label">
      <?php echo esc_html($contents_label !== '' ? $contents_label : __('Contents', 'foundations')); ?>
    </h2>
    <ol class="fm-onyx-policy__contents-list">
      <?php foreach ($sections as $section) : ?>
        <li>
          <a href="#<?php echo esc_attr($section['anchor']); ?>">
            <?php echo esc_html((string) $section['title']); ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ol>
  </nav>

  <div class="fm-onyx-policy__sections">
    <?php foreach ($sections as $section) : ?>
      <?php
      $body = array_values(array_filter(
          array_map('strval', (array) ($section['body'] ?? [])),
          static fn (string $p): bool => trim($p) !== ''
      ));

      $list = array_values(array_filter(
          array_map('strval', (array) ($section['list'] ?? [])),
          static fn (string $p): bool => trim($p) !== ''
      ));
      ?>
      <section class="fm-onyx-policy__section" id="<?php echo esc_attr($section['anchor']); ?>">
        <h3 class="fm-onyx-policy__title"><?php echo esc_html((string) $section['title']); ?></h3>

        <?php foreach ($body as $paragraph) : ?>
          <p class="fm-onyx-policy__para"><?php echo esc_html($paragraph); ?></p>
        <?php endforeach; ?>

        <?php if ($list !== []) : ?>
          <ul class="fm-onyx-policy__list">
            <?php foreach ($list as $line) : ?>
              <li><?php echo esc_html($line); ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>
    <?php endforeach; ?>
  </div>
</section>
