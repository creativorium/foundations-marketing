<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<section class="fm-gutter" style="padding-block: 120px 140px; text-align: center;">
    <p class="fm-eyebrow"><?php esc_html_e('404', 'foundations'); ?></p>
    <h1 style="font-size: clamp(42px, 6.6vw, 96px); text-transform: uppercase; margin: 18px 0 26px;">
        <?php esc_html_e('That page has moved on', 'foundations'); ?>
    </h1>
    <a class="fm-btn fm-btn--solid" href="<?php echo esc_url(home_url('/')); ?>">
        <?php esc_html_e('Back to the homepage', 'foundations'); ?>
    </a>
</section>
<?php
get_footer();
