<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}
?>
</main>

<footer class="fm-footer">
    <?php if (has_nav_menu('footer')) : ?>
        <div class="fm-footer__links">
            <?php fm_nav_menu('footer'); ?>
        </div>
    <?php endif; ?>

    <div class="fm-footer__wordmark" aria-hidden="true"><?php echo esc_html(fm_brand_mark()); ?></div>

    <div class="fm-footer__meta">
        <span><?php esc_html_e('Made by Cular Creative', 'foundations'); ?></span>
        <span>&copy; <?php echo esc_html((string) date('Y')); ?> <?php echo esc_html(FM_BRAND); ?></span>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
