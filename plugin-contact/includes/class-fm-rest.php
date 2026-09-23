<?php
if (!defined('ABSPATH')) exit;
require_once __DIR__ . '/discovery-validation.php';

class FMCD_REST {
  public function __construct() {
    add_action('rest_api_init', function(){
      register_rest_route('fmcd/v1', '/submit', [
        'methods'  => 'POST',
        'callback' => function($request) { return $this->once($request, 'submit'); },
        'permission_callback' => '__return_true'
      ]);
      register_rest_route('fmcd/v1', '/availability', [
        'methods'  => 'GET',
        'callback' => [$this, 'availability'],
        'permission_callback' => '__return_true'
      ]);
      register_rest_route('fmcd/v1', '/book', [
        'methods'  => 'POST',
        'callback' => function($request) { return $this->once($request, 'book'); },
        'permission_callback' => '__return_true'
      ]);
      // Admin-only webhook test
      register_rest_route('fmcd/v1', '/testwebhook', [
        'methods'  => 'POST',
        'callback' => [$this, 'test_webhook'],
        'permission_callback' => function(){ return current_user_can('manage_options'); }
      ]);
    });
  }

  /* ---------- helpers: HTML summary ---------- */
  /** Reuse a completed response when a browser retries after a lost connection. */
  private function once(\WP_REST_Request $request, string $method) {
    $token = (string) $request->get_param('request_id');
    if (!preg_match('/^[a-zA-Z0-9-]{20,80}$/', $token)) {
      return new \WP_REST_Response(['ok'=>false,'error'=>'Please reload the form before submitting.'], 400);
    }
    $key = 'fmcd_'.hash('sha256', $method.'|'.$token);
    $cached = get_transient($key);
    if ($cached !== false) { return $cached; }
    if (!add_option($key.'_lock', time(), '', false)) {
      return new \WP_REST_Response(['ok'=>false,'error'=>'Your request is still being processed. Please wait before retrying.'], 409);
    }
    try {
      $result = $this->$method($request);
      if (is_array($result) && !empty($result['ok'])) { set_transient($key, $result, DAY_IN_SECONDS); }
      return $result;
    } finally { delete_option($key.'_lock'); }
  }

  private function html_row($label, $value){
    if ($value === '' || $value === null) return '';
    if (is_array($value)) $value = implode(', ', array_map('sanitize_text_field', $value));
    $label = esc_html($label);
    $value = wp_kses_post($value);
    return "<tr><th align='left' style='padding:6px 10px;border-bottom:1px solid #eee;width:220px;'>$label</th>
              <td style='padding:6px 10px;border-bottom:1px solid #eee;'>$value</td></tr>";
  }

