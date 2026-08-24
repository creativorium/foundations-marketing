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

/**
 * The site templates offered in the catalogue.
 *
 * Reads the `site_template` CPT registered by the Foundation Packages plugin, so blocks
 * never touch the database or WP_Query themselves — that is the boundary that lets a
 * front-end contributor work in one folder (CONTRIBUTING.md §3).
 *
 * Returns a plain array of scalars, ready to echo. Every value is already the raw string;
 * escape at the point of output, not here.
 *
 * @param int $limit Maximum templates to return. -1 for all.
 *
 * @return array<int, array{
 *     id:int, name:string, niche:string, description:string, category:string,
 *     sections:array<int,string>, url:string, thumb_id:int, packages:array<int,string>
 * }>
 */
function fm_get_templates(int $limit = 9): array
{
    if (!post_type_exists('site_template')) {
        return [];
    }

    $query = new WP_Query([
        'post_type'              => 'site_template',
        'post_status'            => 'publish',
        'posts_per_page'         => $limit,
        'orderby'                => 'menu_order title',
        'order'                  => 'ASC',
        'ignore_sticky_posts'    => true,
        // We only need the post rows plus meta; skip the term cache entirely.
        'update_post_term_cache' => false,
        'no_found_rows'          => true,
    ]);

    $templates = [];

    foreach ($query->posts as $post) {
        $packages = get_post_meta($post->ID, 'tpl_packages', true);
        $sections = get_post_meta($post->ID, 'tpl_sections', true);

        // Sections may be stored as an array or as one-per-line text, depending on
        // how the template was entered. Accept either.
        if (is_string($sections) && $sections !== '') {
            $sections = preg_split('/
||
/', $sections) ?: [];
        }

        $sections = is_array($sections)
            ? array_values(array_filter(array_map('trim', array_map('strval', $sections))))
            : [];

        $templates[] = [
            'id'          => (int) $post->ID,
            'name'        => (string) $post->post_title,
            'niche'       => (string) get_post_meta($post->ID, 'tpl_niche', true),
            'description' => (string) get_post_meta($post->ID, 'tpl_description', true),
            'category'    => (string) get_post_meta($post->ID, 'tpl_category', true),
            'sections'    => $sections,
            'url'         => (string) get_post_meta($post->ID, 'tpl_demo_url', true),
            'thumb_id'    => (int) get_post_thumbnail_id($post->ID),
            'packages'    => is_array($packages) ? array_map('strval', $packages) : [],
        ];
    }

    wp_reset_postdata();

    return $templates;
}
