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
     *
     * Each letter is its own <tspan> so they can be revealed one after another as the
     * footer scrolls into view. The tspans carry no x/y of their own, so they still
     * flow inside the fitted <text> and the sizing is unaffected.
     */
    $wordmark = fm_brand_mark();
    $letters  = preg_split('//u', $wordmark, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    ?>
    <div class="fm-footer__wordmark" aria-hidden="true">
        <svg viewBox="0 0 1000 112" preserveAspectRatio="xMidYMid meet" focusable="false" role="presentation">
            <text x="0" y="100" textLength="1000" lengthAdjust="spacingAndGlyphs" font-size="140"><?php
                foreach ($letters as $letter) {
                    printf('<tspan>%s</tspan>', esc_html($letter));
                }
            ?></text>
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