  private function render_html_summary($type, $data, $attachments_urls = []){
    $title = ($type === 'contact') ? 'New Contact Enquiry' : 'New Discovery Submission';
    $rows  = '';

    if ($type === 'contact'){
      $rows .= $this->html_row('Name', $data['full_name'] ?? '');
      $rows .= $this->html_row('Email', $data['email'] ?? '');
      $rows .= $this->html_row('Phone', $data['phone'] ?? '');
      $rows .= $this->html_row('Social', $data['social'] ?? '');
      $rows .= $this->html_row('Website', $data['website'] ?? '');
      $rows .= $this->html_row('Plan', $data['services_plan'] ?? '');
      $rows .= $this->html_row('About', nl2br(esc_html($data['about'] ?? '')));
      $rows .= $this->html_row('Stage', nl2br(esc_html($data['stage_details'] ?? '')));
      $rows .= $this->html_row('Budget', $data['budget'] ?? '');
      $rows .= $this->html_row('Heard From', $data['ref_source'] ?? '');
      $rows .= $this->html_row('Ideal Finish Date', $data['deadline'] ?? '');

      // Appointment time from hidden appt_iso (if provided in submit)
      if (!empty($data['appt_iso'])) {
        try {
          $tz = new \DateTimeZone('Europe/London');
          $s = new \DateTime($data['appt_iso']);
          $s->setTimezone($tz);
          $e = clone $s; $e->modify('+1 hour');
          $whenHuman = $s->format('D, j M Y · H:i') . '–' . $e->format('H:i') . ' (Europe/London)';
          $rows .= $this->html_row('Appointment', $whenHuman);

          $gcalDay = 'https://calendar.google.com/calendar/u/0/r/day/' . $s->format('Y/m/d');
          $rows .= $this->html_row('Google Calendar', "<a href='".esc_url($gcalDay)."' target='_blank' rel='noopener'>Open Google Calendar (day view)</a>");
        } catch (\Throwable $e) {}
      }

      if (!empty($data['appt_url'])) {
        $rows .= $this->html_row('Calendar Event', "<a href='".esc_url($data['appt_url'])."'>Open in Google Calendar</a>");
      }
    }

    if ($type === 'discovery'){
      $rows .= $this->html_row('Full name', $data['full_name'] ?? '');
      $rows .= $this->html_row('Business Email', $data['business_email'] ?? '');
      $rows .= $this->html_row('Business Name', $data['business_name'] ?? '');
      $rows .= $this->html_row('Profession', nl2br(esc_html($data['profession_services'] ?? '')));
      $rows .= $this->html_row('Experience Length', $data['experience_length'] ?? '');

      if (!empty($data['services'])){
        $rows .= $this->html_row('Services', $this->render_services_html($data['services']));
      }
      $rows .= $this->html_row('Ideal Client', nl2br(esc_html($data['ideal_client'] ?? '')));
      $rows .= $this->html_row('How clients work with you', nl2br(esc_html($data['client_process'] ?? '')));
      $rows .= $this->html_row('Address', $data['location_address'] ?? '');
      $rows .= $this->html_row('State / Region', $data['location_state'] ?? '');
      $rows .= $this->html_row('Postal / Zip', $data['location_zip'] ?? '');
      $rows .= $this->html_row('Serving Mode', ($data['location_mode'] ?? '') === 'online' ? 'Online only' : 'Physical');
      $rows .= $this->html_row('Location / Address', nl2br(esc_html($data['location'] ?? '')));

      $rows .= $this->html_row('Template', $data['template_choice'] ?? '');
      $rows .= $this->html_row('Own Colours', $data['own_colors'] ?? '');
      $rows .= $this->html_row('Colours Provided', $data['colors'] ?? []);
      $rows .= $this->html_row('Own Fonts', $data['own_fonts'] ?? '');
      $rows .= $this->html_row('Fonts Provided', $data['fonts'] ?? []);

      $rows .= $this->html_row('Template images', $this->format_have_images_label($data['have_images'] ?? ''));
      $rows .= $this->html_row('Domain access status', $this->format_access_status($data['domain_status'] ?? ''));
      $rows .= $this->html_row('Domain / website address', esc_html($data['domain_url'] ?? ''));
      $rows .= $this->html_row('Domain provider', $data['domain_provider'] ?? '');
      $rows .= $this->html_row('Domain username', $data['domain_username'] ?? '');
      $rows .= $this->html_row('Hosting access status', $this->format_access_status($data['hosting_status'] ?? ''));
      $rows .= $this->html_row('Hosting provider', $data['hosting_provider'] ?? '');
      $rows .= $this->html_row('Hosting username', $data['hosting_username'] ?? '');
      $rows .= $this->html_row('Business email access status', $this->format_access_status($data['email_status'] ?? ''));
      $rows .= $this->html_row('Business email platform', $data['email_platform'] ?? '');
      $rows .= $this->html_row('Business email username', $data['email_username'] ?? '');
      $rows .= $this->html_row('Widget / calendar link', esc_html($data['booking_url'] ?? ''));
      $rows .= $this->html_row('Client reviews / testimonials', nl2br(esc_html($data['review_text'] ?? '')));
      $rows .= $this->html_row('Social media links', nl2br(esc_html($data['social_links'] ?? '')));
      $rows .= $this->html_row('Booking access status', $this->format_access_status($data['booking_status'] ?? ''));
      $rows .= $this->html_row('Booking platform', $data['booking_platform'] ?? '');
      $rows .= $this->html_row('Booking username', $data['booking_username'] ?? '');
      $rows .= $this->html_row('Assets on hand', $data['assets_have'] ?? []);

      if (!empty($data['image_drive_link'])){
        $drive = esc_url($data['image_drive_link']);
        $rows .= $this->html_row('Image Drive/Dropbox', "<a href='$drive' target='_blank' rel='noopener'>$drive</a>");
      }
      if (!empty($data['image_links'])){
        $items = [];
        foreach ((array) $data['image_links'] as $idx => $link){
          if (!$link) continue;
          $url = esc_url($link);
          $label = 'Image '.($idx+1);
          $items[] = "<li><a href='$url' target='_blank' rel='noopener'>$label</a></li>";
        }
        if ($items) $rows .= $this->html_row('Image Links', "<ul style='margin:6px 0 0 18px;'>".implode('', $items)."</ul>");
      }

      if (!empty($data['content_link'])){
        $cl = esc_url($data['content_link']);
        $rows .= $this->html_row('Website & content link', "<a href='$cl' target='_blank' rel='noopener'>$cl</a>");
      }
      if (!empty($data['review_link'])){
        $rl = esc_url($data['review_link']);
        $rows .= $this->html_row('Review link', "<a href='$rl' target='_blank' rel='noopener'>$rl</a>");
      }
    }

    if (!empty($attachments_urls)){
      $links = [];
      foreach ($attachments_urls as $entry){
        if (is_array($entry)){
          $url = !empty($entry['url']) ? esc_url($entry['url']) : '';
          if (!$url) continue;
          $label = esc_html($entry['label'] ?? $entry['url']);
          $links[] = '<li><a href="'.$url.'" target="_blank" rel="noopener">'.$label.'</a></li>';
        } else {
          $url = esc_url($entry);
          if (!$url) continue;
          $links[] = '<li><a href="'.$url.'" target="_blank" rel="noopener">'.$url.'</a></li>';
        }
      }
      $rows .= "<tr><th align='left' style='padding:6px 10px;border-bottom:1px solid #eee;'>Uploaded Files</th><td style='padding:6px 10px;border-bottom:1px solid #eee;'><ul style='margin:6px 0 0 16px'>".implode('', $links)."</ul></td></tr>";
    }

    $css = "font:14px/1.45 -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Inter,Arial; color:#222";
    return "<div style='$css'><h2 style='margin:0 0 12px'>$title</h2><table cellspacing='0' cellpadding='0' style='border-collapse:collapse;width:100%'>$rows</table></div>";
  }

