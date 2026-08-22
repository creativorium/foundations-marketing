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
    <a class="fm-logo" href="<?php echo esc_url(home_url('/')); ?>">
        <span class="fm-logo__mark"><?php bloginfo('name'); ?></span>
        <?php if ($tagline = get_bloginfo('description')) : ?>
            <span class="fm-logo__sub"><?php echo esc_html($tagline); ?></span>
        <?php endif; ?>
    </a>

    <button class="fm-nav-toggle" type="button"
            data-fm-nav-toggle
            aria-expanded="false"
            aria-controls="fm-primary-nav">
        <?php esc_html_e('Menu', 'foundations'); ?>
    </button>

    <nav class="fm-nav" id="fm-primary-nav" data-fm-nav data-open="false"
         aria-label="<?php esc_attr_e('Primary', 'foundations'); ?>">
        <?php fm_nav_menu('primary'); ?>
    </nav>
</header>

<main id="fm-content">
