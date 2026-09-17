<?php
/**
 * Halo — the site footer.
 *
 * Site Settings only, for the same reason as the header: it is a template part on every
 * page, so the address is edited once (customer-runtime.md §5.1).
 *
 * The visit column prints the address and the opening hours; the contact column prints
 * whatever of phone, email and the footer menu exists. Each is skipped when empty rather
 * than printing a heading with nothing under it.
 *
 * @var array $attributes
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$address = (string) fm_setting('address');
$hours   = (string) fm_setting('opening_hours');
$phone   = (string) fm_setting('phone');
$email   = (string) fm_setting('email');
$footer  = fm_nav('nav_footer');
?>
<div <?php echo fm_wrapper(['fm-halo-footer']); ?>>
  <div class="fm-halo-footer__grid">
    <div>
      <span class="fm-halo-footer__brand"><?php echo esc_html((string) fm_setting('brand_label', 'Halo')); ?></span>
      <p class="fm-halo-footer__text"><?php echo esc_html((string) fm_setting('footer_text')); ?></p>
    </div>

    <?php if ($address !== '' || $hours !== '') : ?>
      <div>
        <h2 class="fm-halo-footer__label"><?php esc_html_e('Visit', 'foundations'); ?></h2>
        <?php if ($address !== '') : ?>
          <p class="fm-halo-footer__line"><?php echo nl2br(esc_html($address), false); ?></p>
        <?php endif; ?>
        <?php if ($hours !== '') : ?>
          <p class="fm-halo-footer__line"><?php echo esc_html($hours); ?></p>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div>
      <h2 class="fm-halo-footer__label"><?php esc_html_e('Contact', 'foundations'); ?></h2>
      <?php if ($email !== '') : ?>
        <a href="<?php echo esc_url('mailto:' . $email); ?>"><?php echo esc_html($email); ?></a>
      <?php endif; ?>
      <?php if ($phone !== '') : ?>
        <?php // tel: strips spaces; the visible number keeps them, because it is read, not dialled by eye. ?>
        <a href="<?php echo esc_url('tel:' . preg_replace('/\s+/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a>
      <?php endif; ?>
      <?php foreach ($footer as $item) : ?>
        <a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="fm-halo-footer__legal">
    <span>&copy; <?php echo esc_html(wp_date('Y') . ' ' . (string) fm_setting('site_name')); ?></span>
    <span class="fm-halo-footer__legal-links">
      <?php foreach ($footer as $item) : ?>
        <a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a>
      <?php endforeach; ?>
      <span><?php esc_html_e('Template by Foundations', 'foundations'); ?></span>
    </span>
  </div>
</div>