  private function clean($v){
    return is_string($v) ? trim(wp_unslash($v)) : $v;
  }

  private function sanitize_text_array($value){
    if (empty($value)) return [];
    if (!is_array($value)) $value = [$value];
    $out = [];
    foreach ($value as $v){
      $clean = $this->clean($v);
      if ($clean === '' || $clean === null) continue;
      $out[] = sanitize_text_field($clean);
    }
    return $out;
  }

  private function sanitize_textarea_array($value){
    if (empty($value)) return [];
    if (!is_array($value)) $value = [$value];
    $out = [];
    foreach ($value as $v){
      $clean = $this->clean($v);
      if ($clean === '' || $clean === null) continue;
      $out[] = sanitize_textarea_field($clean);
    }
    return $out;
  }

  private function sanitize_url_array($value){
    if (empty($value)) return [];
    if (!is_array($value)) $value = [$value];
    $out = [];
    foreach ($value as $v){
      $clean = esc_url_raw($this->clean($v));
      if (!$clean) continue;
      $out[] = $clean;
    }
    return $out;
  }

  private function sanitize_status($value){
    $status = sanitize_text_field($this->clean($value));
    return in_array($status, ['have','need','not_applicable'], true) ? $status : '';
  }

  private function discovery_assets(){
    $defaults = [
      'logo'           => 'Logo',
      'hosting'        => 'Hosting access',
      'domain'         => 'Domain access',
      'brand'          => 'Brand colours / palette',
      'photo_self'     => 'Photos of yourself',
      'photo_space'    => 'Photos of your space',
      'business_email' => 'Business email details',
      'social'         => 'Social media links',
      'testimonials'   => 'Testimonials',
      'booking'        => 'Booking system setup',
    ];
    $filtered = apply_filters('fmcd/discovery_assets', $defaults);
    if (!is_array($filtered)) return $defaults;
    // Normalise: ensure associative slug => label
    $normalized = [];
    foreach ($filtered as $key => $label){
      if (is_array($label) && isset($label['label'])){
        $normalized[$key] = $label['label'];
      } else {
        $normalized[$key] = is_int($key) ? sanitize_text_field($label) : $label;
      }
    }
    return $normalized;
  }

  private function render_services_html($services){
    if (empty($services) || !is_array($services)) return '';
    $items = [];
    foreach ($services as $svc){
      $title = isset($svc['title']) ? esc_html($svc['title']) : '';
      $price = isset($svc['price']) ? esc_html($svc['price']) : '';
      $desc  = isset($svc['description']) ? esc_html($svc['description']) : '';
      if (!$title && !$desc && !$price) continue;
      $line  = $title ? "<strong>$title</strong>" : '';
      if ($price) $line .= $line ? " <span style='color:#555;'>($price)</span>" : "<span style='color:#555;'>$price</span>";
      if ($desc) $line .= "<div style='margin-top:4px;'>$desc</div>";
      $items[] = "<li style='margin-bottom:6px;'>$line</li>";
    }
    if (!$items) return '';
    return "<ul style='margin:6px 0 0 18px; padding:0;'>".implode('', $items)."</ul>";
  }

  private function format_have_images_label($value){
    switch ($value) {
      case 'yes':
        return 'Yes - images already prepared';
      case 'need_help':
        return 'No - I need help choosing';
      case 'freepik':
        return 'No - I want to choose from Freepik stock images';
      case 'no':
        return 'No - I need help choosing';
      default:
        return $value;
    }
  }

  private function format_access_status($value){
    switch ($value) {
      case 'have':
        return 'Login details provided';
      case 'need':
        return 'Needs setup';
      case 'not_applicable':
        return 'No booking widget needed';
      default:
        return '';
    }
  }

