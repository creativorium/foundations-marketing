<?php
/**
 * Marquee band — the scrolling strip of niches under the hero.
 *
 * Accessibility: the strip is duplicated so the loop has no visible seam, but a screen
 * reader must not hear the list twice — the second copy is aria-hidden. The animation
 * is disabled entirely under prefers-reduced-motion (see style.scss), where it becomes
 * a normal horizontally scrollable strip.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$raw = (string) ($attributes['items'] ?? '');

// One item per line in the editor; joined with the design's em-dash separator.
$items = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $raw) ?: [])));

if ($items === []) {
    return;
}

$speed  = max(10, min(200, (int) ($attributes['speed'] ?? 46)));
$spaced = (bool) ($attributes['spacedTop'] ?? true);
$strip  = implode(' — ', $items);

$classes = ['fm-marquee'];

if ($spaced) {
    $classes[] = 'fm-marquee--spaced';
}
?>
<div <?php echo fm_wrapper($classes, ['style' => '--fm-marquee-speed:' . $speed . 's']); ?>>
    <div class="fm-marquee__track">
        <span class="fm-marquee__strip"><?php echo esc_html($strip); ?></span>
        <span class="fm-marquee__strip" aria-hidden="true"><?php echo esc_html($strip); ?></span>
    </div>
</div>
