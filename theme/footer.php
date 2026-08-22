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

    <?php
    /*
     * Decorative, and the brand name is already in the copyright line below, so it is
     * hidden from assistive technology rather than read out a second time.
     *
     * textLength pins the text to the full viewBox width and lengthAdjust spreads the
     * change across the letter spacing, so the word always spans gutter to gutter
     * without ever overflowing — which a vw font-size cannot guarantee.
     */
    $wordmark = fm_brand_mark();
    ?>
    <div class="fm-footer__wordmark" aria-hidden="true">
        <svg viewBox="0 0 1000 112" preserveAspectRatio="xMidYMid meet" focusable="false" role="presentation">
            <text x="0" y="100" textLength="1000" lengthAdjust="spacingAndGlyphs" font-size="140"><?php echo esc_html($wordmark); ?></text>
        </svg>
    </div>

    <div class="fm-footer__meta">
        <span><?php esc_html_e('Made by Cular Creative', 'foundations'); ?></span>
        <span>&copy; <?php echo esc_html((string) date('Y')); ?> <?php echo esc_html(FM_BRAND); ?></span>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
