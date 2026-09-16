<?php
if (!defined('ABSPATH')) { exit; }
$nav = fm_nav('nav_primary');
$home = $nav[0]['url'] ?? home_url('/');
$brand = (string) fm_setting('brand_label', 'ONYX');
?>
<div <?php echo fm_wrapper(['fm-onyx-header']); ?>>
  <a class="fm-onyx-brand" href="<?php echo esc_url($home); ?>">
    <?php if (fm_setting('logo_id')) : ?>
      <?php echo wp_get_attachment_image((int) fm_setting('logo_id'), 'medium', false, ['class'=>'fm-onyx-brand__logo','alt'=>(string) fm_setting('site_name')]); ?>
    <?php else : ?>
      <span class="fm-onyx-brand__dot" aria-hidden="true"></span>
      <span class="fm-onyx-brand__name"><?php echo esc_html($brand); ?></span>
      <span class="fm-onyx-brand__descriptor"><?php echo esc_html((string) fm_setting('brand_descriptor', 'Aesthetics')); ?></span>
    <?php endif; ?>
  </a>
  <nav class="fm-onyx-header__nav" aria-label="Primary">
    <?php foreach ($nav as $item) : ?>
      <div class="fm-onyx-header__item">
        <a href="<?php echo esc_url($item['url']); ?>" <?php echo $item['current'] ? 'aria-current="page"' : ''; ?>><?php echo esc_html($item['label']); ?></a>
        <?php foreach ($item['children'] as $child) : ?>
          <a class="fm-onyx-header__child" href="<?php echo esc_url($child['url']); ?>"><?php echo esc_html($child['label']); ?></a>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
    <?php if (fm_has_setting('cta_url') && fm_has_setting('cta_label')) : ?>
      <a class="fm-onyx-header__cta" href="<?php echo esc_url((string) fm_setting('cta_url')); ?>"><?php echo esc_html((string) fm_setting('cta_label')); ?></a>
    <?php endif; ?>
  </nav>
</div>
