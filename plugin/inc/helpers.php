<?php
/**
 * Shared helpers for block render.php files.
 *
 * Front-end contributors call these instead of touching the database directly —
 * that is what keeps a component folder self-contained (see CONTRIBUTING.md §3).
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Build a class attribute from the block wrapper plus extra classes.
 * Always use this so alignment, custom colours and editor-set classes survive.
 */
function fm_wrapper(array $extra_classes = [], array $extra_attributes = []): string
{
    $classes = array_filter($extra_classes);

    return get_block_wrapper_attributes(array_merge(
        $classes ? ['class' => implode(' ', $classes)] : [],
        $extra_attributes
    ));
}

/**
 * Section eyebrow — the "06 — Questions" rule that opens most bands in the design.
 * Returns escaped markup; pass plain strings.
 */
function fm_section_rule(string $number, string $label, string $aside = ''): string
{
    ob_start(); ?>
    <div class="fm-section-rule">
        <span><?php echo esc_html($number . ' — ' . $label); ?></span>
        <?php if ($aside !== '') : ?>
            <span class="fm-section-rule__aside"><?php echo esc_html($aside); ?></span>
        <?php endif; ?>
    </div>
    <?php
    return (string) ob_get_clean();
}

/**
 * Responsive image by attachment id, sized for a block, with width/height so the
 * browser reserves space (no layout shift) and lazy/async by default.
 *
 * Pass $eager = true for anything above the fold — the hero image must NOT be lazy,
 * it is the LCP element.
 */
function fm_image(int $attachment_id, string $size = 'large', array $attr = [], bool $eager = false): string
{
    if ($attachment_id <= 0) {
        return '';
    }

    $defaults = $eager
        ? ['loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async']
        : ['loading' => 'lazy', 'decoding' => 'async'];

    return (string) wp_get_attachment_image($attachment_id, $size, false, array_merge($defaults, $attr));
}

/** Escaped internal link, falling back to '#' so a half-configured block never breaks the page. */
function fm_url(string $url): string
{
    return $url !== '' ? esc_url($url) : '#';
}
