<?php
/**
 * Asset loading. One helper, used everywhere, so cache-busting is never forgotten.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Version string for a built file: its mtime, or the theme version if the build
 * is missing (which means someone forgot to run `npm run build`).
 */
function fm_asset_version(string $relative_path): string
{
    $file = FM_THEME_DIR . '/' . ltrim($relative_path, '/');

    return file_exists($file) ? (string) filemtime($file) : FM_THEME_VERSION;
}

function fm_enqueue_frontend(): void
{
    if (fm_fonts_are_self_hosted()) {
        // Same-origin fonts: no third-party connection at all. The @font-face rules
        // are inlined so they are parsed before the main stylesheet lands.
        wp_register_style('fm-fonts', false);
        wp_enqueue_style('fm-fonts');
        wp_add_inline_style('fm-fonts', fm_font_face_css());
    } else {
        // Fallback until the woff2 files are dropped into theme/assets/fonts/.
        wp_enqueue_style(
            'fm-fonts',
            'https://fonts.googleapis.com/css2'
                . '?family=Archivo:wght@400..800'
                . '&family=Instrument+Serif:ital@0;1'
                . '&display=swap',
            [],
            null
        );
    }

    wp_enqueue_style(
        'fm-main',
        FM_THEME_URI . '/build/main.css',
        ['fm-fonts'],
        fm_asset_version('build/main.css')
    );

    wp_enqueue_script(
        'fm-main',
        FM_THEME_URI . '/build/main.js',
        [],
        fm_asset_version('build/main.js'),
        true
    );
}
add_action('wp_enqueue_scripts', 'fm_enqueue_frontend');

function fm_enqueue_login(): void
{
    wp_enqueue_style(
        'fm-login',
        FM_THEME_URI . '/build/login.css',
        [],
        fm_asset_version('build/login.css')
    );
}
add_action('login_enqueue_scripts', 'fm_enqueue_login');

/** Point the wp-login logo at the site, not wordpress.org. */
add_filter('login_headerurl', fn (): string => home_url('/'));
add_filter('login_headertext', fn (): string => get_bloginfo('name'));

/**
 * Preconnect to the Google Fonts hosts so the display font is not render-blocking
 * for a whole round trip.
 */
function fm_resource_hints(array $hints, string $relation): array
{
    // Only worth a preconnect while we are still on the Google Fonts CDN.
    if ($relation === 'preconnect' && !fm_fonts_are_self_hosted()) {
        $hints[] = ['href' => 'https://fonts.gstatic.com', 'crossorigin' => ''];
    }

    return $hints;
}
add_filter('wp_resource_hints', 'fm_resource_hints', 10, 2);
