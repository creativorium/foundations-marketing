<?php
/**
 * Halo — the site header.
 *
 * Every word here comes from Site Settings, not from block attributes: the header is a
 * template part rendered on every page, so a customer editing it in one place must
 * change it everywhere (customer-runtime.md §5.1). There is nothing for a page author
 * to fill in, which is why the block is not in the inserter.
 *
 * @var array $attributes
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$nav   = fm_nav('nav_primary');
$home  = home_url('/');
$brand = (string) fm_setting('brand_label', 'Halo');
?>
<div <?php echo fm_wrapper(['fm-halo-header']); ?>>
  <a class="fm-halo-brand" href="<?php echo esc_url($home); ?>" rel="home">
    <?php if (fm_setting('logo_id')) : ?>
      <?php echo wp_get_attachment_image((int) fm_setting('logo_id'), 'medium', false, ['class' => 'fm-halo-brand__logo', 'alt' => (string) fm_setting('site_name')]); ?>
    <?php else : ?>
      <?php echo esc_html($brand); ?>
    <?php endif; ?>
  </a>

  <nav class="fm-halo-header__nav" aria-label="<?php esc_attr_e('Primary', 'foundations'); ?>">
    <?php foreach ($nav as $item) : ?>
      <a
        class="fm-halo-header__link"
        href="<?php echo esc_url($item['url']); ?>"
        <?php echo $item['current'] ? 'aria-current="page"' : ''; ?>
      ><?php echo esc_html($item['label']); ?></a>
    <?php endforeach; ?>

    <?php
    // Both halves or neither: a booking pill with no destination is a dead control,
    // and on this design it is the most prominent thing in the bar.
    if (fm_has_setting('cta_url') && fm_has_setting('cta_label')) :
        ?>
      <a class="fm-halo-header__cta" href="<?php echo esc_url((string) fm_setting('cta_url')); ?>">
        <?php echo esc_html((string) fm_setting('cta_label')); ?>
      </a>
    <?php endif; ?>
  </nav>
</div>
