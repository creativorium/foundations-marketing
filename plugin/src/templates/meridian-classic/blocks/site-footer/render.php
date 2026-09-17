<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
$setting = static fn(string $key): string => function_exists('fm_setting') ? (string) fm_setting($key) : '';
$name = $setting('site_name') ?: 'Alder & Fern';
$nav = function_exists('fm_nav') ? fm_nav('nav_footer') : [];
$address = str_replace(["\r\n", "\r", "\n"], ' · ', $setting('address'));
$email = $setting('email');
?>
<div class="meridian meridian-footer" data-meridian-footer>
  <div class="meridian-container">
    <div class="meridian-footer__row">
      <div><p class="meridian-brand"><?php echo esc_html($name); ?></p><p><?php echo esc_html($address); ?><?php if ($email !== '') : ?> · <a href="<?php echo esc_url('mailto:' . $email); ?>"><?php echo esc_html($email); ?></a><?php endif; ?></p></div>
      <?php if ($nav !== []) : ?><nav aria-label="<?php esc_attr_e('Footer navigation', 'foundations'); ?>"><?php foreach ($nav as $item) : ?><a href="<?php echo esc_url($item['url']); ?>" <?php echo !empty($item['current']) ? 'aria-current="page"' : ''; ?>><?php echo esc_html($item['label']); ?></a><?php endforeach; ?></nav><?php endif; ?>
    </div>
    <p class="meridian-footer__credit">© <?php echo esc_html(wp_date('Y')); ?> <?php echo esc_html($name); ?> — Meridian template by <a href="https://foundationsmarketing.co.uk/">Foundations Marketing</a></p>
  </div>
</div>