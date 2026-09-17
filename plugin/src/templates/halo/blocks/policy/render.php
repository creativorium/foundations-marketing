<?php
/**
 * Halo — the policy body.
 *
 * One block for the whole document rather than a stack of heading and paragraph blocks.
 * A privacy policy is edited as a document — a clause is added, renumbered, reordered —
 * and keeping it as one structured attribute is what lets the contents list and the
 * sections stay in step without anyone maintaining both.
 *
 * The contents list links to real ids on the sections, so it is a table of contents and
 * not a decoration. Each id is sanitised here rather than trusted: it lands in an href
 * and in an attribute, and it comes from an editable field.
 *
 * SEO: every section heading is an H2. The page's single H1 belongs to the page hero
 * above (how-to-work.md §10).
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

// Work the ids out once, so the contents list and the sections cannot disagree. A
// section with no id of its own gets one from its title; a duplicate gets a suffix,
// because two anchors with the same id means the second is unreachable.
$ids  = [];
$seen = [];

foreach ($sections as $index => $section) {
    $id = sanitize_title((string) ($section['id'] ?? '')) ?: sanitize_title((string) $section['title']);
    $id = $id !== '' ? $id : 'section-' . ($index + 1);

    if (isset($seen[$id])) {
        $seen[$id]++;
        $id .= '-' . $seen[$id];
    } else {
        $seen[$id] = 1;
    }

    $ids[$index] = $id;
}
?>
<section <?php echo fm_wrapper(['fm-halo-policy', 'fm-halo-band']); ?>>
  <div class="fm-halo-policy__grid">
    <nav class="fm-halo-policy__contents" aria-label="<?php echo esc_attr($contents_label !== '' ? $contents_label : __('Contents', 'foundations')); ?>">
      <?php if ($contents_label !== '') : ?>
        <h2 class="fm-halo-policy__contents-label"><?php echo esc_html($contents_label); ?></h2>
      <?php endif; ?>

      <ol class="fm-halo-policy__contents-list">
        <?php foreach ($sections as $index => $section) : ?>
          <li>
            <a href="#<?php echo esc_attr($ids[$index]); ?>"><?php echo esc_html((string) $section['title']); ?></a>
          </li>
        <?php endforeach; ?>
      </ol>
    </nav>

    <div class="fm-halo-policy__body">
      <?php foreach ($sections as $index => $section) : ?>
        <?php
        $paragraphs = array_values(array_filter(
            array_map('strval', (array) ($section['body'] ?? [])),
            static fn (string $p): bool => trim($p) !== ''
        ));

        $list = array_values(array_filter(
            array_map('strval', (array) ($section['list'] ?? [])),
            static fn (string $p): bool => trim($p) !== ''
        ));
        ?>
        <article class="fm-halo-policy__section" id="<?php echo esc_attr($ids[$index]); ?>">
          <h2 class="fm-halo-policy__title"><?php echo esc_html((string) $section['title']); ?></h2>

          <?php foreach ($paragraphs as $paragraph) : ?>
            <p class="fm-halo-policy__text"><?php echo esc_html($paragraph); ?></p>
          <?php endforeach; ?>

          <?php if ($list !== []) : ?>
            <ul class="fm-halo-policy__list">
              <?php foreach ($list as $item) : ?>
                <li><?php echo esc_html($item); ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
