<?php
/**
 * Asset loading.
 *
 * A block theme enqueues style.css by itself, so there is very little to do here — and
 * that is the point. The speed budget is 85+ mobile PageSpeed on shared hosting
 * (how-to-work.md §9), which a customer site meets by shipping almost nothing.
 *
 * What must NOT appear in this file, on any customer site:
 *   - jQuery, or any JS framework
 *   - a webfont loaded from a third party (self-host, and no more than two families)
 *   - an icon font
 *   - layout JavaScript
 *
 * A design's own CSS and any block's frontend JS are compiled by the packager into the
 * customer plugin, not added here (customer-runtime.md §4.1).
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cache-bust by file mtime rather than by theme version.
 *
 * A version string is only bumped when someone remembers to; mtime changes whenever the
 * file does. On a client site we may patch a stylesheet without cutting a release, and a
 * stale cached stylesheet looks exactly like a broken site.
 */
function fm_base_asset_version(string $relative): string
{
    $path = FM_BASE_DIR . '/' . ltrim($relative, '/');

    return file_exists($path) ? (string) filemtime($path) : FM_BASE_VERSION;
}

/**
 * WordPress enqueues a block theme's style.css as 'wp-block-theme-styles' — but with the
 * theme version, not the mtime. Re-point it so an unversioned patch still busts caches.
 */
function fm_base_style_version(): void
{
    if (wp_style_is('wp-block-theme-styles', 'registered')) {
        wp_style_add_data('wp-block-theme-styles', 'ver', fm_base_asset_version('style.css'));
    }
}
add_action('wp_enqueue_scripts', 'fm_base_style_version', 20);

/**
 * Remove the core block library's inline SVG duotone filters and classic theme styles,
 * which a site using none of them still pays for in bytes on every page.
 */
function fm_base_trim_core_assets(): void
{
    wp_dequeue_style('classic-theme-styles');

    // Comment reply script only matters where comments are actually open.
    if (!is_singular() || !comments_open()) {
        wp_dequeue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'fm_base_trim_core_assets', 20);

/**
 * The global-styles SVG filter block is emitted even when no duotone is in use. Customer
 * designs do not use duotone; if one ever does, delete this.
 */
remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
