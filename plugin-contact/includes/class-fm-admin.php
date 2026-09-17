<?php
if (!defined('ABSPATH')) exit;

class FMCD_Admin {
  public function __construct() {
    add_action('admin_menu', [$this, 'menu']);
    add_action('admin_init', [$this, 'settings']);
  }

  public function menu() {
    add_menu_page('FM Forms', 'FM Forms', 'manage_options', 'fmcd', [$this, 'page'], 'dashicons-feedback', 58);
  }

  public function settings() {
    // Email
    register_setting('fmcd', 'fmcd_to_email');
    register_setting('fmcd', 'fmcd_cc_emails');

    // Calendar
    register_setting('fmcd', 'fmcd_calendar_email');
    register_setting('fmcd', 'fmcd_calendar_endpoint');
    register_setting('fmcd', 'fmcd_optimistic_booking'); // 0/1

    add_settings_section('fmcd_email', 'Email Settings', '__return_false', 'fmcd');
    add_settings_field('fmcd_to_email', 'Primary recipient (To)', [$this, 'field_text'], 'fmcd', 'fmcd_email', ['key'=>'fmcd_to_email', 'ph'=>'info@foundationsmarketing.co.uk']);
    add_settings_field('fmcd_cc_emails', 'CC recipients (comma separated)', [$this, 'field_text'], 'fmcd', 'fmcd_email', ['key'=>'fmcd_cc_emails', 'ph'=>'you@domain.com, teammate@domain.com']);

    add_settings_section('fmcd_cal', 'Calendar Settings', [$this,'cal_help'], 'fmcd');
    add_settings_field('fmcd_calendar_email', 'Calendar Email', [$this, 'field_text'], 'fmcd', 'fmcd_cal', ['key'=>'fmcd_calendar_email', 'ph'=>'info@foundationsmarketing.co.uk']);
    add_settings_field('fmcd_calendar_endpoint', 'Availability API Endpoint', [$this, 'field_text'], 'fmcd', 'fmcd_cal', ['key'=>'fmcd_calendar_endpoint', 'ph'=>'https://script.google.com/macros/s/XXXX/exec']);
    add_settings_field('fmcd_optimistic_booking', 'Optimistic booking (treat non-200 as success)', function(){
      $v = get_option('fmcd_optimistic_booking', '1');
      echo '<label><input type="checkbox" name="fmcd_optimistic_booking" value="1" '.checked($v, '1', false).'> Enable (temporary fallback while debugging GAS)</label>';
    }, 'fmcd', 'fmcd_cal');

    // Mailchimp & Webhooks
    register_setting('fmcd', 'fmcd_mc_api_key');
    register_setting('fmcd', 'fmcd_mc_list_contact');
    register_setting('fmcd', 'fmcd_mc_tags_contact');
    register_setting('fmcd', 'fmcd_mc_list_discovery');
    register_setting('fmcd', 'fmcd_mc_tags_discovery');
    register_setting('fmcd', 'fmcd_contact_webhook');
    register_setting('fmcd', 'fmcd_discovery_webhook');

    add_settings_section('fmcd_mc', 'Mailchimp & Webhooks', function(){
      echo '<p>Connect Mailchimp audiences and optional webhooks. Mailchimp status_if_new is <b>subscribed</b>; tags are applied per form.</p>';
    }, 'fmcd');

    $this->add_text('fmcd_mc_api_key',           'Mailchimp API Key',              'fmcd', 'fmcd_mc', 'xxxxx-us21');
    $this->add_text('fmcd_mc_list_contact',      'Contact – Audience/List ID',     'fmcd', 'fmcd_mc', 'abcd1234');
    $this->add_text('fmcd_mc_tags_contact',      'Contact – Tags (comma separated)','fmcd', 'fmcd_mc', 'Contact, Website, Lead');
    $this->add_text('fmcd_mc_list_discovery',    'Discovery – Audience/List ID',   'fmcd', 'fmcd_mc', 'efgh5678');
    $this->add_text('fmcd_mc_tags_discovery',    'Discovery – Tags',               'fmcd', 'fmcd_mc', 'Discovery, Questionnaire');
    $this->add_text('fmcd_contact_webhook',      'Contact – Webhook URL',          'fmcd', 'fmcd_mc', 'https://hook.eu2.make.com/XXXX');
    $this->add_text('fmcd_discovery_webhook',    'Discovery – Webhook URL',        'fmcd', 'fmcd_mc', 'https://hook.eu2.make.com/YYYY');
  }

  private function add_text($key, $label, $page, $section, $ph='') {
    add_settings_field($key, $label, [$this,'field_text'], $page, $section, ['key'=>$key, 'ph'=>$ph]);
  }

  public function field_text($args) {
    $val = esc_attr(get_option($args['key'], ''));
    printf('<input type="text" style="width:420px" name="%s" value="%s" placeholder="%s"/>',
      esc_attr($args['key']), $val, esc_attr($args['ph']));
  }

  public function cal_help() {
    echo '<p>Provide a Web API endpoint that returns availability/booking for your Google Calendar (recommended: a Google Apps Script Web App connected to <b>info@foundationsmarketing.co.uk</b>). Hours are fixed to <b>3–8 PM Europe/London</b>.</p>';
  }

  public function page() { ?>
    <div class="wrap">
      <h1>FM Contact & Discovery Forms</h1>
      <form method="post" action="options.php">
        <?php
        settings_fields('fmcd');
        do_settings_sections('fmcd');
        submit_button();
        ?>
      </form>

      <hr>
      <h2>Diagnostics</h2>
      <?php
      $lc = get_option('fmcd_last_contact_webhook');
      $ld = get_option('fmcd_last_discovery_webhook');
      $this->dump_diag('Last Contact Webhook', $lc);
      $this->dump_diag('Last Discovery Webhook', $ld);
      ?>
      <p><em>After submitting a form, refresh to see the latest webhook HTTP code/body.</em></p>
    </div>
  <?php }

  private function dump_diag($label, $data){
    echo '<h3 style="margin-bottom:6px">'.$label.'</h3>';
    if (!$data){ echo '<p>No data yet.</p>'; return; }
    echo '<pre style="max-width:900px;white-space:pre-wrap;background:#f6f8fa;padding:10px;border-radius:8px;border:1px solid #e5e7eb">';
    echo esc_html(print_r($data, true));
    echo '</pre>';
  }
}