<?php
/**
 * The site header.
 *
 * One block, in parts/header.html, rendered site-wide. The customer edits the VALUES in
 * Site Settings; the LAYOUT is ours (customer-runtime.md §5.1). That is the whole product
 * promise for the header, so keep the markup here and keep it fixed.
 *
 * Navigation is rendered from the settings rows, not wp_nav_menu(). Appearance → Menus and
 * the Site Editor share the edit_theme_options capability, so a customer given menus is
 * also given the Site Editor — see customer-runtime.md §5.2 for why we do not do that.
 *
 * @var array    $attributes
 * @var string   $content
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$variant  = (string) ($attributes['variant'] ?? 'inline');
$show_cta = (bool) ($attributes['showCta'] ?? true);
$nav      = fm_nav('nav_primary');

$logo_id = (int) fm_setting('logo_id');
$name    = (string) fm_setting('site_name');

// An empty business name falls back to the WordPress site title rather than rendering a
// header with no identity in it — a half-configured site should still look like a site.
if ($name === '') {
    $name = (string) get_bloginfo('name');
}

$classes = 'fm-site-header fm-site-header--' . sanitize_html_class($variant);
?>
<div <?php echo wp_kses_data(get_block_wrapper_attributes(['class' => $classes])); ?>>
  <div class="fm-site-header__inner">

    <div class="fm-site-header__brand">
      <a class="fm-site-header__home" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
        <?php if ($logo_id > 0) : ?>
          <?php
            // wp_get_attachment_image emits width and height, which the speed budget
            // requires — a logo without them shifts the whole header on load (§9).
            echo wp_get_attachment_image($logo_id, 'medium', false, [
                'class'    => 'fm-site-header__logo',
                'alt'      => esc_attr($name),
                'loading'  => 'eager',
                'decoding' => 'async',
            ]);
          ?>
        <?php else : ?>
          <span class="fm-site-header__name"><?php echo esc_html($name); ?></span>
        <?php endif; ?>
      </a>
    </div>

    <?php if ($nav !== []) : ?>
      <nav class="fm-site-header__nav" aria-label="<?php esc_attr_e('Main', 'foundations-customer'); ?>">
        <ul class="fm-nav">
          <?php foreach ($nav as $item) : ?>
            <li class="fm-nav__item<?php echo $item['children'] !== [] ? ' fm-nav__item--has-children' : ''; ?>">
              <a
                class="fm-nav__link"
                href="<?php echo esc_url($item['url']); ?>"
                <?php echo $item['current'] ? ' aria-current="page"' : ''; ?>
              ><?php echo esc_html($item['label']); ?></a>

              <?php if ($item['children'] !== []) : ?>
                <ul class="fm-nav__children">
                  <?php foreach ($item['children'] as $child) : ?>
                    <li class="fm-nav__item">
                      <a
                        class="fm-nav__link"
                        href="<?php echo esc_url($child['url']); ?>"
                        <?php echo $child['current'] ? ' aria-current="page"' : ''; ?>
                      ><?php echo esc_html($child['label']); ?></a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </nav>
    <?php endif; ?>

    <?php
    // Render the button only when it has BOTH a label and a link. A button with one of
    // the two is a dead control the customer cannot see is broken.
    if ($show_cta && fm_has_setting('cta_label') && fm_has_setting('cta_url')) :
        ?>
      <div class="fm-site-header__cta">
        <a class="fm-button" href="<?php echo esc_url((string) fm_setting('cta_url')); ?>">
          <?php echo esc_html((string) fm_setting('cta_label')); ?>
        </a>
      </div>
    <?php endif; ?>

  </div>
</div>
