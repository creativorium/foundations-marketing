<?php
/**
 * The two shell blocks — site-header and site-footer.
 *
 * WHY THESE LIVE IN THE THEME AND NOT IN THE CUSTOMER PLUGIN.
 *
 * customer-runtime.md §4 puts "the selected template's blocks" in the generated plugin.
 * These two are not a template's blocks — they are the site shell, identical in every
 * design apart from a variant and the tokens around them. Putting them in the theme means:
 *
 *   - parts/header.html renders whether or not the plugin is present, so the theme is
 *     testable on its own. That is what this branch depends on.
 *   - There is one header implementation to fix, not one per design.
 *   - The parts and the blocks they contain version together, which is what the packager
 *     stamps as "Foundations Base".
 *
 * A design varies the shell through `variant` attributes, its theme.json tokens, and its
 * own style.scss — not by forking these files.
 *
 * They are registered server-side only: no editor script, `inserter: false`. The customer
 * cannot reach the Site Editor (customer-runtime.md §5.2), so there is nothing to insert
 * them with and no editor UI to build.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return array<int, string>
 */
function fm_base_shell_blocks(): array
{
    return ['site-header', 'site-footer'];
}

function fm_base_register_shell_blocks(): void
{
    foreach (fm_base_shell_blocks() as $name) {
        $dir = FM_BASE_DIR . '/blocks/' . $name;

        // A missing block.json would fatal register_block_type_from_metadata on some
        // versions; a customer site must never white-screen because a file was dropped
        // from a package.
        if (is_readable($dir . '/block.json')) {
            register_block_type($dir);
        }
    }
}
add_action('init', 'fm_base_register_shell_blocks');
