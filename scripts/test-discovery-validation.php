<?php
require __DIR__ . '/../plugin-contact/includes/discovery-validation.php';
$valid = [
    'domain_status' => 'have', 'hosting_status' => 'have', 'email_status' => 'have',
    'domain_url' => 'https://example.org', 'domain_provider' => 'Registrar', 'domain_username' => 'owner',
    'hosting_provider' => 'Host', 'hosting_username' => 'owner',
    'email_platform' => 'Mail', 'email_username' => 'owner@example.org',
    'booking_status' => 'not_applicable',
];
$cases = ['no booking needed' => [$valid, false]];
foreach (['domain_url', 'domain_provider', 'domain_username', 'hosting_provider', 'hosting_username', 'email_platform', 'email_username'] as $field) {
    $missing = $valid; unset($missing[$field]);
    $cases['missing ' . $field] = [$missing, true];
}
foreach (['domain', 'hosting', 'email'] as $kind) {
    $cases[$kind . ' not ready'] = [array_replace($valid, [$kind . '_status' => 'need']), true];
}
$cases['booking not selected'] = [array_replace($valid, ['booking_status' => '']), true];
$cases['booking missing details'] = [array_replace($valid, ['booking_status' => 'have']), true];
$booking = array_replace($valid, ['booking_status' => 'have', 'booking_url' => 'https://example.org/calendar', 'booking_platform' => 'Calendar', 'booking_username' => 'owner']);
$cases['booking complete'] = [$booking, false];
$cases['invalid domain'] = [array_replace($valid, ['domain_url' => 'not-a-domain']), true];
$cases['invalid booking link'] = [array_replace($booking, ['booking_url' => 'javascript:alert(1)']), true];
foreach ($cases as $name => [$data, $shouldFail]) {
    if ((fmcd_discovery_setup_errors($data) !== []) !== $shouldFail) {
        throw new RuntimeException('Failed: ' . $name);
    }
    echo 'pass ' . $name . PHP_EOL;
}
