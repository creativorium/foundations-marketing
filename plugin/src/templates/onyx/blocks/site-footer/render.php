<?php
if (!defined('ABSPATH')) { exit; }
?>
<div <?php echo fm_wrapper(['fm-onyx-footer']); ?>>
  <div class="fm-onyx-footer__grid">
    <div><span class="fm-onyx-footer__brand"><?php echo esc_html((string) fm_setting('brand_label', 'ONYX')); ?></span><p><?php echo esc_html((string) fm_setting('footer_text')); ?></p></div>
    <div><h2>Pages</h2><?php foreach (fm_nav('nav_primary') as $item) : ?><a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a><?php endforeach; ?></div>
    <div><h2>Clinic</h2><p><?php echo nl2br(esc_html((string) fm_setting('address')), false); ?></p><p><?php echo nl2br(esc_html((string) fm_setting('opening_hours')), false); ?></p></div>
    <div><h2>Legal</h2><?php foreach (fm_nav('nav_footer') as $item) : ?><a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a><?php endforeach; ?></div>
  </div>
  <div class="fm-onyx-footer__legal"><span>&copy; <?php echo esc_html(wp_date('Y').' '.(string) fm_setting('site_name')); ?></span><span>Template by Foundations</span></div>
</div>
