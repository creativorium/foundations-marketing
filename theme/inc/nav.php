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
 * The secondary links for the foot of the mobile drawer.
 *
 * Sourced from the footer menu rather than hardcoded, so Privacy, Terms and whatever
 * else the business needs stay editable in Appearance > Menus and never drift out of
 * sync with the footer itself.
 *
 * The footer menu also repeats most of the primary menu, though, and a drawer that
 * lists "Services" twice is worse than one with space in it. So the primary menu's own
 * URLs are subtracted, leaving exactly the items the drawer is not already showing.
 * Trailing slashes are normalised before comparing: "/services" and "/services/" are
 * the same destination, and menus are hand-edited, so both spellings turn up.
 *
 * @return WP_Post[]
 */
function fm_drawer_secondary_items(): array
{
    if (!has_nav_menu('footer')) {
        return [];
    }

    $locations = get_nav_menu_locations();

    $footer_items = wp_get_nav_menu_items($locations['footer'] ?? 0);

    if (!$footer_items) {
        return [];
    }

    $primary_urls = [];

    if (has_nav_menu('primary')) {
        foreach ((array) wp_get_nav_menu_items($locations['primary'] ?? 0) as $item) {
            $primary_urls[] = untrailingslashit((string) ($item->url ?? ''));
        }
    }

    $secondary = array_filter(
        $footer_items,
        static fn ($item): bool => !in_array(untrailingslashit((string) ($item->url ?? '')), $primary_urls, true)
    );

    return array_values($secondary);
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
