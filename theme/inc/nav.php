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
