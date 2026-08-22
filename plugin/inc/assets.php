<?php
/**
 * Editor and front-end bundles for the blocks.
 *
 * Vite emits fixed filenames into build/; filemtime() does the cache-busting so
 * nothing has to read a manifest at runtime.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

function fm_blocks_asset_version(string $relative_path): string
{
    $file = FM_BLOCKS_DIR . ltrim($relative_path, '/');

    return file_exists($file) ? (string) filemtime($file) : FM_BLOCKS_VERSION;
}

function fm_blocks_enqueue_editor(): void
{
    wp_enqueue_script(
        'fm-blocks-editor',
        FM_BLOCKS_URL . 'build/editor.js',
        ['wp-blocks', 'wp-block-editor', 'wp-components', 'wp-element', 'wp-i18n', 'wp-data'],
        fm_blocks_asset_version('build/editor.js'),
        true
    );

    wp_set_script_translations('fm-blocks-editor', 'foundations');

    if (file_exists(FM_BLOCKS_DIR . 'build/editor.css')) {
        wp_enqueue_style(
            'fm-blocks-editor',
            FM_BLOCKS_URL . 'build/editor.css',
            [],
            fm_blocks_asset_version('build/editor.css')
        );
    }
}
add_action('enqueue_block_editor_assets', 'fm_blocks_enqueue_editor');

/** Block styles load on both the front end and inside the editor canvas. */
function fm_blocks_enqueue_frontend(): void
{
    wp_enqueue_style(
        'fm-blocks',
        FM_BLOCKS_URL . 'build/frontend.css',
        [],
        fm_blocks_asset_version('build/frontend.css')
    );

    if (file_exists(FM_BLOCKS_DIR . 'build/frontend.js')) {
        wp_enqueue_script(
            'fm-blocks',
            FM_BLOCKS_URL . 'build/frontend.js',
            [],
            fm_blocks_asset_version('build/frontend.js'),
            true
        );
    }
}
add_action('enqueue_block_assets', 'fm_blocks_enqueue_frontend');
