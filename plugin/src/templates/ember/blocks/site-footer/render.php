<?php
if (!defined('ABSPATH')) { exit; }
$brand = function_exists('fm_setting') ? (string) fm_setting('brand_label', 'Serene · Touch') : 'Serene · Touch';
$email = function_exists('fm_setting') ? (string) fm_setting('email', 'hello@seretouch.com') : 'hello@seretouch.com';
$credit = function_exists('fm_setting') ? (string) fm_setting('footer_text', 'Template by Foundations Marketing') : 'Template by Foundations Marketing';
$nav = function_exists('fm_nav') ? fm_nav('nav_footer') : [];
if (!$nav) {
    $nav = [
        ['label' => 'About', 'url' => '#about'],
        ['label' => 'Treatments', 'url' => '#treatments'],
        ['label' => 'Book', 'url' => '#book'],
    ];
}
?>
<div class="ember-footer">
  <div class="ember-footer-logo"><?php echo esc_html($brand); ?></div>
  <nav class="ember-footer-links" aria-label="<?php esc_attr_e('Footer navigation', 'foundations'); ?>">
    <?php foreach ($nav as $item) : ?><a href="<?php echo esc_url((string) $item['url']); ?>"><?php echo esc_html((string) $item['label']); ?></a><?php endforeach; ?>
    <?php if ($email !== '') : ?><a href="mailto:<?php echo esc_attr($email); ?>">Contact</a><?php endif; ?>
  </nav>
  <p class="ember-footer-credit"><?php echo esc_html($credit); ?></p>
</div>
