<?php
/**
 * Block registration.
 *
 * Blocks are discovered by scanning for block.json — adding a component means adding a
 * folder, nothing here changes. Each block.json points at its own render.php via
 * `render: file:./render.php`, so markup stays inside the component.
 *
 * Two places are scanned, because we ship two different kinds of block:
 *
 *   src/blocks/<name>/                    THIS website — homepage, services, checkout.
 *   src/templates/<slug>/blocks/<name>/   ONE sold template, and only that one.
 *
 * A sold template is a self-contained mini site: its own blocks, its own palette, its
 * own content, so one folder can be lifted out and installed on a client's hosting
 * without dragging the rest of the catalogue with it. See how-to-work.md §2.1b.
 *
 * Registering here is what gives a block its SERVER side. The editor already knows the
 * block from src/editor.js, so a block missing from this scan still appears in the
 * inserter and still edits — and then renders NOTHING on the front end, because blocks
 * are `save: () => null` and there is no saved markup to fall back on. That failure
 * looks like broken markup and is not; it is a block this function never saw.
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

/**
 * Every block folder we ship, main-site and template alike.
 *
 * @return array<int, string> absolute paths to folders containing a block.json
 */
function fm_block_dirs(): array
{
    $patterns = [
        FM_BLOCKS_DIR . 'src/blocks/*/block.json',
        FM_BLOCKS_DIR . 'src/templates/*/blocks/*/block.json',
    ];

    $dirs = [];

    foreach ($patterns as $pattern) {
        foreach (glob($pattern) ?: [] as $metadata) {
            $dirs[] = dirname($metadata);
        }
    }

    return $dirs;
}

function fm_register_blocks(): void
{
    foreach (fm_block_dirs() as $dir) {
        register_block_type($dir);
    }
}
add_action('init', 'fm_register_blocks');

/**
 * The sold templates that ship blocks of their own.
 *
 * @return array<string, string> slug => absolute path to the template folder
 */
function fm_template_dirs(): array
{
    $found = [];

    foreach (glob(FM_BLOCKS_DIR . 'src/templates/*', GLOB_ONLYDIR) ?: [] as $dir) {
        if (is_dir($dir . '/blocks')) {
            $found[basename($dir)] = $dir;
        }
    }

    return $found;
}

/**
 * A template's display name, from its own template.json, falling back to the folder
 * slug so a half-finished template still shows up sensibly in the inserter.
 */
function fm_template_name(string $slug, string $dir): string
{
    $file = $dir . '/template.json';

    if (is_readable($file)) {
        $data = json_decode((string) file_get_contents($file), true);

        if (is_array($data) && !empty($data['name']) && is_string($data['name'])) {
            return $data['name'];
        }
    }

    return ucwords(str_replace('-', ' ', $slug));
}

/**
 * Dedicated block categories keep our components out of the generic lists, so an editor
 * picking a section sees only what is actually available.
 *
 * Each sold template gets its own category. Without that, a catalogue site carrying
 * several templates would show every template's header, hero and footer in one
 * undifferentiated "Foundations" list, and picking the right one becomes guesswork.
 */
function fm_block_category(array $categories): array
{
    $ours = [
        [
            'slug'  => 'foundations',
            'title' => __('Foundations', 'foundations'),
        ],
    ];

    foreach (fm_template_dirs() as $slug => $dir) {
        $ours[] = [
            'slug'  => 'foundations-' . $slug,
            /* translators: %s: the site template's name. */
            'title' => sprintf(__('Template — %s', 'foundations'), fm_template_name($slug, $dir)),
        ];
    }

    return array_merge($ours, $categories);
}
add_filter('block_categories_all', 'fm_block_category');
