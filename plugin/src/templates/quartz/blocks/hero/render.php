<?php
/**
 * Quartz hero. Owns the page H1; the photograph is the LCP, so it loads eagerly.
 */
if (!defined('ABSPATH')) {
    exit;
}

$a     = $attributes;
$text  = static fn(string $key): string => trim((string) ($a[$key] ?? ''));
$image = (int) ($a['imageId'] ?? 0);
?>
<section class="quartz quartz-hero" id="top">
    <div class="quartz-hero__text">
        <?php if ($text('eyebrow') !== '') : ?>
            <p class="quartz-eyebrow"><?php echo esc_html($text('eyebrow')); ?></p>
        <?php endif; ?>
        <h1>
            <?php echo esc_html($text('heading')); ?>
            <?php if ($text('highlight') !== '') : ?><em><?php echo esc_html($text('highlight')); ?></em><?php endif; ?>
            <?php echo esc_html($text('headingEnd')); ?>
        </h1>
        <?php if ($text('body') !== '') : ?>
            <p class="quartz-hero__sub"><?php echo esc_html($text('body')); ?></p>
        <?php endif; ?>
        <div class="quartz-hero__actions">
            <?php if ($text('primaryLabel') !== '') : ?>
                <a class="quartz-button quartz-button--fill" href="<?php echo esc_url($text('primaryUrl') ?: '#book'); ?>"><?php echo esc_html($text('primaryLabel')); ?></a>
            <?php endif; ?>
            <?php if ($text('secondaryLabel') !== '') : ?>
                <a class="quartz-button quartz-button--ghost" href="<?php echo esc_url($text('secondaryUrl') ?: '#results'); ?>"><?php echo esc_html($text('secondaryLabel')); ?></a>
            <?php endif; ?>
        </div>
    </div>
    <div class="quartz-hero__visual">
        <?php if ($image > 0) : ?>
            <?php echo wp_get_attachment_image($image, 'full', false, ['class' => 'quartz-hero__photo', 'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async', 'sizes' => '(max-width: 768px) 100vw, 50vw']); ?>
        <?php endif; ?>
        <?php if ($text('badgeNumber') !== '') : ?>
            <p class="quartz-hero__badge">
                <span class="quartz-hero__badge-number"><?php echo esc_html($text('badgeNumber')); ?></span>
                <span class="quartz-hero__badge-label"><?php echo esc_html($text('badgeLabel')); ?></span>
            </p>
        <?php endif; ?>
    </div>
</section>
