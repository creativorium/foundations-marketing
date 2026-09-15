<?php
if (!defined('ABSPATH')) exit;

class FMCD_Integrations {

  private static function log_webhook($key, $data){
    $data['time'] = current_time('mysql');
    update_option($key, $data, false);
  }

  /** Upsert to Mailchimp (per form type) and apply tags. */
  public static function mailchimp_sync($type, $fields){
    $apiKey = trim((string) get_option('fmcd_mc_api_key', ''));
    if (!$apiKey || strpos($apiKey, '-') === false) return ['ok'=>false,'skip'=>true,'reason'=>'No API key'];
    $dc = substr($apiKey, strpos($apiKey, '-')+1);

    $list = '';
    $tagsCsv = '';
    if ($type === 'contact') {
      $list = trim((string) get_option('fmcd_mc_list_contact', ''));
      $tagsCsv = (string) get_option('fmcd_mc_tags_contact', '');
    } else {
      $list = trim((string) get_option('fmcd_mc_list_discovery', ''));
      $tagsCsv = (string) get_option('fmcd_mc_tags_discovery', '');
    }
    if (!$list) return ['ok'=>false,'skip'=>true,'reason'=>'No list configured'];

    $email = isset($fields['email']) ? sanitize_email($fields['email']) : '';
    if (!$email) return ['ok'=>false,'skip'=>true,'reason'=>'No email in payload'];

    // Merge fields
    $full = isset($fields['full_name']) ? sanitize_text_field($fields['full_name']) : '';
    $parts = preg_split('/\s+/', $full, 2);
    $fname = $parts[0] ?? '';
    $lname = $parts[1] ?? '';
    $phone = isset($fields['phone']) ? sanitize_text_field($fields['phone']) : '';
    $biz   = isset($fields['business_name']) ? sanitize_text_field($fields['business_name']) : '';

    $memberHash = md5(strtolower($email));
    $endpoint = "https://{$dc}.api.mailchimp.com/3.0/lists/{$list}/members/{$memberHash}";

    $payload = [
      'email_address'  => $email,
      'status_if_new'  => 'subscribed',
      'merge_fields'   => [
        'FNAME'   => $fname,
        'LNAME'   => $lname,
        'PHONE'   => $phone,
        'BIZNAME' => $biz,
      ],
    ];

    $resp = wp_remote_request($endpoint, [
      'method'  => 'PUT',
      'headers' => [
        'Authorization' => 'Basic ' . base64_encode('any:' . $apiKey),
        'Content-Type'  => 'application/json'
      ],
      'timeout' => 15,
      'body'    => wp_json_encode($payload)
    ]);

    if (is_wp_error($resp)) { error_log('[FMCD] Mailchimp upsert error: '.$resp->get_error_message()); return ['ok'=>false]; }
    $code = wp_remote_retrieve_response_code($resp);
    if ($code < 200 || $code >= 300) {
      error_log('[FMCD] Mailchimp upsert HTTP '.$code.' body: '.wp_remote_retrieve_body($resp));
      return ['ok'=>false];
    }

    // Tags
    $tags = array_filter(array_map('trim', explode(',', $tagsCsv)));
    if ($tags) {
      $tagsEndpoint = "https://{$dc}.api.mailchimp.com/3.0/lists/{$list}/members/{$memberHash}/tags";
      $tagBody = ['tags' => array_map(function($t){ return ['name'=>$t,'status'=>'active']; }, $tags)];
      $tResp = wp_remote_post($tagsEndpoint, [
        'headers' => [
          'Authorization' => 'Basic ' . base64_encode('any:' . $apiKey),
          'Content-Type'  => 'application/json'
        ],
        'timeout' => 15,
        'body'    => wp_json_encode($tagBody)
      ]);
      if (is_wp_error($tResp) || (wp_remote_retrieve_response_code($tResp) < 200 || wp_remote_retrieve_response_code($tResp) >= 300)) {
        error_log('[FMCD] Mailchimp tag error: '.(is_wp_error($tResp)?$tResp->get_error_message():wp_remote_retrieve_body($tResp)));
      }
    }

    return ['ok'=>true];
  }

  /** Contact webhook forward (JSON) */
  public static function fire_contact_webhook($fields){
    $url = trim((string) get_option('fmcd_contact_webhook', ''));
    if (!$url) { self::log_webhook('fmcd_last_contact_webhook', ['ok'=>false,'skip'=>true,'reason'=>'no_url']); return ['ok'=>false,'skip'=>true]; }

    $resp = wp_remote_post(esc_url_raw($url), [
      'timeout' => 15,
      'headers' => ['Content-Type'=>'application/json'],
      'body'    => wp_json_encode(['type'=>'contact','payload'=>$fields,'site'=>home_url('/', null)])
    ]);

    if (is_wp_error($resp)) {
      $err = $resp->get_error_message();
      error_log('[FMCD] Contact webhook error: '.$err);
      self::log_webhook('fmcd_last_contact_webhook', ['ok'=>false,'error'=>$err]);
      return ['ok'=>false, 'error'=>$err];
    }

    $code = wp_remote_retrieve_response_code($resp);
    $body = wp_remote_retrieve_body($resp);
    self::log_webhook('fmcd_last_contact_webhook', ['ok'=>($code>=200 && $code<300), 'code'=>$code, 'body'=>substr($body,0,500)]);

    if ($code < 200 || $code >= 300) {
      error_log('[FMCD] Contact webhook HTTP '.$code.' body: '.$body);
      return ['ok'=>false, 'code'=>$code, 'body'=>$body];
    }
    return ['ok'=>true];
  }

  /** Discovery webhook forward (JSON) */
  public static function fire_discovery_webhook($fields){
    $url = trim((string) get_option('fmcd_discovery_webhook', ''));
    if (!$url) { self::log_webhook('fmcd_last_discovery_webhook', ['ok'=>false,'skip'=>true,'reason'=>'no_url']); return ['ok'=>false,'skip'=>true]; }

    $resp = wp_remote_post(esc_url_raw($url), [
      'timeout' => 15,
      'headers' => ['Content-Type'=>'application/json'],
      'body'    => wp_json_encode(['type'=>'discovery','payload'=>$fields,'site'=>home_url('/', null)])
    ]);

    if (is_wp_error($resp)) {
      $err = $resp->get_error_message();
      error_log('[FMCD] Discovery webhook error: '.$err);
      self::log_webhook('fmcd_last_discovery_webhook', ['ok'=>false,'error'=>$err]);
      return ['ok'=>false, 'error'=>$err];
    }

    $code = wp_remote_retrieve_response_code($resp);
    $body = wp_remote_retrieve_body($resp);
    self::log_webhook('fmcd_last_discovery_webhook', ['ok'=>($code>=200 && $code<300), 'code'=>$code, 'body'=>substr($body,0,500)]);

    if ($code < 200 || $code >= 300) {
      error_log('[FMCD] Discovery webhook HTTP '.$code.' body: '.$body);
      return ['ok'=>false, 'code'=>$code, 'body'=>$body];
    }
    return ['ok'=>true];
  }
}
