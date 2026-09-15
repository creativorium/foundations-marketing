<?php
if (!defined('ABSPATH')) exit;

class FMCD_Mail {
  public static function send($subject, $html, $extra_headers = []) {
    $to = trim((string) get_option('fmcd_to_email', ''));
    if (!$to) {
      // Fallback to WordPress admin email so submissions never silently drop
      $to = get_option('admin_email');
      if (!$to) { error_log('[FMCD] Missing To email and admin_email'); return false; }
    }

    $cc = trim((string) get_option('fmcd_cc_emails', ''));
    $headers = array_merge([
      'Content-Type: text/html; charset=UTF-8',
      'From: Foundations Marketing <no-reply@' . parse_url(home_url(), PHP_URL_HOST) . '>',
    ], $extra_headers);

    if ($cc) $headers[] = 'Cc: ' . $cc;

    $ok = wp_mail($to, $subject, $html, $headers);
    if (!$ok) error_log('[FMCD] wp_mail failed for subject: ' . $subject);
    return $ok;
  }

  public static function send_custom($to, $subject, $content, $is_html = true, $extra_headers = []){
    $to = trim((string) $to);
    if (!$to) return false;
    $headers = $extra_headers;
    if ($is_html){
      $headers[] = 'Content-Type: text/html; charset=UTF-8';
    } else {
      $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    }
    $headers[] = 'From: Foundations Marketing <no-reply@' . parse_url(home_url(), PHP_URL_HOST) . '>';
    return wp_mail($to, $subject, $content, $headers);
  }
}
