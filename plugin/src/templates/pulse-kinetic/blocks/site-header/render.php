<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
$nav = fm_nav('nav_primary');
$brand = (string) fm_setting('brand_label', 'PULSE');
$cta_label = (string) fm_setting('cta_label', 'Start free week');
$cta_url = (string) fm_setting('cta_url', home_url('/#book'));
?>
<div <?php echo fm_wrapper(['fm-pulse-kinetic']); ?> data-pk-root>
  <header class="fm-pk-header">
    <a class="fm-pk-header__brand" href="<?php echo esc_url(home_url('/#top')); ?>" rel="home"><?php echo esc_html($brand); ?></a>
    <nav aria-label="<?php esc_attr_e('Primary', 'foundations'); ?>">
      <?php foreach ($nav as $item) : ?><a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a><?php endforeach; ?>
    </nav>
    <details class="fm-pk-header__menu">
      <summary><?php esc_html_e('Menu', 'foundations'); ?></summary>
      <nav aria-label="<?php esc_attr_e('Mobile primary', 'foundations'); ?>">
        <?php foreach ($nav as $item) : ?><a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a><?php endforeach; ?>
      </nav>
    </details>
    <a class="fm-pk-header__cta" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_label); ?></a>
  </header>
</div>
