<?php
/**
 * Plugin Name: Foundations Blocks
 * Plugin URI: https://github.com/creativorium/foundations-marketing
 * Description: Server-rendered Gutenberg blocks for the Foundations Marketing site. Every block is one folder under src/blocks/.
 * Version: 0.1.0
 * Author: Creativorium
 * Author URI: https://creativorium.com
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: foundations
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

define('FM_BLOCKS_VERSION', '0.1.0');
define('FM_BLOCKS_DIR', plugin_dir_path(__FILE__));
define('FM_BLOCKS_URL', plugin_dir_url(__FILE__));

require_once FM_BLOCKS_DIR . 'inc/register.php';
require_once FM_BLOCKS_DIR . 'inc/assets.php';
require_once FM_BLOCKS_DIR . 'inc/helpers.php';
require_once FM_BLOCKS_DIR . 'inc/demo.php';

// The WooCommerce side of the package builder. Loaded only when WooCommerce is
// active: every hook in it is a WooCommerce hook, and on a site without the plugin
// the file is dead weight that also type-hints classes that do not exist.
if (class_exists('WooCommerce')) {
    require_once FM_BLOCKS_DIR . 'inc/checkout.php';
}
