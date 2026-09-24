<?php
/**
 * Quartz treatments. The card row scrolls inside its own region so the page body never
 * scrolls sideways (how-to-work.md §8); the region is focusable so keyboards can scroll it.
 */
if (!defined('ABSPATH')) {
    exit;
}

$a       = $attributes;
$anchor  = sanitize_title(trim((string) ($a['anchor'] ?? '')) ?: 'services');
$items   = array_values(array_filter((array) ($a['items'] ?? []), 'is_array'));
$heading = (string) ($a['heading'] ?? '');
?>
<section class="quartz quartz-treatments" id="<?php echo esc_attr($anchor); ?>">
    <header class="quartz-section-header">
        <?php if (!empty($a['eyebrow'])) : ?>
            <p class="quartz-eyebrow"><?php echo esc_html((string) $a['eyebrow']); ?></p>
        <?php endif; ?>
        <h2 class="quartz-h2"><?php echo esc_html($heading); ?></h2>
    </header>
    <div class="quartz-treatments__track" role="region" tabindex="0" aria-label="<?php echo esc_attr($heading ?: __('Treatments', 'foundations')); ?>">
        <ol class="quartz-treatments__list">
            <?php foreach ($items as $index => $item) : ?>
                <li class="quartz-treatments__card">
                    <span class="quartz-treatments__number" aria-hidden="true"><?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                    <h3><?php echo esc_html((string) ($item['title'] ?? '')); ?></h3>
                    <p><?php echo esc_html((string) ($item['body'] ?? '')); ?></p>
                    <p class="quartz-treatments__meta">
                        <span class="quartz-treatments__price"><?php echo esc_html((string) ($item['price'] ?? '')); ?></span>
                        <span class="quartz-treatments__duration"><?php echo esc_html((string) ($item['duration'] ?? '')); ?></span>
                    </p>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>
