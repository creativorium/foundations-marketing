<?php
/**
 * Hero — the page opener.
 *
 * SEO: this block renders the page's single H1 (strategy §4). `multiple: false` in
 * block.json stops an editor adding a second hero, which is the usual way a page ends
 * up with two H1s.
 *
 * Performance: the device screenshot is the LCP element on every page that has one, so
 * it is rendered eager with fetchpriority="high" — never lazy. See fm_image().
 *
 * Two layouts from the client's canvas:
 *   a — split: copy left, device right (default)
 *   b — centred: oversized accent heading, device beneath
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$variant   = ($attributes['variant'] ?? 'a') === 'b' ? 'b' : 'a';
$badge     = (string) ($attributes['badge'] ?? '');
$note      = (string) ($attributes['note'] ?? '');
$heading   = (string) ($attributes['heading'] ?? '');
$accent    = (string) ($attributes['headingAccent'] ?? '');
$lede      = (string) ($attributes['lede'] ?? '');
$footnote  = (string) ($attributes['footnote'] ?? '');
$media_id  = (int) ($attributes['mediaId'] ?? 0);
$media_alt = (string) ($attributes['mediaAlt'] ?? '');
$cap_left  = (string) ($attributes['captionLeft'] ?? '');
$cap_right = (string) ($attributes['captionRight'] ?? '');

$primary_text   = (string) ($attributes['primaryText'] ?? '');
$primary_url    = (string) ($attributes['primaryUrl'] ?? '');
$secondary_text = (string) ($attributes['secondaryText'] ?? '');
$secondary_url  = (string) ($attributes['secondaryUrl'] ?? '');

if ($heading === '' && $accent === '') {
    return;
}
?>
<section <?php echo fm_wrapper(['fm-hero', 'fm-hero--' . $variant]); ?>>
    <div class="fm-hero__inner">

        <div class="fm-hero__copy">
            <?php if ($badge !== '' || $note !== '') : ?>
                <p class="fm-hero__flags">
                    <?php if ($badge !== '') : ?>
                        <span class="fm-hero__badge"><?php echo esc_html($badge); ?></span>
                    <?php endif; ?>
                    <?php if ($note !== '') : ?>
                        <span class="fm-hero__note"><?php echo esc_html($note); ?></span>
                    <?php endif; ?>
                </p>
            <?php endif; ?>

            <h1 class="fm-hero__title">
                <?php echo esc_html($heading); ?>
                <?php if ($accent !== '') : ?>
                    <span class="fm-hero__title-accent"><?php echo esc_html($accent); ?></span>
                <?php endif; ?>
            </h1>

            <?php if ($lede !== '') : ?>
                <p class="fm-hero__lede"><?php echo esc_html($lede); ?></p>
            <?php endif; ?>

            <?php if ($primary_text !== '' || $secondary_text !== '') : ?>
                <p class="fm-hero__actions">
                    <?php if ($primary_text !== '') : ?>
                        <a class="fm-hero__cta" href="<?php echo fm_url($primary_url); ?>">
                            <?php echo esc_html($primary_text); ?>
                            <span aria-hidden="true">&rarr;</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($secondary_text !== '') : ?>
                        <a class="fm-hero__link" href="<?php echo fm_url($secondary_url); ?>">
                            <?php echo esc_html($secondary_text); ?>
                        </a>
                    <?php endif; ?>
                </p>
            <?php endif; ?>

            <?php if ($footnote !== '') : ?>
                <p class="fm-hero__footnote"><?php echo esc_html($footnote); ?></p>
            <?php endif; ?>
        </div>

        <div class="fm-hero__media">
            <div class="fm-hero__device">
                <span class="fm-hero__device-dot" aria-hidden="true"></span>
                <div class="fm-hero__screen">
                    <?php if ($media_id > 0) : ?>
                        <?php
                        // Above the fold: eager + fetchpriority="high". This is the LCP.
                        echo fm_image($media_id, 'large', ['alt' => $media_alt, 'class' => 'fm-hero__shot'], true);
                        ?>
                    <?php else : ?>
                        <?php
                        /*
                         * No screenshot chosen yet. Render the canvas's wireframe
                         * placeholder so the layout still reads, and keep it out of the
                         * accessibility tree — it carries no information.
                         */
                        ?>
                        <div class="fm-hero__skeleton" aria-hidden="true">
                            <div class="fm-hero__skeleton-bar">
                                <i style="width:40px;height:8px"></i>
                                <i style="width:18px;height:4px;margin-left:auto"></i>
                                <i style="width:18px;height:4px"></i>
                                <i style="width:32px;height:12px;border-radius:20px"></i>
                            </div>
                            <div class="fm-hero__skeleton-copy">
                                <i style="width:86%;height:16px"></i>
                                <i style="width:60%;height:16px"></i>
                                <i style="width:70%;height:6px;margin-top:5px"></i>
                                <i style="width:52%;height:6px"></i>
                                <i style="width:74px;height:19px;border-radius:20px;margin-top:6px"></i>
                            </div>
                            <div class="fm-hero__skeleton-fill"></div>
                            <div class="fm-hero__skeleton-feet">
                                <i></i><i></i>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($cap_left !== '' || $cap_right !== '') : ?>
                <p class="fm-hero__caption">
                    <span><?php echo esc_html($cap_left); ?></span>
                    <?php if ($cap_right !== '') : ?>
                        <span class="fm-hero__caption-accent"><?php echo esc_html($cap_right); ?></span>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </div>

    </div>
</section>