  /** Handle form submissions (contact or discovery) */
  public function submit(\WP_REST_Request $r) {
    error_log('[FMCD] submit start '.current_time('mysql').' type='.$r->get_param('type'));
    $type = $this->clean($r->get_param('type')); // contact|discovery
    if (!$type) return new \WP_REST_Response(['ok'=>false,'error'=>'Missing type'], 400);

    // Collect all params first
    $data = $r->get_params();
    foreach (array_keys($data) as $key) { if (str_contains(strtolower((string)$key), 'password')) { unset($data[$key]); } }
    if (!in_array($type, ['contact','discovery'], true)) { return new \WP_REST_Response(['ok'=>false,'error'=>'Unknown form.'],400); }
    if ($type === 'contact' && (empty($data['full_name']) || !is_email($data['email'] ?? ''))) { return new \WP_REST_Response(['ok'=>false,'error'=>'Please enter your name and a valid email address.'],400); }
    error_log('[FMCD] payload keys: '.implode(',', array_keys($data)));
    $errors = [];

    // Normalize discovery email field for integrations and emails
    if ($type === 'discovery') {
      if (empty($data['email']) && !empty($data['business_email'])) {
        $data['email'] = $this->clean($data['business_email']);
      }
    }

    // Normalize multi-selects / arrays
    foreach (['colors','fonts','assets_have','image_links'] as $k){
      if (isset($data[$k]) && !is_array($data[$k])) $data[$k] = [$data[$k]];
    }

    if ($type === 'discovery') {
      $data['service_title']       = isset($data['service_title'])       ? (array) $data['service_title']       : [];
      $data['service_description'] = isset($data['service_description']) ? (array) $data['service_description'] : [];
      $data['service_price']       = isset($data['service_price'])       ? (array) $data['service_price']       : [];

      $services = [];
      $maxRows  = max(count($data['service_title']), count($data['service_description']), count($data['service_price']));
      for ($i=0; $i < $maxRows; $i++){
        $title = sanitize_text_field($this->clean($data['service_title'][$i] ?? ''));
        $desc  = sanitize_textarea_field($this->clean($data['service_description'][$i] ?? ''));
        $price = sanitize_text_field($this->clean($data['service_price'][$i] ?? ''));
        if ($title === '' && $desc === '' && $price === '') continue;
        $services[] = [
          'title'       => $title,
          'description' => $desc,
          'price'       => $price,
        ];
      }
      $data['services'] = $services;

      $data['colors']      = $this->sanitize_text_array($data['colors'] ?? []);
      $data['fonts']       = $this->sanitize_text_array($data['fonts'] ?? []);
      $data['assets_have'] = $this->sanitize_text_array($data['assets_have'] ?? []);
      $data['image_links'] = $this->sanitize_url_array($data['image_links'] ?? []);


      $data['profession_services'] = sanitize_textarea_field($data['profession_services'] ?? '');
      $data['ideal_client']        = sanitize_textarea_field($data['ideal_client'] ?? '');
      $data['client_process']      = sanitize_textarea_field($data['client_process'] ?? '');
      $data['experience_length']   = sanitize_text_field($data['experience_length'] ?? '');
      $data['own_colors']          = sanitize_text_field($data['own_colors'] ?? '');
      $data['own_fonts']           = sanitize_text_field($data['own_fonts'] ?? '');
      $data['have_images']         = sanitize_text_field($data['have_images'] ?? '');
      $allowed_images              = ['yes','freepik'];
      if (!in_array($data['have_images'], $allowed_images, true)) {
        $data['have_images'] = '';
      }
      $data['location_address']    = sanitize_text_field($data['location_address'] ?? '');
      $data['location_state']      = sanitize_text_field($data['location_state'] ?? '');
      $data['location_zip']        = sanitize_text_field($data['location_zip'] ?? '');
      $data['location_mode']       = sanitize_text_field($data['location_mode'] ?? 'physical');
      $data['location_mode']       = ($data['location_mode'] === 'online') ? 'online' : 'physical';
      $data['image_drive_link']    = esc_url_raw($data['image_drive_link'] ?? '');
      $data['content_link']        = esc_url_raw($data['content_link'] ?? '');
      $data['review_link']         = esc_url_raw($data['review_link'] ?? '');
      $data['domain_url'] = esc_url_raw($data['domain_url'] ?? '');
      $data['booking_url'] = esc_url_raw($data['booking_url'] ?? '');
      $data['review_text'] = sanitize_textarea_field($data['review_text'] ?? '');
      $data['social_links'] = sanitize_textarea_field($data['social_links'] ?? '');

      $data['domain_status']       = $this->sanitize_status($data['domain_status'] ?? 'have');
      if ($data['domain_status'] === '') $data['domain_status'] = 'have';
      $data['hosting_status']      = $this->sanitize_status($data['hosting_status'] ?? 'have');
      if ($data['hosting_status'] === '') $data['hosting_status'] = 'have';
      $data['email_status']        = $this->sanitize_status($data['email_status'] ?? '');
      $data['booking_status']      = $this->sanitize_status($data['booking_status'] ?? '');

      $data['domain_provider']     = sanitize_text_field($data['domain_provider'] ?? '');
      $data['domain_username']     = sanitize_text_field($data['domain_username'] ?? '');
      $data['domain_password']     = sanitize_text_field($data['domain_password'] ?? '');
      $data['hosting_provider']    = sanitize_text_field($data['hosting_provider'] ?? '');
      $data['hosting_username']    = sanitize_text_field($data['hosting_username'] ?? '');
      $data['hosting_password']    = sanitize_text_field($data['hosting_password'] ?? '');
      $data['email_platform']      = sanitize_text_field($data['email_platform'] ?? '');
      $data['email_username']      = sanitize_text_field($data['email_username'] ?? '');
      $data['email_password']      = sanitize_text_field($data['email_password'] ?? '');
      $data['booking_platform']    = sanitize_text_field($data['booking_platform'] ?? '');
      $data['booking_username']    = sanitize_text_field($data['booking_username'] ?? '');
      $data['booking_password']    = sanitize_text_field($data['booking_password'] ?? '');

      $errors = array_merge($errors, fmcd_discovery_setup_errors($data));
      if ($data['booking_status'] === 'not_applicable') {
        $data['booking_url'] = $data['booking_platform'] = $data['booking_username'] = '';
      }
      $hasContentUpload = !empty(array_filter((array) ($_FILES['content_file']['name'] ?? [])));
      if ($data['content_link'] === '' && !$hasContentUpload) {
        $errors[] = 'Please provide your website copy as a document upload or a shared link.';
      }
      if ($data['content_link'] !== '' && !filter_var($data['content_link'], FILTER_VALIDATE_URL)) {
        $errors[] = 'Please provide a valid website copy link.';
      }

      $locationParts = array_filter([
        $data['location_address'] ?? '',
        $data['location_state'] ?? '',
        $data['location_zip'] ?? ''
      ], function($v){ return $v !== ''; });
      if ($data['location_mode'] === 'online'){
        $data['location'] = 'Online only';
      } else {
        $data['location'] = implode(', ', $locationParts);
      }
    }

    // Handle file uploads (Media Library) and collect URLs with labels
    $uploaded_urls = [];
    $upload_items  = [];
    // Preserve field validation errors when processing uploads.
    $max_size      = 20 * 1024 * 1024; // 20MB

    if ($type === 'discovery') {
      $assets = $this->discovery_assets();
      if (!empty($data['assets_have'])){
        foreach ($assets as $slug => $label){
          if (in_array($label, (array) $data['assets_have'], true)){
            $names = $_FILES['asset_uploads']['name'][$slug] ?? [];
            if (!is_array($names)) $names = [$names];
            $hasUpload = false;
            foreach ($names as $nm){
              if (!empty($nm)){ $hasUpload = true; break; }
            }
            if (!$hasUpload){
              $label_text = isset($assets[$slug]) ? sanitize_text_field($assets[$slug]) : sanitize_text_field($slug);
              $errors[] = sprintf('Please upload the file for "%s".', $label_text);
            }
          }
        }
      }
      if (!empty($_FILES['asset_uploads']['name']) && is_array($_FILES['asset_uploads']['name'])){
        foreach ($_FILES['asset_uploads']['name'] as $slug => $names){
          $names = is_array($names) ? $names : [$names];
          $types = $_FILES['asset_uploads']['type'][$slug] ?? [];
          if (!is_array($types)) $types = [$types];
          $tmp   = $_FILES['asset_uploads']['tmp_name'][$slug] ?? [];
          if (!is_array($tmp)) $tmp = [$tmp];
          $errs  = $_FILES['asset_uploads']['error'][$slug] ?? [];
          if (!is_array($errs)) $errs = [$errs];
          $sizes = $_FILES['asset_uploads']['size'][$slug] ?? [];
          if (!is_array($sizes)) $sizes = [$sizes];

          foreach ($names as $idx => $name){
            if (!$name) continue;
            $size = $sizes[$idx] ?? 0;
            $label = isset($assets[$slug]) ? sanitize_text_field($assets[$slug]) : sanitize_text_field($slug);
            if (!empty($size) && $size > $max_size){
              $errors[] = sprintf('%s (file %d) exceeds the 20MB limit.', sanitize_text_field($label), $idx + 1);
              continue;
            }
            $key = 'fmcd_asset_'.$slug.'_'.$idx;
            $_FILES[$key] = [
              'name'     => $name,
              'type'     => $types[$idx] ?? '',
              'tmp_name' => $tmp[$idx] ?? '',
              'error'    => $errs[$idx] ?? 0,
              'size'     => $size,
            ];
            $upload_items[] = ['key' => $key, 'label' => $label.' (file '.($idx + 1).')'];
          }
        }
      }

      foreach (['content_file' => 'Website content document', 'review_file' => 'Review file'] as $field => $label){
        if (empty($_FILES[$field]['name'])) continue;

        $names = $_FILES[$field]['name'];
        $types = $_FILES[$field]['type'];
        $tmp   = $_FILES[$field]['tmp_name'];
        $errs  = $_FILES[$field]['error'];
        $sizes = $_FILES[$field]['size'];

        if (!is_array($names)){
          $names = [$names];
          $types = [$types];
          $tmp   = [$tmp];
          $errs  = [$errs];
          $sizes = [$sizes];
        }

        foreach ($names as $idx => $name){
          if (!$name) continue;
          $size = $sizes[$idx] ?? 0;
          if (!empty($size) && $size > $max_size){
            $errors[] = sprintf('%s exceeds the 20MB limit.', $label);
            continue;
          }

          if (!in_array(strtolower(pathinfo($name, PATHINFO_EXTENSION)), ['pdf', 'doc', 'docx', 'txt', 'odt', 'rtf'], true)) {
            $errors[] = $label . ' must be a text document (PDF, Word, TXT, ODT or RTF).';
            continue;
          }
          $key = 'fmcd_'.$field.'_'.$idx;
          $_FILES[$key] = [
            'name'     => $name,
            'type'     => $types[$idx] ?? '',
            'tmp_name' => $tmp[$idx] ?? '',
            'error'    => $errs[$idx] ?? 0,
            'size'     => $size,
          ];
          $upload_items[] = ['key' => $key, 'label' => $label];
        }
      }
    }

    if (!empty($errors)){
      return new \WP_REST_Response(['ok'=>false, 'error'=>implode(' ', $errors)], 400);
    }

    if (!empty($upload_items)){
      require_once ABSPATH.'wp-admin/includes/file.php';
      require_once ABSPATH.'wp-admin/includes/media.php';
      require_once ABSPATH.'wp-admin/includes/image.php';

      foreach ($upload_items as $item){
        $att_id = media_handle_upload($item['key'], 0);
        if (!is_wp_error($att_id)){
          $uploaded_urls[] = [
            'label' => $item['label'],
            'url'   => wp_get_attachment_url($att_id),
          ];
        }
        unset($_FILES[$item['key']]);
      }
    }

    // Store entry (CPT must exist via FMCD_CPT)
    $name  = sanitize_text_field($data['full_name'] ?? '');
    $title = sprintf('[%s] %s - %s', strtoupper($type), $name ?: 'No name', current_time('mysql'));

    error_log('[FMCD] inserting post');
    $post_id = wp_insert_post([
      'post_type'   => 'fmcd_entry',
      'post_status' => 'publish',
      'post_title'  => $title
    ], true);

    if (!$post_id || is_wp_error($post_id)) {
      return new \WP_REST_Response(['ok'=>false,'error'=>'We could not save your enquiry. Please try again.'], 500);
    }
    if ($post_id && !is_wp_error($post_id)){
      update_post_meta($post_id, '_fmcd_type', $type);
      update_post_meta($post_id, '_fmcd_payload', $data);
      if (!empty($uploaded_urls)) update_post_meta($post_id, '_fmcd_files', $uploaded_urls);
      else delete_post_meta($post_id, '_fmcd_files');

      if ($type === 'discovery'){
        if (!empty($data['image_drive_link'])) update_post_meta($post_id, '_fmcd_image_drive_link', esc_url_raw($data['image_drive_link']));
        else delete_post_meta($post_id, '_fmcd_image_drive_link');

        if (!empty($data['content_link'])) update_post_meta($post_id, '_fmcd_content_link', esc_url_raw($data['content_link']));
        else delete_post_meta($post_id, '_fmcd_content_link');

        if (!empty($data['review_link'])) update_post_meta($post_id, '_fmcd_review_link', esc_url_raw($data['review_link']));
        else delete_post_meta($post_id, '_fmcd_review_link');
      }

      error_log('[FMCD] post inserted ID '.$post_id);
    }

    // Build HTML email (pretty table)
    $html = $this->render_html_summary($type, $data, $uploaded_urls);
    $subject = ($type === 'contact') ? 'New Contact Enquiry' : 'New Discovery Submission';
    FMCD_Mail::send($subject, $html); // admin + cc handled inside FMCD_Mail
    error_log('[FMCD] mail sent subject '.$subject);

    // Integrations (non-blocking)
    if (class_exists('FMCD_Integrations')) {
      try {
        wp_schedule_single_event(time() + 5, 'fmcd_mailchimp_sync', [$type, $data]);
      } catch (\Throwable $e) {
        error_log('[FMCD] MC sync schedule exception: '.$e->getMessage());
      }
    }

    error_log('[FMCD] submit end '.$post_id);
    return ['ok'=>true, 'id'=>$post_id];
  }

