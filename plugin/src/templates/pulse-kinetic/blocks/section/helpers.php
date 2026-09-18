<?php
/** Allowlisted editable-data handling shared by Pulse Kinetic section variants. */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }

function fm_pk_defaults(): array {
    static $defaults;
    return $defaults ??= json_decode((string) file_get_contents(__DIR__ . '/defaults.json'), true);
}

function fm_pk_record(array $schema, array $value): array {
    $result = [];
    foreach ($schema as $key => $default) {
        $input = $value[$key] ?? $default;
        if (is_array($default)) {
            $row_schema = is_array($default[0] ?? null) ? $default[0] : [];
            $result[$key] = is_array($input) ? array_map(
                static fn ($row): array => fm_pk_record($row_schema, is_array($row) ? $row : []),
                array_slice(array_values($input), 0, 100)
            ) : $default;
        } else {
            $result[$key] = is_scalar($input) ? (string) $input : (string) $default;
        }
    }
    return $result;
}

function fm_pk_settings(array $defaults, array $settings): array {
    $result = [];
    foreach (['fields', 'images'] as $group) {
        $result[$group] = fm_pk_record($defaults[$group], is_array($settings[$group] ?? null) ? $settings[$group] : []);
    }
    $result['lists'] = [];
    foreach ($defaults['lists'] as $key => $rows) {
        $input = $settings['lists'][$key] ?? $rows;
        $schema = is_array($rows[0] ?? null) ? $rows[0] : [];
        $result['lists'][$key] = is_array($input) ? array_map(
            static fn ($row): array => fm_pk_record($schema, is_array($row) ? $row : []),
            array_slice(array_values($input), 0, 100)
        ) : $rows;
    }
    return $result;
}

function fm_pk_image(array $images, string $name): string {
    $allowed = ['hero-barre','hero-mat','hero-mobility','studio-sunset','studio-stretch','class-barre','class-mat','class-mobility','coach'];
    if (!in_array($name, $allowed, true)) { return ''; }
    $url = (string) ($images[$name] ?? '');
    return preg_match('~^https?://[^\s\x22\x27()<>]+$~i', $url) ? $url : '';
}

function fm_pk_image_style(array $images, string $name): string {
    $url = fm_pk_image($images, $name);
    return $url === '' ? '' : ' style="background-image:url(' . esc_url($url) . ')"';
}

function fm_pk_img(array $images, string $name, int $width, int $height, string $alt, bool $eager = false): void {
    $url = fm_pk_image($images, $name);
    if ($url === '') { return; }
    printf(
        '<img src="%s" width="%d" height="%d" alt="%s" loading="%s" decoding="async"%s>',
        esc_url($url),
        $width,
        $height,
        esc_attr($alt),
        $eager ? 'eager' : 'lazy',
        $eager ? ' fetchpriority="high"' : ''
    );
}

function fm_pk_part(string $part, array $defaults, array $settings, string $directory): void {
    if (!isset($defaults[$part])) { return; }
    ['fields' => $fields, 'lists' => $lists, 'images' => $images] = fm_pk_settings($defaults[$part], $settings);
    $uid = wp_unique_id('pulse-kinetic-');
    require $directory . '/parts/' . $part . '.php';
}
