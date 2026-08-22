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
 * The first image on a page is almost always the LCP element, and lazy-loading it
 * delays the paint. Since WordPress 6.3 core works this out itself — it marks the
 * first few images as not-lazy and puts fetchpriority="high" on the one it believes
 * is the LCP — so we do not second-guess it with a filter of our own.
 *
 * What we control is the threshold: how many images from the top of the document are
 * exempt from lazy-loading. Our pages open with a hero image and, on the template
 * grid, a row of cards, so 2 is a better fit than the default.
 *
 * Blocks that know they render above the fold should still pass $eager = true to
 * fm_image() — that is explicit and does not depend on document order.
 */
add_filter('wp_omit_loading_attr_threshold', fn (): int => 2);

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

/**
 * Elementor is still active because the template demo pages are built with it, but it
 * enqueues its frontend CSS/JS on *every* page — including the block-built ones that
 * contain no Elementor content at all. On those, its stylesheets alone are a few hundred
 * KB of render-blocking CSS for markup that does not exist.
 *
 * This drops Elementor's assets only when the current page genuinely has no Elementor
 * data. As pages are migrated to blocks they each get faster automatically, and when the
 * last Elementor page is gone this whole function can be deleted along with the plugin.
 *
 * Deliberately conservative: anything that might be Elementor keeps its assets.
 */
function fm_page_uses_elementor(): bool
{
    if (!did_action('elementor/loaded')) {
        return false;
    }

    if (!is_singular()) {
        // Archives can render Elementor templates; never strip assets there.
        return true;
    }

    $post_id = get_queried_object_id();

    if ($post_id <= 0) {
        return true;
    }

    // Elementor sets this on every post it has ever edited.
    if (get_post_meta($post_id, '_elementor_edit_mode', true) === 'builder') {
        return true;
    }

    // Elementor Pro theme parts (header/footer/popup) render outside the post.
    if (function_exists('elementor_theme_do_location')) {
        foreach (['header', 'footer', 'single', 'archive'] as $location) {
            if (\Elementor\Plugin::$instance->documents ?? null) {
                // If Pro has a template assigned to any location, keep everything.
                if (apply_filters('fm_elementor_has_location_' . $location, false)) {
                    return true;
                }
            }
        }
    }

    return false;
}

function fm_dequeue_elementor_assets(): void
{
    if (is_admin() || fm_page_uses_elementor()) {
        return;
    }

    $styles = [
        'elementor-frontend', 'elementor-post', 'elementor-icons',
        'elementor-common', 'elementor-global', 'elementor-gf-local-roboto',
        'elementor-gf-local-robotoslab', 'e-animations', 'widget-image',
        'widget-heading', 'widget-text-editor', 'swiper', 'e-swiper',
    ];

    foreach ($styles as $handle) {
        wp_dequeue_style($handle);
        wp_deregister_style($handle);
    }

    $scripts = [
        'elementor-frontend', 'elementor-frontend-modules', 'elementor-webpack-runtime',
        'elementor-common', 'elementor-pro-frontend', 'e-sticky', 'swiper', 'e-swiper',
        'elementor-waypoints', 'preloaded-modules', 'share-link', 'imagesloaded',
    ];

    foreach ($scripts as $handle) {
        wp_dequeue_script($handle);
        wp_deregister_script($handle);
    }
}
add_action('wp_enqueue_scripts', 'fm_dequeue_elementor_assets', 999);

add_action('wp_print_styles', 'fm_dequeue_elementor_assets', 100);

/**
 * Elementor's global kit enqueues its Google Fonts while the document renders, which is
 * after every dequeue pass has already run — so dequeuing cannot catch them whatever
 * priority it uses. Suppressing the tag at output time works regardless of when the
 * style was registered.
 *
 * The kit asks for Roboto and Roboto Slab in every weight and italic. The design uses
 * neither, so on a block-built page that is two full font families and a third-party
 * connection fetched for text that never renders in them.
 */
function fm_suppress_elementor_kit_fonts(string $tag, string $handle): string
{
    if (is_admin() || !str_starts_with($handle, 'elementor-gf-')) {
        return $tag;
    }

    return fm_page_uses_elementor() ? $tag : '';
}
add_filter('style_loader_tag', 'fm_suppress_elementor_kit_fonts', 10, 2);
