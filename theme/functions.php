<?php
/**
 * Foundations theme bootstrap.
 *
 * Assets are built by Vite to theme/build/ with fixed filenames and cache-busted
 * with filemtime(), so no build manifest has to be read at runtime — this has to
 * work on plain shared hosting with no Node.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

define('FM_THEME_VERSION', '0.1.0');
define('FM_THEME_DIR', get_stylesheet_directory());
define('FM_THEME_URI', get_stylesheet_directory_uri());

require_once FM_THEME_DIR . '/inc/assets.php';
require_once FM_THEME_DIR . '/inc/setup.php';
require_once FM_THEME_DIR . '/inc/fonts.php';
require_once FM_THEME_DIR . '/inc/nav.php';
require_once FM_THEME_DIR . '/inc/performance.php';
require_once FM_THEME_DIR . '/inc/seo.php';
