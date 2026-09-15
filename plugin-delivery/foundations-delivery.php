<?php
/**
 * Plugin Name: Foundations Delivery
 * Description: Master designs, customer projects, previews, releases and always-active keycards.
 * Version: 0.1.0
 * Requires PHP: 8.0
 * License: GPL-2.0-or-later
 */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
define('FM_DELIVERY_MANAGER',true);
define('FM_DELIVERY_DIR',__DIR__.'/');
if(!is_file(FM_DELIVERY_DIR.'runtime/inc/bundle.php')) {
    add_action('admin_notices',function(){echo '<div class="notice notice-error"><p>Foundations Delivery: run npm run build:delivery and deploy the runtime directory.</p></div>';});return;
}
require_once FM_DELIVERY_DIR.'runtime/inc/bundle.php';
if(!function_exists('fm_setting')){require_once FM_DELIVERY_DIR.'runtime/base/inc/settings-contract.php';}
if(!function_exists('fm_wrapper')){require_once FM_DELIVERY_DIR.'runtime/helpers.php';}
require_once FM_DELIVERY_DIR.'runtime/inc/settings.php';
require_once FM_DELIVERY_DIR.'inc/manager.php';
require_once FM_DELIVERY_DIR.'inc/preview.php';
require_once FM_DELIVERY_DIR.'inc/catalogue.php';
register_activation_hook(__FILE__,function(){fm_delivery_types();fm_delivery_routes();flush_rewrite_rules();});
