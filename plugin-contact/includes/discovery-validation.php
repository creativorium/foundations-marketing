<?php
/** Pure validation shared by submission and regression tests. */
function fmcd_discovery_setup_errors(array $data): array
{
    $errors = [];
    $fields = [
        'domain_url' => 'domain / website address',
        'domain_provider' => 'domain provider', 'domain_username' => 'domain username',
        'hosting_provider' => 'hosting provider', 'hosting_username' => 'hosting username',
        'email_platform' => 'business email platform', 'email_username' => 'business email username',
    ];
    foreach (['domain', 'hosting', 'email'] as $kind) {
        if (($data[$kind . '_status'] ?? '') !== 'have') {
            $errors[] = 'Your ' . $kind . ' details are required before you can submit.';
        }
    }
    if (($data['booking_status'] ?? '') === 'have') {
        $fields += ['booking_url' => 'booking widget / calendar link', 'booking_platform' => 'booking platform', 'booking_username' => 'booking username'];
    } elseif (($data['booking_status'] ?? '') !== 'not_applicable') {
        $errors[] = 'Choose a booking widget or “No booking widget needed”.';
    }
    foreach ($fields as $field => $label) {
        $value = $data[$field] ?? '';
        if (!is_string($value) || trim($value) === '') {
            $errors[] = 'Please provide your ' . $label . '.';
        } elseif (str_ends_with($field, '_url') && (!filter_var($value, FILTER_VALIDATE_URL) || !in_array(strtolower((string) parse_url($value, PHP_URL_SCHEME)), ['http', 'https'], true))) {
            $errors[] = 'Please provide a valid https:// link for your ' . $label . '.';
        }
    }
    return $errors;
}
