<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
$footer = fm_nav('nav_footer');
$brand = (string) fm_setting('brand_label', fm_setting('site_name', 'Pulse'));
?>
<div <?php echo fm_wrapper(['fm-pulse-atelier']); ?> data-pa-root><footer class="fm-pa-footer__footer-1" data-pa-part="footer">
  <div class="fm-pa-footer__div-2">
    <div class="fm-pa-footer__div-3"><div class="fm-pa-footer__div-4"><?php echo esc_html($brand); ?><span class="fm-pa-footer__span-5">.</span></div><p class="fm-pa-footer__p-6"><?php echo esc_html((string) fm_setting('footer_text')); ?></p></div>
    <div class="fm-pa-footer__div-7"><div class="fm-pa-footer__div-8"><?php esc_html_e('Explore', 'foundations'); ?></div><div class="fm-pa-footer__div-9"><?php foreach ($footer as $item) : ?><a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a><?php endforeach; ?></div></div>
    <div class="fm-pa-footer__div-14"><div class="fm-pa-footer__div-15"><?php esc_html_e('Studio', 'foundations'); ?></div><div class="fm-pa-footer__div-16"><?php echo nl2br(esc_html((string) fm_setting('address')), false); ?></div></div>
    <div class="fm-pa-footer__div-19"><div class="fm-pa-footer__div-20"><?php esc_html_e('Contact', 'foundations'); ?></div><div class="fm-pa-footer__div-21"><?php if (fm_has_setting('email')) : ?><a href="<?php echo esc_url('mailto:' . (string) fm_setting('email')); ?>"><?php echo esc_html((string) fm_setting('email')); ?></a><?php endif; ?><?php if (fm_has_setting('phone')) : ?><a href="<?php echo esc_url('tel:' . preg_replace('/[^0-9+]/', '', (string) fm_setting('phone'))); ?>"><?php echo esc_html((string) fm_setting('phone')); ?></a><?php endif; ?></div></div>
  </div>
  <div class="fm-pa-footer__div-25"><div>© <?php echo esc_html(gmdate('Y') . ' ' . $brand); ?></div><div><?php esc_html_e('Template by Foundations', 'foundations'); ?></div></div>
</footer></div>
