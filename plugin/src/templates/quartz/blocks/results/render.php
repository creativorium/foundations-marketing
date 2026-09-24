<?php
/**
 * Quartz before & after. One <figure> per card; the divider is decorative.
 */
if (!defined('ABSPATH')) {
    exit;
}

$a      = $attributes;
$anchor = sanitize_title(trim((string) ($a['anchor'] ?? '')) ?: 'results');
$pairs  = array_values(array_filter((array) ($a['pairs'] ?? []), 'is_array'));

$card = static function (array $pair, string $side): void {
    $image = (int) ($pair[$side . 'ImageId'] ?? 0);
    ?>
    <figure class="quartz-results__card quartz-results__card--<?php echo esc_attr($side); ?>">
        <?php if ($image > 0) : ?>
            <?php echo wp_get_attachment_image($image, 'large', false, ['loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(max-width: 768px) 100vw, 430px']); ?>
        <?php endif; ?>
        <figcaption>
            <span class="quartz-results__stage"><?php echo esc_html((string) ($pair[$side . 'Stage'] ?? '')); ?></span>
            <span class="quartz-results__title"><?php echo esc_html((string) ($pair[$side . 'Title'] ?? '')); ?></span>
        </figcaption>
    </figure>
    <?php
};
?>
<section class="quartz quartz-results" id="<?php echo esc_attr($anchor); ?>">
    <header class="quartz-section-header">
        <?php if (!empty($a['eyebrow'])) : ?>
            <p class="quartz-eyebrow"><?php echo esc_html((string) $a['eyebrow']); ?></p>
        <?php endif; ?>
        <h2 class="quartz-h2"><?php echo esc_html((string) ($a['heading'] ?? '')); ?></h2>
    </header>
    <div class="quartz-results__grid">
        <?php foreach ($pairs as $index => $pair) : ?>
            <?php if ($index > 0 && !empty($pair['divider'])) : ?>
                <div class="quartz-results__divider" aria-hidden="true"><span><?php echo esc_html((string) $pair['divider']); ?></span></div>
            <?php endif; ?>
            <?php $card($pair, 'before'); ?>
            <?php $card($pair, 'after'); ?>
        <?php endforeach; ?>
    </div>
</section>
