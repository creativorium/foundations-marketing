<?php
/**
 * Performance.
 *
 * Target: 85+ on Google PageSpeed for mobile (SEO strategy §4). The biggest wins on
 * a WooCommerce site are not micro-optimisations — they are (a) not shipping
 * WooCommerce's CSS/JS on pages that have no shop on them, (b) not shipping jQuery
 * to the front end, and (c) not letting WordPress emit assets nobody uses.
 *
 * Everything here is conservative: if a page might be a shop page, it keeps its assets.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/** True when the current request actually needs the WooCommerce front-end bundle. */
function fm_is_woo_page(): bool
{
    if (!function_exists('is_woocommerce')) {
        return false;
    }

    if (is_woocommerce() || is_cart() || is_checkout() || is_account_page()) {
        return true;
    }

    // A page can embed a shop shortcode or a Woo block without being a Woo page.
    $post = get_post();

    if ($post instanceof WP_Post) {
        $needles = ['[woocommerce_', '[products', 'wp:woocommerce/', '[add_to_cart'];

        foreach ($needles as $needle) {
            if (str_contains((string) $post->post_content, $needle)) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Drop WooCommerce's styles and scripts on pages that have no commerce on them.
 * On a marketing homepage this alone removes several hundred KB.
 */
function fm_dequeue_woo_assets(): void
{
    if (is_admin() || fm_is_woo_page()) {
        return;
    }

    foreach (['woocommerce-general', 'woocommerce-layout', 'woocommerce-smallscreen', 'wc-blocks-style'] as $handle) {
        wp_dequeue_style($handle);
    }

    foreach (['woocommerce', 'wc-cart-fragments', 'wc-add-to-cart', 'woocommerce-js', 'jquery-blockui'] as $handle) {
        wp_dequeue_script($handle);
    }
}
add_action('wp_enqueue_scripts', 'fm_dequeue_woo_assets', 99);

/**
 * WooCommerce's cart fragments AJAX call fires on every page load and is one of the
 * most common causes of a slow WooCommerce site. Off where there is no cart.
 */
function fm_disable_cart_fragments(): void
{
    if (!is_admin() && !fm_is_woo_page()) {
        wp_dequeue_script('wc-cart-fragments');
    }
}
add_action('wp_enqueue_scripts', 'fm_disable_cart_fragments', 100);

/**
 * The block library's default stylesheet styles core blocks we do not use — ours are
 * all custom and bring their own CSS. Kept in the editor, dropped on the front end.
 */
function fm_dequeue_unused_core_styles(): void
{
    if (is_admin()) {
        return;
    }

    wp_dequeue_style('classic-theme-styles');
    wp_dequeue_style('global-styles');
}
add_action('wp_enqueue_scripts', 'fm_dequeue_unused_core_styles', 100);

/** Emoji support costs a script, a stylesheet and a DNS prefetch, for emoji the design never uses. */
function fm_disable_emoji(): void
{
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

    add_filter('tiny_mce_plugins', function (array $plugins): array {
        return array_diff($plugins, ['wpemoji']);
    });

    add_filter('emoji_svg_url', '__return_false');
}
add_action('init', 'fm_disable_emoji');

/** Head cleanup: output nobody consumes, on every single page. */
function fm_clean_head(): void
{
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'wp_shortlink_wp_head');
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
}
add_action('init', 'fm_clean_head');

/**
 * The first image on a page is almost always the LCP element. WordPress lazy-loads
 * it by default, which delays the paint — so exempt it and give it high priority.
 */
function fm_first_image_is_eager(string $html, string $context, $attachment_id): string
{
    static $seen = false;

    if ($seen || $context !== 'the_content') {
        return $html;
    }

    $seen = true;

    $html = str_replace(' loading="lazy"', ' loading="eager" fetchpriority="high"', $html);

    return $html;
}
add_filter('wp_get_attachment_image', 'fm_first_image_is_eager', 10, 3);

/**
 * WebP/AVIF uploads (SEO strategy §4 asks for WebP). WordPress allows WebP already;
 * this adds AVIF for browsers that take it, at a further ~30% saving.
 */
function fm_allow_modern_images(array $mimes): array
{
    $mimes['webp'] = 'image/webp';
    $mimes['avif'] = 'image/avif';

    return $mimes;
}
add_filter('upload_mimes', 'fm_allow_modern_images');

/** JPEG quality: 82 is visually indistinguishable from 90 and noticeably smaller. */
add_filter('jpeg_quality', fn (): int => 82);
add_filter('wp_editor_set_quality', fn (): int => 82);

/**
 * Speculative prerendering: the browser fetches a page the moment the user hovers a
 * link, so same-site navigation feels instant. Conservative rules — GET only, and
 * never the cart, checkout, account or admin.
 */
function fm_speculation_rules(): void
{
    if (is_admin() || is_user_logged_in()) {
        return;
    }

    $rules = [
        'prerender' => [[
            'source'    => 'document',
            'where'     => [
                'and' => [
                    ['href_matches' => '/*'],
                    ['not' => ['href_matches' => ['/wp-admin/*', '/wp-login.php', '/cart/*', '/checkout/*', '/my-account/*']]],
                    ['not' => ['selector_matches' => '[rel~="nofollow"]']],
                ],
            ],
            'eagerness' => 'moderate',
        ]],
    ];

    printf(
        '<script type="speculationrules">%s</script>' . "\n",
        wp_json_encode($rules)
    );
}
add_action('wp_footer', 'fm_speculation_rules');
