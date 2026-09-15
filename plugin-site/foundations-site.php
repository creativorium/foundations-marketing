<?php
/**
 * Plugin Name: Foundations Site
 * Description: Editable customer settings, portable content and a non-blocking keycard.
 * Version: 0.1.0
 * Requires PHP: 8.0
 * Requires at least: 6.4
 * License: GPL-2.0-or-later
 */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
define('FM_SITE_DIR', __DIR__ . '/');
require_once FM_SITE_DIR . 'inc/bundle.php';
require_once FM_SITE_DIR . 'inc/settings.php';
require_once FM_SITE_DIR . 'inc/tools.php';
register_activation_hook(__FILE__, 'fm_site_roles');
add_action('init', function (): void {
    foreach (glob(FM_SITE_DIR . 'blocks/*/block.json') ?: [] as $file) { register_block_type(dirname($file)); }
});
add_filter('fm_settings_extensions', function (array $fields): array {
    $file = FM_SITE_DIR . 'template.json';
    $data = is_file($file) ? json_decode((string) file_get_contents($file), true) : [];
    $extensions = array_filter((array) ($data['settings'] ?? []),
        static fn($value, $key) => is_array($value) && !str_starts_with((string) $key, '$'),
        ARRAY_FILTER_USE_BOTH);
    return array_merge($fields, $extensions);
});
add_action('enqueue_block_editor_assets', function (): void {
    if (is_file(FM_SITE_DIR . 'build/editor.js')) {
        wp_enqueue_script('fm-site-blocks', plugins_url('build/editor.js', __FILE__), ['wp-blocks','wp-element','wp-block-editor','wp-components','wp-i18n','wp-data','wp-server-side-render'], (string) filemtime(FM_SITE_DIR . 'build/editor.js'), true);
    }
    if (is_file(FM_SITE_DIR . 'build/editor.css')) { wp_enqueue_style('fm-site-editor', plugins_url('build/editor.css', __FILE__), [], (string) filemtime(FM_SITE_DIR . 'build/editor.css')); }
});
add_action('enqueue_block_assets', function (): void {
    foreach (['css', 'js'] as $ext) {
        $file = 'build/frontend.' . $ext;
        if (!is_file(FM_SITE_DIR . $file)) { continue; }
        if ($ext === 'css') { wp_enqueue_style('fm-site-blocks', plugins_url($file, __FILE__), [], (string) filemtime(FM_SITE_DIR . $file)); }
        elseif (!is_admin()) { wp_enqueue_script('fm-site-blocks', plugins_url($file, __FILE__), [], (string) filemtime(FM_SITE_DIR . $file), true); }
    }
});
