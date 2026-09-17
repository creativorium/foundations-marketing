<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }

function fm_site_roles(): void
{
    $caps = ['read','upload_files','edit_posts','edit_published_posts','publish_posts','delete_posts','delete_published_posts','edit_pages','edit_others_pages','edit_published_pages','publish_pages','delete_pages','delete_published_pages','fm_manage_site_settings'];
    $role = get_role('fm_site_owner') ?: add_role('fm_site_owner', 'Site Owner', []);
    foreach ($caps as $cap) { $role->add_cap($cap); }
    foreach (array_keys($role->capabilities) as $cap) { if(!in_array($cap,$caps,true)){$role->remove_cap($cap);} }
    get_role('administrator')?->add_cap('fm_manage_site_settings');
}

/** Used by the customer UI and the project manager; reject unsupported fields. */
function fm_site_sanitize_settings(array $input): array
{
    if (!function_exists('fm_settings_schema')) { throw new RuntimeException('Activate a Foundations customer theme first.'); }
    $out = [];
    foreach (fm_settings_schema() as $key => $spec) {
        $value = $input[$key] ?? ($spec['default'] ?? '');
        if ($key === 'nav_primary' || $key === 'nav_footer') {
            $rows = is_string($value) ? json_decode($value, true) : $value;
            if (!is_array($rows)) { throw new InvalidArgumentException('Navigation must be a list of links.'); }
            $clean = function (array $items, int $depth = 0) use (&$clean): array {
                $result = [];
                foreach (array_slice($items, 0, 30) as $row) {
                    if (!is_array($row)) { continue; }
                    $label = sanitize_text_field((string) ($row['label'] ?? ''));
                    if ($label === '') { continue; }
                    $result[] = ['label'=>$label, 'page'=>absint($row['page'] ?? 0), 'url'=>esc_url_raw((string) ($row['url'] ?? '')), 'children'=>$depth === 0 ? $clean((array) ($row['children'] ?? []), 1) : []];
                }
                return $result;
            };
            $out[$key] = $clean($rows);
        } elseif ($key === 'social') {
            $rows = is_string($value) ? json_decode($value, true) : $value;
            if (!is_array($rows)) { throw new InvalidArgumentException('Social links must be a list.'); }
            $out[$key] = [];
            foreach (array_slice($rows, 0, 20) as $row) {
                if (!is_array($row) || empty($row['url'])) { continue; }
                $out[$key][] = ['network'=>sanitize_text_field((string) ($row['network'] ?? '')), 'url'=>esc_url_raw((string) $row['url'])];
            }
        } elseif (($spec['type'] ?? '') === 'image') {
            $id = absint($value);
            $out[$key] = $id && wp_attachment_is_image($id) ? $id : 0;
        } elseif (($spec['type'] ?? '') === 'url') { $out[$key] = esc_url_raw((string) $value); }
        elseif (($spec['type'] ?? '') === 'email') { $out[$key] = sanitize_email((string) $value); }
        elseif (($spec['type'] ?? '') === 'textarea') { $out[$key] = sanitize_textarea_field((string) $value); }
        else { $out[$key] = sanitize_text_field(is_scalar($value) ? (string) $value : ''); }
    }
    return $out;
}

function fm_site_settings_fields(array $settings): void
{
    if (!function_exists('fm_settings_schema')) { echo '<p>Activate a customer theme to edit its settings.</p>'; return; }
    $page_choices = [];
    $project = absint($_GET['project'] ?? $_GET['design'] ?? 0);
    if (defined('FM_DELIVERY_MANAGER') && $project) {
        $content = (array) get_post_meta($project, '_fm_content', true);
        foreach ($content['pages'] ?? [] as $page) { $page_choices[] = ['id'=>(int)$page,'title'=>get_the_title($page)]; }
    } elseif (!defined('FM_DELIVERY_MANAGER')) {
        foreach (get_pages(['post_status'=>'publish']) as $page) { $page_choices[] = ['id'=>$page->ID,'title'=>$page->post_title]; }
    }
    echo '<table class="form-table" role="presentation">';
    foreach (fm_settings_schema() as $key => $spec) {
        $value = $settings[$key] ?? ($spec['default'] ?? '');
        $id = 'fm-field-' . $key;
        echo '<tr><th><label for="' . esc_attr($id) . '">' . esc_html($spec['label'] ?? $key) . '</label></th><td>';
        if (in_array($key, ['nav_primary','nav_footer','social'], true)) {
            echo '<div class="fm-link-editor" data-pages="'.esc_attr(wp_json_encode($page_choices)).'" data-field="' . esc_attr($key) . '"><input type="hidden" id="' . esc_attr($id) . '" name="settings[' . esc_attr($key) . ']" value="' . esc_attr(wp_json_encode($value ?: [])) . '"><div class="fm-link-rows"></div><button type="button" class="button fm-link-add">Add link</button></div>';
        } elseif (($spec['type'] ?? '') === 'textarea') {
            echo '<textarea class="large-text" rows="3" id="' . esc_attr($id) . '" name="settings[' . esc_attr($key) . ']">' . esc_textarea((string) $value) . '</textarea>';
        } else {
            $type = ['email'=>'email','url'=>'url','tel'=>'tel','image'=>'number'][$spec['type'] ?? ''] ?? 'text';
            echo '<input class="regular-text" type="' . esc_attr($type) . '" id="' . esc_attr($id) . '" name="settings[' . esc_attr($key) . ']" value="' . esc_attr((string) $value) . '">';
            if (($spec['type'] ?? '') === 'image') { echo ' <button type="button" class="button fm-pick-image" data-input="' . esc_attr($id) . '">Choose image</button>'; }
        }
        echo '</td></tr>';
    }
    echo '</table>';
}

if (!defined('FM_DELIVERY_MANAGER')) {
add_action('admin_menu', function (): void {
    add_menu_page('Site Settings','Site Settings','fm_manage_site_settings','fm-site-settings', function (): void {
        if (!current_user_can('fm_manage_site_settings')) { wp_die('Not allowed', '', ['response'=>403]); }
        echo '<div class="wrap"><h1>Site Settings</h1><p>Update your business details and navigation across the website.</p>';
        if (isset($_GET['saved'])) { echo '<div class="notice notice-success"><p>Settings saved.</p></div>'; }
        echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="post"><input type="hidden" name="action" value="fm_site_save">';
        wp_nonce_field('fm_site_save');
        fm_site_settings_fields((array) get_option('fm_site_settings', []));
        submit_button();
        echo '</form></div>';
    }, 'dashicons-admin-home');
});
add_action('admin_post_fm_site_save', function (): void {
    if (!current_user_can('fm_manage_site_settings')) { wp_die('Not allowed', '', ['response'=>403]); }
    check_admin_referer('fm_site_save');
    try {
        $settings = fm_site_sanitize_settings((array) wp_unslash($_POST['settings'] ?? []));
        update_option('fm_site_settings', $settings);
        if ($settings['site_name'] !== '') { update_option('blogname', $settings['site_name']); }
        update_option('blogdescription', $settings['tagline'] ?? '');
    } catch (Throwable $e) { wp_die(esc_html($e->getMessage())); }
    wp_safe_redirect(admin_url('admin.php?page=fm-site-settings&saved=1')); exit;
});
add_action('admin_enqueue_scripts', function (): void {
    if (!in_array($_GET['page'] ?? '', ['fm-site-settings','fm-delivery'], true)) { return; }
    wp_enqueue_media();
    wp_enqueue_script('fm-site-settings-ui', plugins_url('../assets/settings.js', __FILE__), ['media-editor'], '0.1.0', true);
});

}
