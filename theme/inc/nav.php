<?php
/**
 * Navigation output. The design's primary menu ends in a pill-shaped CTA — that is
 * pure CSS (`li:last-child`), so editors just order the menu normally.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render a registered menu, or nothing at all if it has not been assigned yet —
 * never fall back to wp_page_menu(), which would dump every page into the header.
 */
function fm_nav_menu(string $location, array $args = []): void
{
    if (!has_nav_menu($location)) {
        return;
    }

    wp_nav_menu(array_merge([
        'theme_location' => $location,
        'container'      => false,
        'depth'          => 1,
        'fallback_cb'    => false,
    ], $args));
}

/**
 * Strip aria-current from anchor-only menu links.
 *
 * The primary menu is all in-page anchors (/#templates, /#price, …). WordPress compares
 * each item's URL against the current URL, ignores the fragment, and so decides that
 * *every* item is the current page — nine elements on the homepage all announcing
 * "current page" to a screen reader, which is worse than none announcing it.
 *
 * A fragment link is a jump within a page, never a different page, so it should never
 * carry aria-current.
 */
function fm_strip_anchor_aria_current(array $atts, $item): array
{
    $url = $item->url ?? '';

    if (is_string($url) && str_contains($url, '#')) {
        unset($atts['aria-current']);
    }

    return $atts;
}
add_filter('nav_menu_link_attributes', 'fm_strip_anchor_aria_current', 10, 2);

/**
 * The same comparison also adds current-menu-item classes to every anchor link.
 */
function fm_strip_anchor_current_classes(array $classes, $item): array
{
    $url = $item->url ?? '';

    if (is_string($url) && str_contains($url, '#')) {
        $classes = array_diff($classes, [
            'current-menu-item',
            'current_page_item',
            'current-menu-ancestor',
            'current_page_parent',
        ]);
    }

    return $classes;
}
add_filter('nav_menu_css_class', 'fm_strip_anchor_current_classes', 10, 2);
