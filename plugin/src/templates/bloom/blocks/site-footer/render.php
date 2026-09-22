<?php
if (!defined('ABSPATH')) { exit; } $brand=(string)fm_setting('brand_label','Bloom'); $name=(string)fm_setting('site_name',$brand); $nav=fm_nav('nav_footer');
?>
<div class="bloom-footer"><strong><?php echo esc_html($brand); ?></strong><span>&copy; <?php echo esc_html(wp_date('Y').' '.$name); ?></span><nav aria-label="<?php esc_attr_e('Legal','foundations'); ?>"><?php foreach($nav as $item): ?><a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a><?php endforeach; ?><a href="https://cularcreative.com/" target="_blank" rel="noopener">Made by Cular Creative</a></nav></div>
