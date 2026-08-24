<?php
/**
 * Theme supports and menu registration.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

function fm_theme_setup(): void
{
    load_theme_textdomain('foundations-marketing', FM_THEME_DIR . '/languages');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    add_theme_support('html5', [
        'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script',
    ]);

    // WooCommerce runs the storefront; our own templates live in theme/woocommerce/.
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');

    register_nav_menus([
        'primary' => __('Primary navigation', 'foundations-marketing'),
        'footer'  => __('Footer links', 'foundations-marketing'),
    ]);
}
add_action('after_setup_theme', 'fm_theme_setup');

/**
 * The palette is a site-wide choice (Steel or Nari) from the client's design canvas.
 * It is stamped on <html> so every block reads it through CSS custom properties.
 */
function fm_palette(): string
{
    $palette = (string) get_theme_mod('fm_palette', 'steel');

    return in_array($palette, ['steel', 'nari'], true) ? $palette : 'steel';
}

function fm_html_palette_attribute(string $output): string
{
    return $output . ' data-palette="' . esc_attr(fm_palette()) . '"';
}
add_filter('language_attributes', 'fm_html_palette_attribute');

function fm_customize_palette(WP_Customize_Manager $wp_customize): void
{
    $wp_customize->add_setting('fm_palette', [
        'default'           => 'steel',
        'sanitize_callback' => fn ($v): string => in_array($v, ['steel', 'nari'], true) ? $v : 'steel',
    ]);

    $wp_customize->add_control('fm_palette', [
        'label'   => __('Colour scheme', 'foundations-marketing'),
        'section' => 'colors',
        'type'    => 'select',
        'choices' => [
            'steel' => __('Steel (default)', 'foundations-marketing'),
            'nari'  => __('Nari', 'foundations-marketing'),
        ],
    ]);
}
add_action('customize_register', 'fm_customize_palette');

/** Editor gets the same tokens so blocks look right in the admin. */
function fm_editor_styles(): void
{
    add_theme_support('editor-styles');
    add_editor_style('build/main.css');
}
add_action('after_setup_theme', 'fm_editor_styles');