  /** Build availability for 3–8 PM Europe/London (1h slots), optionally filtered by external endpoint */
  public function availability(\WP_REST_Request $r) {
    $date     = $this->clean($r->get_param('date')); // YYYY-MM-DD
    $endpoint = get_option('fmcd_calendar_endpoint', '');
    $email    = get_option('fmcd_calendar_email', 'info@foundationsmarketing.co.uk');

    $tz = new \DateTimeZone('Europe/London');
    $d  = \DateTime::createFromFormat('!Y-m-d', $date, $tz);
    if (!$d || $d->format('Y-m-d')!==$date || $d < new \DateTime('today',$tz)) return ['ok'=>false, 'error'=>'Choose today or a future date.'];
    if (!$endpoint) return ['ok'=>false,'error'=>'Appointments need confirmation from our team.'];

    $slots = [];
    for ($h=15; $h<20; $h++) {
      $start = clone $d; $start->setTime($h, 0, 0);
      $end   = clone $d; $end->setTime($h+1, 0, 0);
      $slots[] = [
        'start'     => $start->format(\DateTime::ATOM),
        'end'       => $end->format(\DateTime::ATOM),
        'available' => false,
      ];
    }

    if ($endpoint) {
      $url = add_query_arg([
        'action' => 'availability',
        'email'  => $email,
        'date'   => $date
      ], $endpoint);

      $res = wp_remote_get($url, ['timeout'=>12]);
      if (!is_wp_error($res) && wp_remote_retrieve_response_code($res) === 200) {
        $data = json_decode(wp_remote_retrieve_body($res), true);
        if (!empty($data['ok']) && isset($data['slots']) && is_array($data['slots'])) {
          $byStart = [];
          foreach ($data['slots'] as $s) {
            if (!empty($s['start'])) $byStart[$s['start']] = $s;
          }
          foreach ($slots as &$local) {
            if (isset($byStart[$local['start']])) {
              $local['available'] = !empty($byStart[$local['start']]['available']) && strtotime($local['start']) > time();
            }
          }
          unset($local);
        } else { return ['ok'=>false,'error'=>'Calendar availability could not be verified.']; }
      } else { return ['ok'=>false,'error'=>'Calendar availability could not be verified.']; }
    }

    return ['ok'=>true, 'slots'=>$slots, 'tz'=>'Europe/London'];
  }

