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

<?php
/*
 * The page-transition curtain: a grid of solid cells covering the viewport, which
 * wipes away column by column as the page loads. It is markup rather than a CSS mask
 * because the effect needs each cell timed separately, and one mask cannot carry
 * sixty-six different delays.
 *
 * Purely decorative and never interactive — aria-hidden, and the container is
 * pointer-events:none, so it cannot swallow a tap even mid-animation.
 *
 * Without the stylesheet these divs have no size, no background and no position, so
 * a CSS failure leaves nothing on screen rather than a black box over the site.
 */
$fm_px_rows = 6;
$fm_px_cols = 11;
?>
<div class="fm-px" aria-hidden="true"><?php
    echo str_repeat('<div class="fm-px__cell"></div>', $fm_px_rows * $fm_px_cols);
?></div>

<a class="fm-skip-link" href="#fm-content"><?php esc_html_e('Skip to content', 'foundations-marketing'); ?></a>

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
        <span class="fm-nav-toggle__label fm-nav-toggle__label--open"><?php esc_html_e('Menu', 'foundations-marketing'); ?></span>
        <span class="fm-nav-toggle__label fm-nav-toggle__label--close" aria-hidden="true"><?php esc_html_e('Close', 'foundations-marketing'); ?></span>
    </button>

    <nav class="fm-nav" id="fm-primary-nav" data-fm-nav data-open="false"
         aria-label="<?php esc_attr_e('Primary', 'foundations-marketing'); ?>">

        <?php /*
         * The drawer covers the header on mobile, so it carries its own wordmark —
         * the header's is hidden while it is open (styles/_header.scss), and only
         * ever one of the two is in the accessibility tree at a time.
         *
         * Set on two lines rather than one because the lockup is ~270px wide and the
         * drawer's content box is narrower than that on a small phone, where it would
         * otherwise be clipped by the Close button.
         *
         * The whole head is display:none above the drawer breakpoint: on desktop the
         * real header logo is right there and this would be a duplicate link.
         */ ?>
        <div class="fm-nav__head">
            <a class="fm-nav__brand" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                <span class="fm-nav__brand-line"><?php echo esc_html($brand_mark); ?></span>
                <?php if ($brand_suffix !== '') : ?>
                    <span class="fm-nav__brand-line"><?php echo esc_html($brand_suffix); ?></span>
                <?php endif; ?>
            </a>
        </div>

        <?php fm_nav_menu('primary'); ?>

        <?php
        // Whatever the footer menu carries that the drawer does not already list —
        // Privacy, Terms and the like. Nothing renders if there is no such item.
        $fm_secondary = fm_drawer_secondary_items();
        ?>
        <?php if ($fm_secondary !== []) : ?>
            <div class="fm-nav__foot">
                <ul class="fm-nav__secondary">
                    <?php foreach ($fm_secondary as $fm_item) : ?>
                        <li>
                            <a href="<?php echo esc_url((string) $fm_item->url); ?>">
                                <?php echo esc_html((string) $fm_item->title); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p class="fm-nav__legal">
                    &copy; <?php echo esc_html((string) date('Y')); ?> <?php echo esc_html(FM_BRAND); ?>
                </p>
            </div>
        <?php endif; ?>
    </nav>
</header>

<?php // The drawer's backdrop. Kept outside <header> so it can cover the page without
      // inheriting the header's flex row. Hidden entirely above the drawer breakpoint. ?>
<div class="fm-nav-scrim" data-fm-nav-scrim data-open="false"></div>

<main id="fm-content">
