<?php
/**
 * Photo strip — a full-bleed row of images divided by the hairline rule.
 *
 * Below the fold on every page it appears on, so images stay lazy. Each needs its own
 * alt text; a photo with no alt is rendered decorative (alt="") rather than having the
 * filename read out, which is worse than nothing.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$items = is_array($attributes['items'] ?? null) ? $attributes['items'] : [];

if ($items === []) {
    return;
}
?>
<div <?php echo fm_wrapper(['fm-photo-strip']); ?>>
    <?php foreach ($items as $item) : ?>
        <?php
        $id      = (int) ($item['id'] ?? 0);
        $alt     = (string) ($item['alt'] ?? '');
        $caption = (string) ($item['caption'] ?? '');
        ?>
        <figure class="fm-photo-strip__cell">
            <?php if ($id > 0) : ?>
                <?php echo fm_image($id, 'large', ['alt' => $alt, 'class' => 'fm-photo-strip__img']); ?>
            <?php else : ?>
                <span class="fm-photo-strip__placeholder">
                    <span class="fm-photo-strip__tag">[ photo ]</span>
                    <?php if ($caption !== '') : ?>
                        <span class="fm-photo-strip__hint"><?php echo esc_html($caption); ?></span>
                    <?php endif; ?>
                </span>
            <?php endif; ?>
        </figure>
    <?php endforeach; ?>
</div>
