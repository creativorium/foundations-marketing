<?php
/** Activate the two first-party plugins after an automated file deployment. */
declare(strict_types=1);

if (PHP_SAPI !== 'cli' || count($argv) !== 2) {
    http_response_code(404);
    exit(1);
}

$wp_load = realpath((string) $argv[1]);

if ($wp_load === false || basename($wp_load) !== 'wp-load.php') {
    fwrite(STDERR, "Pass the target WordPress wp-load.php path.\n");
    exit(1);
}

require $wp_load;
require_once ABSPATH . 'wp-admin/includes/plugin.php';

foreach (['fm-contact-discovery/fm-contact-discovery.php', 'foundations-delivery/foundations-delivery.php'] as $plugin) {
    if (is_plugin_active($plugin)) {
        echo $plugin . " already active.\n";
        continue;
    }

    $result = activate_plugin($plugin);

    if (is_wp_error($result)) {
        fwrite(STDERR, $plugin . ': ' . $result->get_error_message() . "\n");
        exit(1);
    }

    echo $plugin . " activated.\n";
}

if (!isset($GLOBALS['fmcd_plugin']) || !$GLOBALS['fmcd_plugin'] instanceof FMCD_Plugin) {
    fwrite(STDERR, "The contact plugin did not initialize.\n");
    exit(1);
}

$GLOBALS['fmcd_plugin']->install_native_form_pages();

foreach ([
    'contact-page' => '[fm_contact_page]',
    'foundation-website-discovery-form' => '[fm_discovery_page]',
] as $slug => $shortcode) {
    $page = get_page_by_path($slug, OBJECT, 'page');
    if (!$page instanceof WP_Post || $page->post_status !== 'publish' || !str_contains($page->post_content, $shortcode)) {
        fwrite(STDERR, $slug . " is not a published native form page.\n");
        exit(1);
    }

    echo $slug . " verified.\n";
}
