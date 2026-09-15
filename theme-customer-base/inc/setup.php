<?php
/**
 * Theme supports.
 *
 * Deliberately short. A block theme gets most of what it needs from theme.json; anything
 * added here is something theme.json cannot express.
 *
 * NOT here, on purpose: WooCommerce support, a Customizer palette control, and classic
 * nav menu locations. The marketing theme in theme/ has all three and none belongs on a
 * customer site — WooCommerce because the customer is not selling our packages, the
 * Customizer because settings live in one Site Settings screen (customer-runtime.md §5),
 * and nav menus because registering a location would put Appearance → Menus back in play
 * and reopen the capability conflict §5.2 exists to resolve.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

function fm_base_setup(): void
{
    load_theme_textdomain('foundations-customer', FM_BASE_DIR . '/languages');

    // Block theme essentials.
    add_theme_support('wp-block-styles');
    add_theme_support('editor-styles');
    add_theme_support('responsive-embeds');
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', [
        'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script',
    ]);

    // The editor loads style.css too, so the --fm-* bridge exists while editing and a
    // block does not look one way in the editor and another on the page.
    add_editor_style('style.css');
}
add_action('after_setup_theme', 'fm_base_setup');

/**
 * A skip link, injected rather than left to each design to remember.
 *
 * Every template renders its own header markup, so the one accessibility affordance that
 * must never be missing is put here instead — a design cannot forget it, and reviewers
 * do not have to check for it on every template. Target is the <main> in templates/.
 */
function fm_base_skip_link(): void
{
    printf(
        '<a class="fm-skip-link" href="#fm-content">%s</a>',
        esc_html__('Skip to content', 'foundations-customer')
    );
}
add_action('wp_body_open', 'fm_base_skip_link');

/** Staff preview shell parts; customers retain their restricted role. */
add_action('enqueue_block_editor_assets', function (): void {
    wp_enqueue_script('fm-shell-editor', FM_BASE_URI . '/assets/editor.js', ['wp-blocks','wp-element','wp-block-editor','wp-server-side-render'], FM_BASE_VERSION, true);
});
