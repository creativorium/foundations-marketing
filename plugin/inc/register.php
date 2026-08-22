<?php
/**
 * Block registration.
 *
 * Blocks are discovered by scanning src/blocks/ for block.json — adding a component
 * means adding a folder, nothing here changes. Each block.json points at its own
 * render.php via `render: file:./render.php`, so markup stays inside the component.
 *
 * block.json deliberately does NOT declare editorScript/style handles: every block's
 * JS and CSS is compiled into one shared bundle (build/editor.js, build/frontend.css)
 * and enqueued once in inc/assets.php. Per-block handles would mean per-block files,
 * which is more requests, not fewer.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

function fm_register_blocks(): void
{
    $blocks_dir = FM_BLOCKS_DIR . 'src/blocks';

    if (!is_dir($blocks_dir)) {
        return;
    }

    foreach (glob($blocks_dir . '/*/block.json') ?: [] as $metadata) {
        register_block_type(dirname($metadata));
    }
}
add_action('init', 'fm_register_blocks');

/**
 * A dedicated block category keeps our components out of the generic lists, so an
 * editor picking a section sees only the ones this site actually has.
 */
function fm_block_category(array $categories): array
{
    array_unshift($categories, [
        'slug'  => 'foundations',
        'title' => __('Foundations', 'foundations'),
    ]);

    return $categories;
}
add_filter('block_categories_all', 'fm_block_category');
