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
    <div class="ember-nav-logo"><?php
      $logo = function_exists('fm_setting') ? (int) fm_setting('logo_id', 0) : 0;
      echo $logo ? wp_get_attachment_image($logo, 'medium', false, ['alt' => $brand]) : esc_html($brand);
    ?></div>
    <ul class="ember-nav-links">
      <?php foreach ($nav as $item) : ?>
        <?php
        $url = (string) $item['url'];
        $fragment = wp_parse_url($url, PHP_URL_FRAGMENT);
        if ($fragment && wp_parse_url($url, PHP_URL_HOST) === wp_parse_url(home_url('/'), PHP_URL_HOST) && wp_parse_url($url, PHP_URL_PATH) === wp_parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH)) { $url = '#' . $fragment; }
        ?>
        <li><a href="<?php echo esc_url($url); ?>"><?php echo esc_html((string) $item['label']); ?></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
</nav>
