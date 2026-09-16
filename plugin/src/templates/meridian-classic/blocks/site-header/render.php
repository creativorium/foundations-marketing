<?php
if (!defined('ABSPATH')) { exit; }
$nav = function_exists('fm_nav') ? fm_nav('nav_primary') : [];
$setting = static fn($key) => function_exists('fm_setting') ? (string) fm_setting($key) : '';
$uid = wp_unique_id('meridian-nav-');
$name = $setting('site_name') ?: 'Alder & Fern';
?>
<div class="meridian meridian-header" data-meridian-header>
  <div class="meridian-header__bar">
    <a class="meridian-brand" href="<?php echo esc_url($setting('home_url') ?: home_url('/')); ?>">
      <?php $logo = (int) $setting('logo_id'); echo $logo ? wp_get_attachment_image($logo, 'medium', false, ['alt' => $name, 'loading' => 'eager']) : esc_html($name); ?>
    </a>
    <nav class="meridian-nav" id="<?php echo esc_attr($uid); ?>" aria-label="Main navigation">
      <button type="button" class="meridian-menu-close" data-menu-close aria-label="Close navigation">×</button>
      <ul>
        <?php foreach ($nav as $item) : ?>
          <li><a href="<?php echo esc_url($item['url']); ?>" <?php echo !empty($item['current']) ? 'aria-current="page"' : ''; ?>><?php echo esc_html($item['label']); ?></a></li>
        <?php endforeach; ?>
        <?php if ($setting('cta_url')) : ?><li class="meridian-nav__mobile-cta"><a href="<?php echo esc_url($setting('cta_url')); ?>">Book a treatment</a></li><?php endif; ?>
      </ul>
    </nav>
    <?php if ($setting('cta_url') && $setting('cta_label')) : ?><a class="meridian-button meridian-header__cta" href="<?php echo esc_url($setting('cta_url')); ?>"><?php echo esc_html($setting('cta_label')); ?></a><?php endif; ?>
    <button type="button" class="meridian-menu-toggle" aria-expanded="false" aria-controls="<?php echo esc_attr($uid); ?>" aria-label="Open navigation"><span aria-hidden="true"></span><span aria-hidden="true"></span></button>
  </div>
</div>