  /** Book a selected slot (passes through to external endpoint if configured) */
  public function book(\WP_REST_Request $r) {
    $name     = $this->clean($r->get_param('full_name'));
    $email    = $this->clean($r->get_param('email'));
    $start    = $this->clean($r->get_param('startIso'));   // may be empty
    $end      = $this->clean($r->get_param('endIso'));     // may be empty
    $phone    = $this->clean($r->get_param('phone'));
    $noBooking= $this->clean($r->get_param('noBooking'));

    // Extra contact fields (for the report email)
    $social   = $this->clean($r->get_param('social'));
    $website  = $this->clean($r->get_param('website'));
    $plan     = $this->clean($r->get_param('services_plan'));
    $about    = $this->clean($r->get_param('about'));
    $stage    = $this->clean($r->get_param('stage_details'));
    $budget   = $this->clean($r->get_param('budget'));
    $ref      = $this->clean($r->get_param('ref_source'));
    $deadline = $this->clean($r->get_param('deadline'));

    $endpoint = get_option('fmcd_calendar_endpoint', '');
    $calEmail = get_option('fmcd_calendar_email', 'info@foundationsmarketing.co.uk');

    if (!$name || !is_email($email)) {
      return new \WP_REST_Response(['ok'=>false,'error'=>'Missing fields'], 400);
    }

    if ($noBooking) { return ['ok'=>true,'booked'=>false]; }
    if (!$start || !$end || !strtotime($start) || strtotime($start)<=time() || strtotime($end)-strtotime($start)!==3600) {
      return new \WP_REST_Response(['ok'=>false,'error'=>'Choose a valid appointment time.'], 400);
    }
    if (!$endpoint) {
      return new \WP_REST_Response(['ok'=>false,'error'=>'Your enquiry is saved. Appointments need confirmation from our team.'], 503);
    }
    // Store a booking intent; it is not a confirmed calendar event.
    $id = wp_insert_post([
      'post_type'=>'fmcd_entry','post_status'=>'publish',
      'post_title'=>"[BOOKING] $name - ". ($start ?: current_time('mysql'))
    ], true);
    if (is_wp_error($id) || !$id) { return new \WP_REST_Response(['ok'=>false,'error'=>'Could not save appointment request.'], 500); }
    foreach ([
      'booking_email'=>$email, 'booking_phone'=>$phone,
      'booking_start'=>$start, 'booking_end'=>$end,
      'social'=>$social, 'website'=>$website, 'services_plan'=>$plan,
      'about'=>$about, 'stage_details'=>$stage, 'budget'=>$budget,
      'ref_source'=>$ref, 'deadline'=>$deadline
    ] as $k=>$v){ update_post_meta($id, $k, $v); }

    // If there's a calendar endpoint and we have a slot, try to book
    $ok = true; $err = null; $eventOk = false;
    if ($endpoint && !$noBooking && $start && $end) {
      $bodyArr = [
        'action'        => 'book',
        'calendarEmail' => $calEmail,
        'payload'       => [
          'name'=>$name,'email'=>$email,'phone'=>$phone,
          'startIso'=>$start,'endIso'=>$end
        ]
      ];
      $res = wp_remote_post($endpoint, [
        'timeout'=>12,
        'headers'=>['Content-Type'=>'application/json'],
        'body'=> wp_json_encode($bodyArr)
      ]);
      $optimistic = false;

      if (is_wp_error($res)) {
        $ok  = $optimistic; $err = $res->get_error_message();
      } else {
        $code = wp_remote_retrieve_response_code($res);
        $body = wp_remote_retrieve_body($res);
        if ($code !== 200) { $ok = $optimistic; $err = 'Calendar endpoint error (HTTP '.$code.'): '.substr($body,0,200); }
        else {
          $j = json_decode($body, true);
          $eventOk = ($j['ok'] ?? false) === true;
          if (!$eventOk) { $ok = $optimistic; $err = isset($j['error']) ? $j['error'] : 'Calendar booking failed (body: '.substr($body,0,200).')'; }
        }
      }
    }

    // Build admin/cc report email (single email with all details)
    $tz = new \DateTimeZone('Europe/London');
    $whenHuman = 'No appointment selected';
    $gcalTemplate = '';
    $gcalDay = '';

    if ($start && $end) {
      try {
        $s = new \DateTime($start); $e = new \DateTime($end);
        $s->setTimezone($tz); $e->setTimezone($tz);
        $whenHuman = $s->format('D, j M Y · H:i') . '–' . $e->format('H:i') . ' (Europe/London)';

        // Google Calendar “Add/View” link
        $title = rawurlencode('Discovery Call: '.$name);
        $dates = $s->format('Ymd\THis\Z') . '/' . $e->format('Ymd\THis\Z'); // ISO basic UTC
        $details = rawurlencode("Lead: $name\nEmail: $email\nPhone: $phone\n\n".$about);
        $gcalTemplate = 'https://calendar.google.com/calendar/render'
          . '?action=TEMPLATE'
          . '&text=' . $title
          . '&dates=' . $dates
          . '&details=' . $details
          . '&add=' . rawurlencode($email)
          . '&ctz=Europe/London';
        $gcalDay = 'https://calendar.google.com/calendar/u/0/r/day/' . $s->format('Y/m/d');
      } catch (\Throwable $e) {}
    }

    ob_start(); ?>
      <h2>New Enquiry</h2>
      <table cellspacing="0" cellpadding="6" style="border-collapse:collapse">
        <tr><td><b>Name</b></td><td><?php echo esc_html($name); ?></td></tr>
        <tr><td><b>Email</b></td><td><?php echo esc_html($email); ?></td></tr>
        <?php if($phone): ?><tr><td><b>Phone</b></td><td><?php echo esc_html($phone); ?></td></tr><?php endif; ?>
        <?php if($social): ?><tr><td><b>Social</b></td><td><?php echo esc_html($social); ?></td></tr><?php endif; ?>
        <?php if($website): ?><tr><td><b>Website</b></td><td><?php echo esc_html($website); ?></td></tr><?php endif; ?>
        <?php if($plan): ?><tr><td><b>Plan</b></td><td><?php echo esc_html($plan); ?></td></tr><?php endif; ?>
        <?php if($budget): ?><tr><td><b>Budget</b></td><td><?php echo esc_html($budget); ?></td></tr><?php endif; ?>
        <?php if($ref): ?><tr><td><b>How heard</b></td><td><?php echo esc_html($ref); ?></td></tr><?php endif; ?>
        <?php if($deadline): ?><tr><td><b>Ideal finish date</b></td><td><?php echo esc_html($deadline); ?></td></tr><?php endif; ?>
        <tr><td><b>Appointment</b></td><td><?php echo esc_html($whenHuman); ?></td></tr>
      </table>
      <?php if($about): ?>
        <p><b>About their business</b><br><?php echo nl2br(esc_html($about)); ?></p>
      <?php endif; ?>
      <?php if($stage): ?>
        <p><b>Stage details</b><br><?php echo nl2br(esc_html($stage)); ?></p>
      <?php endif; ?>
      <?php if($gcalTemplate): ?>
        <p>
          <a href="<?php echo esc_url($gcalTemplate); ?>" target="_blank" rel="noopener">View/Add in Google Calendar</a>
          &nbsp;•&nbsp;
          <a href="<?php echo esc_url($gcalDay); ?>" target="_blank" rel="noopener">Open day view</a>
        </p>
      <?php endif; ?>
      <?php if($err): ?>
        <p style="color:#b00020"><b>Calendar note:</b> <?php echo esc_html($err); ?></p>
      <?php endif; ?>
    <?php
    $reportHtml = ob_get_clean();

    // The main submit email already handled the admin notification (includes calendar link now).

    // Client confirmation (send if a start exists, regardless of endpoint status)
    if ($start && $eventOk) {
      $plain = sprintf(
        "Hi %s,\n\nThanks! Your discovery call is confirmed.\n%s\n\nWe've sent a Google Calendar invitation to %s. If you don't see it, please check your spam folder or add the details manually.\n\nIf you need to reschedule, just reply to this email.\n\n— Foundations Marketing",
        $name ?: 'there',
        $whenHuman !== 'No appointment selected' ? ('When: ' . $whenHuman) : '',
        $email ?: 'your inbox'
      );
      FMCD_Mail::send_custom($email, 'Your appointment is confirmed', $plain, false);
    }

    return ['ok'=>$ok, 'error'=>$err];
  }

  /** Manual webhook test (admin only) */
  public function test_webhook(\WP_REST_Request $r){
    $type = $this->clean($r->get_param('type')); // contact|discovery
    $fields = [
      'full_name' => 'Test User',
      'email'     => 'test@example.com',
      'phone'     => '',
      'about'     => 'Ping from FMCD test_webhook',
    ];

    if ($type === 'discovery') {
      if (class_exists('FMCD_Integrations') && method_exists('FMCD_Integrations','fire_discovery_webhook')){
        $res = FMCD_Integrations::fire_discovery_webhook($fields);
        return ['ok'=>!empty($res['ok']), 'details'=>$res];
      }
    } else {
      if (class_exists('FMCD_Integrations')){
        $res = FMCD_Integrations::fire_contact_webhook($fields);
        return ['ok'=>!empty($res['ok']), 'details'=>$res];
      }
    }
    return new \WP_REST_Response(['ok'=>false,'error'=>'Integrations not available'], 500);
  }
}
