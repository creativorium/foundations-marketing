<?php
/**
 * Quartz reviews. Works as a swipeable scroll-snap row with no JavaScript; view.js adds
 * the arrows, dots and gentle autoplay the design shows.
 */
if (!defined('ABSPATH')) {
    exit;
}

$a      = $attributes;
$anchor = sanitize_title(trim((string) ($a['anchor'] ?? '')) ?: 'reviews');
$items  = array_values(array_filter((array) ($a['items'] ?? []), 'is_array'));
$count  = count($items);
$id     = 'quartz-reviews-' . $anchor;
?>
<section class="quartz quartz-reviews" id="<?php echo esc_attr($anchor); ?>">
    <header class="quartz-section-header">
        <?php if (!empty($a['eyebrow'])) : ?>
            <p class="quartz-eyebrow"><?php echo esc_html((string) $a['eyebrow']); ?></p>
        <?php endif; ?>
        <h2 class="quartz-h2"><?php echo esc_html((string) ($a['heading'] ?? '')); ?></h2>
    </header>
    <div class="quartz-slider" data-quartz-slider>
        <ul class="quartz-slider__track" id="<?php echo esc_attr($id); ?>" role="region" aria-roledescription="carousel" aria-label="<?php esc_attr_e('Client reviews', 'foundations'); ?>" tabindex="0">
            <?php foreach ($items as $index => $item) : ?>
                <li class="quartz-slide" aria-roledescription="slide" aria-label="<?php echo esc_attr(sprintf('%d of %d', $index + 1, $count)); ?>">
                    <figure>
                        <p class="quartz-slide__stars" role="img" aria-label="<?php esc_attr_e('Five out of five stars', 'foundations'); ?>">★★★★★</p>
                        <span class="quartz-slide__mark" aria-hidden="true">&ldquo;</span>
                        <blockquote><p><?php echo esc_html((string) ($item['quote'] ?? '')); ?></p></blockquote>
                        <figcaption>
                            <?php echo esc_html(implode(' · ', array_filter([(string) ($item['name'] ?? ''), (string) ($item['detail'] ?? '')]))); ?>
                        </figcaption>
                    </figure>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php if ($count > 1) : ?>
            <div class="quartz-slider__controls" hidden>
                <button class="quartz-slider__arrow" type="button" data-quartz-prev aria-controls="<?php echo esc_attr($id); ?>">
                    <span aria-hidden="true">&larr;</span><span class="screen-reader-text"><?php esc_html_e('Previous review', 'foundations'); ?></span>
                </button>
                <div class="quartz-slider__dots">
                    <?php for ($i = 0; $i < $count; $i++) : ?>
                        <button class="quartz-slider__dot" type="button" data-quartz-dot="<?php echo esc_attr((string) $i); ?>" aria-controls="<?php echo esc_attr($id); ?>"<?php echo $i === 0 ? ' aria-current="true"' : ''; ?>>
                            <span class="screen-reader-text"><?php echo esc_html(sprintf(__('Show review %d', 'foundations'), $i + 1)); ?></span>
                        </button>
                    <?php endfor; ?>
                </div>
                <button class="quartz-slider__arrow" type="button" data-quartz-next aria-controls="<?php echo esc_attr($id); ?>">
                    <span aria-hidden="true">&rarr;</span><span class="screen-reader-text"><?php esc_html_e('Next review', 'foundations'); ?></span>
                </button>
                <button class="quartz-slider__pause" type="button" data-quartz-pause aria-pressed="false"><?php esc_html_e('Pause autoplay', 'foundations'); ?></button>
            </div>
        <?php endif; ?>
    </div>
</section>
