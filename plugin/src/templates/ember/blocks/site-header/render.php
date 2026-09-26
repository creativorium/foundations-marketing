<?php
if (!defined('ABSPATH')) { exit; }
$brand = function_exists('fm_setting') ? (string) fm_setting('brand_label', 'Serene · Touch') : 'Serene · Touch';
$nav = function_exists('fm_nav') ? fm_nav('nav_primary') : [];
if (!$nav) {
    $nav = [
        ['label' => 'About', 'url' => '#about'],
        ['label' => 'Treatments', 'url' => '#treatments'],
        ['label' => 'Book', 'url' => '#book'],
    ];
}
?>
<nav class="ember-site-nav" data-ember-nav aria-label="<?php esc_attr_e('Primary navigation', 'foundations'); ?>">
  <div class="ember-nav-inner">
    <div class="ember-nav-logo"><?php echo esc_html($brand); ?></div>
    <ul class="ember-nav-links">
      <?php foreach ($nav as $item) : ?>
        <li><a href="<?php echo esc_url((string) $item['url']); ?>"><?php echo esc_html((string) $item['label']); ?></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
</nav>
