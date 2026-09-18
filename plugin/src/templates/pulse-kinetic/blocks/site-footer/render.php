<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
$primary = fm_nav('nav_primary');
$footer = fm_nav('nav_footer');
$brand = (string) fm_setting('brand_label', 'PULSE');
$email = (string) fm_setting('email', 'hey@pulsestudio.co.uk');
$site_name = (string) fm_setting('site_name', 'Pulse Studio');
?>
<div <?php echo fm_wrapper(['fm-pulse-kinetic']); ?> data-pk-root>
  <footer class="fm-pk-footer">
    <div class="fm-pk-footer__inner">
      <div class="fm-pk-footer__grid">
        <div><div class="fm-pk-footer__brand"><?php echo esc_html($brand); ?></div><p><?php echo esc_html((string) fm_setting('footer_text')); ?></p></div>
        <div><h2><?php esc_html_e('Studio', 'foundations'); ?></h2><nav aria-label="<?php esc_attr_e('Studio', 'foundations'); ?>"><?php foreach ($primary as $item) : ?><a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a><?php endforeach; ?></nav></div>
        <div><h2><?php esc_html_e('Get in touch', 'foundations'); ?></h2><nav aria-label="<?php esc_attr_e('Get in touch', 'foundations'); ?>"><?php foreach (array_slice($footer, 0, 2) as $item) : ?><a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a><?php endforeach; ?><a href="<?php echo esc_url('mailto:' . $email); ?>"><?php echo esc_html($email); ?></a></nav></div>
        <div><h2><?php esc_html_e('Legal', 'foundations'); ?></h2><nav aria-label="<?php esc_attr_e('Legal', 'foundations'); ?>"><?php foreach (array_slice($footer, 2) as $item) : ?><a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a><?php endforeach; ?></nav></div>
      </div>
      <div class="fm-pk-footer__bottom"><span>© 2026 <?php echo esc_html($site_name); ?></span><span>Template by Foundations</span></div>
    </div>
  </footer>
</div>
