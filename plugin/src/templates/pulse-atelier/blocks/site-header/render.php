<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
$nav = fm_nav('nav_primary');
$brand = (string) fm_setting('brand_label', fm_setting('site_name', 'Pulse'));
?>
<div <?php echo fm_wrapper(['fm-pulse-atelier']); ?> data-pa-root><div class="fm-pa-header__div-1" data-pa-part="header">
  <a class="fm-pa-header__a-2" href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php echo esc_html($brand); ?><span class="fm-pa-header__span-3">.</span></a>
  <nav class="fm-pa-header__div-4" aria-label="<?php esc_attr_e('Primary', 'foundations'); ?>"><?php foreach ($nav as $item) : ?><a class="fm-pa-header__a-5" href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a><?php endforeach; ?><?php if (fm_has_setting('cta_label') && fm_has_setting('cta_url')) : ?><a class="fm-pa-header__a-9" href="<?php echo esc_url((string) fm_setting('cta_url')); ?>"><?php echo esc_html((string) fm_setting('cta_label')); ?></a><?php endif; ?></nav>
</div></div>
