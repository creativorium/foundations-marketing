<?php
/**
 * The Site Settings contract — the fields a customer can edit, and how anything reads them.
 *
 * WHO OWNS WHAT (customer-runtime.md §5.3):
 *   - This file DECLARES the shared core fields and READS them. It is in the theme
 *     because header and footer parts need values whether or not the editing screen
 *     exists yet.
 *   - The customer runtime plugin WRITES them — the Site Settings screen, the Site Owner
 *     role and the fm_manage_site_settings capability are step 2 and are NOT here.
 *
 * So on this branch the values come from defaults and from whatever the starter content
 * bundle wrote. That is enough to prove the header and footer integration points render
 * real data, which is what this branch is for.
 *
 * INITIAL CONTRACT, NOT A STABLE SCHEMA. Treat this list as the smallest set every design
 * needs. It is expected to grow once the fixture and the first real design show what is
 * missing — see the extension point below. Do not call it stable until then.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/** The single option row every customer setting lives in. */
const FM_SETTINGS_OPTION = 'fm_site_settings';

/**
 * The shared core field set.
 *
 * Add to this ONLY what every customer site needs. A field that one design wants is an
 * extension (see fm_settings_schema()), not a core field — otherwise every salon,
 * nutritionist and tutor site carries an empty "opening hours" box forever.
 *
 * `type` drives how the plugin renders the control in step 2. Keep the vocabulary small:
 * text, textarea, url, email, tel, image, repeater.
 *
 * @return array<string, array<string, mixed>>
 */
function fm_settings_core(): array
{
    return [
        // Identity
        'site_name'    => ['type' => 'text',  'label' => 'Business name',    'default' => ''],
        'logo_id'      => ['type' => 'image', 'label' => 'Logo',             'default' => 0],
        'tagline'      => ['type' => 'text',  'label' => 'Tagline',          'default' => ''],

        // Contact
        'phone'        => ['type' => 'tel',   'label' => 'Phone',            'default' => ''],
        'email'        => ['type' => 'email', 'label' => 'Email',            'default' => ''],
        'address'      => ['type' => 'textarea', 'label' => 'Address',       'default' => ''],

        // Call to action — the one button the header and footer both use
        'cta_label'    => ['type' => 'text',  'label' => 'Button text',      'default' => ''],
        'cta_url'      => ['type' => 'url',   'label' => 'Button link',      'default' => ''],

        // Footer
        'footer_text'  => ['type' => 'textarea', 'label' => 'Footer text',   'default' => ''],

        /*
         * Navigation lives here rather than in Appearance → Menus, because that screen
         * and the Site Editor share the edit_theme_options capability and a customer
         * cannot be given one without the other (customer-runtime.md §5.2).
         *
         * Rows: ['label' => string, 'page' => int|0, 'url' => string, 'children' => []]
         * One level of children. Resolve `page` first; fall back to `url`.
         */
        'nav_primary'  => ['type' => 'repeater', 'label' => 'Main navigation',   'default' => []],
        'nav_footer'   => ['type' => 'repeater', 'label' => 'Footer navigation', 'default' => []],

        // Social — ['network' => string, 'url' => string]
        'social'       => ['type' => 'repeater', 'label' => 'Social links',      'default' => []],
    ];
}

/**
 * The full schema: core, plus this design's extensions.
 *
 * EXTENSION POINT. A template declares its extras in its own template.json:
 *
 *     "settings": {
 *       "opening_hours": { "type": "textarea", "label": "Opening hours" },
 *       "booking_url":   { "type": "url",      "label": "Booking link" }
 *     }
 *
 * The packager reads that and registers them on this filter in the generated plugin, so
 * they appear in Site Settings beside the core fields. Nothing in the base needs editing
 * to add a design's field — if you find yourself editing fm_settings_core() for one
 * design, it belongs in that design's template.json instead.
 *
 * A key collision is resolved in favour of core, deliberately: a design must not redefine
 * `phone` to mean something else, or the shared header stops being shared.
 *
 * @return array<string, array<string, mixed>>
 */
function fm_settings_schema(): array
{
    $core = fm_settings_core();

    /**
     * Filter the per-design Site Settings extensions.
     *
     * @param array<string, array<string, mixed>> $extensions
     * @param array<string, array<string, mixed>> $core
     */
    $extensions = (array) apply_filters('fm_settings_extensions', [], $core);

    // array_merge order matters: core wins a collision.
    return array_merge($extensions, $core);
}

/**
 * Read one setting, falling back to its declared default.
 *
 * Every part and block reads settings through this — never get_option() directly — so
 * that a field absent from the database behaves like an empty field rather than a PHP
 * notice, and so a missing Site Settings screen (as on this branch) is survivable.
 *
 * @return mixed
 */
function fm_setting(string $key, mixed $fallback = null): mixed
{
    static $values = null;

    if ($values === null) {
        $values = (array) get_option(FM_SETTINGS_OPTION, []);
    }

    if (array_key_exists($key, $values) && $values[$key] !== '' && $values[$key] !== []) {
        return $values[$key];
    }

    if ($fallback !== null) {
        return $fallback;
    }

    $schema = fm_settings_schema();

    return $schema[$key]['default'] ?? '';
}

/**
 * Has this setting got a usable value? Use it to decide whether to render a whole
 * element — an empty phone number should render no link at all, not an empty one.
 */
function fm_has_setting(string $key): bool
{
    $value = fm_setting($key);

    return $value !== '' && $value !== 0 && $value !== [];
}

/**
 * Resolve one navigation row to a URL.
 *
 * A row links by PAGE ID first and by URL second. That order is what survives an import:
 * the importer remaps page ids, so a menu built on ids keeps working on a new site, while
 * a menu built on absolute URLs would still point at our dev site
 * (customer-runtime.md §6a step 3).
 *
 * @param array<string, mixed> $row
 */
function fm_nav_url(array $row): string
{
    $page = (int) ($row['page'] ?? 0);

    if ($page > 0) {
        $permalink = get_permalink($page);

        if (is_string($permalink) && $permalink !== '') {
            return $permalink;
        }
    }

    $url = (string) ($row['url'] ?? '');

    return $url !== '' ? $url : '';
}

/**
 * The navigation rows for one location, each with its URL already resolved and any row
 * that resolves to nothing dropped — a menu item pointing nowhere is worse than absent.
 *
 * @return array<int, array{label: string, url: string, current: bool, children: array<int, array{label: string, url: string, current: bool}>}>
 */
function fm_nav(string $location = 'nav_primary'): array
{
    $rows = fm_setting($location, []);

    if (!is_array($rows)) {
        return [];
    }

    $current = is_singular() ? (int) get_queried_object_id() : 0;
    $items   = [];

    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }

        $url = fm_nav_url($row);

        if ($url === '') {
            continue;
        }

        $children = [];

        foreach ((array) ($row['children'] ?? []) as $child) {
            if (!is_array($child)) {
                continue;
            }

            $child_url = fm_nav_url($child);

            if ($child_url === '') {
                continue;
            }

            $children[] = [
                'label'   => (string) ($child['label'] ?? ''),
                'url'     => $child_url,
                'current' => $current > 0 && (int) ($child['page'] ?? 0) === $current,
            ];
        }

        $items[] = [
            'label'    => (string) ($row['label'] ?? ''),
            'url'      => $url,
            'current'  => $current > 0 && (int) ($row['page'] ?? 0) === $current,
            'children' => $children,
        ];
    }

    return $items;
}
