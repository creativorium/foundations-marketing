<?php
/** Shared, allowlisted data handling for the Pulse Atelier blocks. */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }

function fm_pa_defaults(string $block): array {
    static $cache = [];
    if ($block !== 'pulse-atelier-section') { return []; }
    return $cache[$block] ??= json_decode(file_get_contents(__DIR__ . '/defaults.json'), true);
}

/** Preserve empty editor values, ignore unknown fields, and normalize malformed attributes. */
function fm_pa_record(array $schema, array $value): array {
    $result = [];
    foreach ($schema as $key => $default) {
        $input = $value[$key] ?? $default;
        if (is_array($default)) {
            $result[$key] = is_array($input) ? array_map(
                static fn ($row): array => fm_pa_record($default[0] ?? [], is_array($row) ? $row : []),
                array_slice(array_values($input), 0, 100)
            ) : $default;
        } else {
            $result[$key] = is_scalar($input) ? (string) $input : (string) $default;
        }
    }
    return $result;
}

function fm_pa_settings(array $defaults, array $settings): array {
    $result = [];
    foreach (['fields', 'images'] as $group) {
        $result[$group] = fm_pa_record($defaults[$group], is_array($settings[$group] ?? null) ? $settings[$group] : []);
    }
    $result['lists'] = [];
    foreach ($defaults['lists'] as $key => $rows) {
        $input = $settings['lists'][$key] ?? $rows;
        $result['lists'][$key] = is_array($input) ? array_map(
            static fn ($row): array => fm_pa_record($rows[0] ?? [], is_array($row) ? $row : []),
            array_slice(array_values($input), 0, 100)
        ) : $rows;
    }
    return $result;
}

function fm_pa_image(array $images, string $name): string {
    if (!in_array($name, ['hero', 'practice-wide', 'practice-detail', 'teacher', 'audience', 'booking'], true)) { return ''; }
    $override = $images[$name] ?? '';
    // Only web media URLs may enter CSS; reject quote/parenthesis injection.
    if (preg_match('~^https?://[^\s\x22\x27()<>]+$~i', $override)) { return $override; }
    return '';
}

function fm_pa_part(string $part, array $defaults, array $settings, string $directory): void {
    if (!isset($defaults[$part])) { return; }
    ['fields' => $fields, 'lists' => $lists, 'images' => $images] = fm_pa_settings($defaults[$part], $settings);
    $uid = wp_unique_id('pulse-atelier-');
    require $directory . '/parts/' . $part . '.php';
    if ($part === 'testimonials') {
        // Inert data, not executable code; no reference runtime is included.
        echo '<template data-pa-quotes>' . esc_html(json_encode($lists['quotes'], JSON_UNESCAPED_UNICODE)) . '</template>';
    }
}

