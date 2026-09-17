<?php
if (!defined('ABSPATH')) exit;

class FMCD_CPT {
  public function __construct() {
    add_action('init', [$this, 'register']);
    add_action('add_meta_boxes', [$this, 'add_boxes']);
  }

  public function register() {
    register_post_type('fmcd_entry', [
      'labels' => ['name' => 'Form Entries', 'singular_name' => 'Form Entry'],
      'public' => false,
      'show_ui' => true,
      'menu_icon' => 'dashicons-archive',
      'supports' => ['title', 'custom-fields'],
    ]);
  }

  public function add_boxes() {
    add_meta_box('fmcd_entry_summary', 'Form Entry Summary', [$this, 'render_entry_summary'], 'fmcd_entry', 'normal', 'high');
  }

  private function html_row($label, $value){
    if ($value === '' || $value === null) return '';
    if (is_array($value)) $value = implode(', ', array_map('sanitize_text_field', $value));
    $label = esc_html($label);
    $value = wp_kses_post($value);
    return "<tr><th align='left' style='padding:6px 10px;border-bottom:1px solid #eee;width:220px;'>$label</th><td style='padding:6px 10px;border-bottom:1px solid #eee;'>$value</td></tr>";
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
        return 'No - I need help choosing images';
      case 'freepik':
        return 'No - I want to choose from Freepik stock images';
      case 'no':
        return 'No - I need help choosing images';
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
      default:
        return '';
    }
  }

  public function render_entry_summary($post){
    $type = get_post_meta($post->ID, '_fmcd_type', true);
    $data = get_post_meta($post->ID, '_fmcd_payload', true);
    if (!is_array($data)) $data = [];
    $files = get_post_meta($post->ID, '_fmcd_files', true);
    if (!is_array($files)) $files = [];
    $asset = get_post_meta($post->ID, '_fmcd_asset_link', true);

    $rows = '';
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
    } else { // discovery (default)
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
      $rows .= $this->html_row('Domain provider', $data['domain_provider'] ?? '');
      $rows .= $this->html_row('Domain username', $data['domain_username'] ?? '');
      $rows .= $this->html_row('Domain password', $data['domain_password'] ?? '');
      $rows .= $this->html_row('Hosting access status', $this->format_access_status($data['hosting_status'] ?? ''));
      $rows .= $this->html_row('Hosting provider', $data['hosting_provider'] ?? '');
      $rows .= $this->html_row('Hosting username', $data['hosting_username'] ?? '');
      $rows .= $this->html_row('Hosting password', $data['hosting_password'] ?? '');
      $rows .= $this->html_row('Business email access status', $this->format_access_status($data['email_status'] ?? ''));
      $rows .= $this->html_row('Business email platform', $data['email_platform'] ?? '');
      $rows .= $this->html_row('Business email username', $data['email_username'] ?? '');
      $rows .= $this->html_row('Business email password', $data['email_password'] ?? '');
      $rows .= $this->html_row('Booking access status', $this->format_access_status($data['booking_status'] ?? ''));
      $rows .= $this->html_row('Booking platform', $data['booking_platform'] ?? '');
      $rows .= $this->html_row('Booking username', $data['booking_username'] ?? '');
      $rows .= $this->html_row('Booking password', $data['booking_password'] ?? '');
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

    if (!empty($files)){
      $links = [];
      foreach ($files as $entry){
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
    if (!empty($asset)){
      $rows .= $this->html_row('Legacy Asset Link', '<a href="'.esc_url($asset).'" target="_blank" rel="noopener">'.esc_html($asset).'</a>');
    }

    echo "<div style='font:14px/1.45 -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Inter,Arial; color:#222'><table cellspacing='0' cellpadding='0' style='border-collapse:collapse;width:100%'>$rows</table></div>";
  }
}
