<?php
/**
 * Photo banner — one full-bleed image band.
 *
 * The caption is a briefing note for whoever supplies the photo ("wide shot of a
 * practitioner's space"), so it only shows while the slot is empty. It is not alt
 * text and must never be used as such.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$media_id  = (int) ($attributes['mediaId'] ?? 0);
$media_alt = (string) ($attributes['mediaAlt'] ?? '');
$caption   = (string) ($attributes['caption'] ?? '');
$spaced    = (bool) ($attributes['spacedTop'] ?? true);

$classes = ['fm-photo-banner'];

if ($spaced) {
    $classes[] = 'fm-photo-banner--spaced';
}
?>
<div <?php echo fm_wrapper($classes); ?>>
    <?php if ($media_id > 0) : ?>
        <?php echo fm_image($media_id, 'full', ['alt' => $media_alt, 'class' => 'fm-photo-banner__img']); ?>
    <?php else : ?>
        <span class="fm-photo-banner__placeholder">
            <span class="fm-photo-banner__tag">[ photo &mdash; full bleed ]</span>
            <?php if ($caption !== '') : ?>
                <span class="fm-photo-banner__hint"><?php echo esc_html($caption); ?></span>
            <?php endif; ?>
        </span>
    <?php endif; ?>
</div>
