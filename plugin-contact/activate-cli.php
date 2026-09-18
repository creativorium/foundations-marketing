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
