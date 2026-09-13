<?php
/**
 * Foundations Customer Base — bootstrap.
 *
 * This theme is never shipped as-is. scripts/package.mjs assembles a per-design theme
 * from this base plus one template's theme.json, parts/ and styles. See
 * customer-runtime.md §3.
 *
 * Keep this file thin. It defines constants and requires; behaviour lives in inc/.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Base version. The packager rewrites this, the style.css header and the content
 * manifest together — three places, one value, so "what is this client running?" has an
 * answer six months from now (customer-runtime.md §3.2).
 */
define('FM_BASE_VERSION', '1.0.0');
define('FM_BASE_DIR', get_template_directory());
define('FM_BASE_URI', get_template_directory_uri());

// settings-contract.php first: the shell blocks read settings through it.
require_once FM_BASE_DIR . '/inc/settings-contract.php';
require_once FM_BASE_DIR . '/inc/setup.php';
require_once FM_BASE_DIR . '/inc/assets.php';
require_once FM_BASE_DIR . '/inc/shell-blocks.php';
