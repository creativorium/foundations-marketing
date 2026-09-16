<?php
if (!defined('ABSPATH')) { exit; }
$a = $attributes;
$text = static fn($key) => esc_html((string)($a[$key] ?? ''));
$items = array_values(array_filter((array)($a['items'] ?? []), 'is_array'));
$anchor = sanitize_title((string)($a['anchor'] ?? ''));
?>
<?php $setting=static fn($key)=>function_exists('fm_setting')?(string)fm_setting($key):''; ?><div class="meridian meridian-footer"><div class="meridian-container"><div class="meridian-footer__row"><div><p class="meridian-brand"><?php echo esc_html($setting('site_name')); ?></p><p><?php echo nl2br(esc_html($setting('address'))); ?><br><a href="<?php echo esc_url('mailto:'.$setting('email')); ?>"><?php echo esc_html($setting('email')); ?></a></p></div><nav aria-label="Footer navigation"><?php foreach(function_exists('fm_nav')?fm_nav('nav_footer'):[] as $item): ?><a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a><?php endforeach; ?></nav></div><p class="meridian-footer__credit">© <?php echo esc_html(wp_date('Y')); ?> <?php echo esc_html($setting('site_name')); ?> · <a href="https://foundationsmarketing.co.uk/">Website by Foundations Marketing</a></p></div></div>
