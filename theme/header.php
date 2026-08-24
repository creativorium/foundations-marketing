<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="fm-skip-link" href="#fm-content"><?php esc_html_e('Skip to content', 'foundations'); ?></a>

<header class="fm-header">
    <?php [$brand_mark, $brand_suffix] = fm_brand_parts(); ?>
    <a class="fm-logo" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
        <span class="fm-logo__mark"><?php echo esc_html($brand_mark); ?></span>
        <?php if ($brand_suffix !== '') : ?>
            <span class="fm-logo__sub"><?php echo esc_html($brand_suffix); ?></span>
        <?php endif; ?>
    </a>

    <?php // Two labels stacked in one grid cell: the button keeps the width of the
          // longer word, so opening the drawer never nudges the header layout. ?>
    <button class="fm-nav-toggle" type="button"
            data-fm-nav-toggle
            aria-expanded="false"
            aria-controls="fm-primary-nav">
        <span class="fm-nav-toggle__label fm-nav-toggle__label--open"><?php esc_html_e('Menu', 'foundations'); ?></span>
        <span class="fm-nav-toggle__label fm-nav-toggle__label--close" aria-hidden="true"><?php esc_html_e('Close', 'foundations'); ?></span>
    </button>

    <nav class="fm-nav" id="fm-primary-nav" data-fm-nav data-open="false"
         aria-label="<?php esc_attr_e('Primary', 'foundations'); ?>">
        <?php fm_nav_menu('primary'); ?>
    </nav>
</header>

<?php // The drawer's backdrop. Kept outside <header> so it can cover the page without
      // inheriting the header's flex row. Hidden entirely above the drawer breakpoint. ?>
<div class="fm-nav-scrim" data-fm-nav-scrim data-open="false"></div>

<main id="fm-content">
