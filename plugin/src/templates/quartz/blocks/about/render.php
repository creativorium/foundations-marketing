<?php
/**
 * Quartz about. Paragraphs are separated by a blank line in the body field.
 */
if (!defined('ABSPATH')) {
    exit;
}

$a          = $attributes;
$text       = static fn(string $key): string => trim((string) ($a[$key] ?? ''));
$anchor     = sanitize_title($text('anchor') ?: 'about');
$paragraphs = array_filter(array_map('trim', preg_split('/\R{2,}/', $text('body'))));
$stats      = array_values(array_filter((array) ($a['stats'] ?? []), 'is_array'));
$image      = (int) ($a['imageId'] ?? 0);
?>
<section class="quartz quartz-about" id="<?php echo esc_attr($anchor); ?>">
    <figure class="quartz-about__visual">
        <?php if ($image > 0) : ?>
            <?php echo wp_get_attachment_image($image, 'full', false, ['loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(max-width: 768px) 100vw, 50vw']); ?>
        <?php endif; ?>
    </figure>
    <div class="quartz-about__content">
        <?php if ($text('eyebrow') !== '') : ?>
            <p class="quartz-eyebrow"><?php echo esc_html($text('eyebrow')); ?></p>
        <?php endif; ?>
        <h2 class="quartz-h2"><?php echo esc_html($text('heading')); ?></h2>
        <?php foreach ($paragraphs as $paragraph) : ?>
            <p><?php echo esc_html($paragraph); ?></p>
        <?php endforeach; ?>
        <?php if ($stats) : ?>
            <dl class="quartz-about__stats">
                <?php foreach ($stats as $stat) : ?>
                    <div>
                        <dt><?php echo esc_html((string) ($stat['label'] ?? '')); ?></dt>
                        <dd><?php echo esc_html((string) ($stat['number'] ?? '')); ?></dd>
                    </div>
                <?php endforeach; ?>
            </dl>
        <?php endif; ?>
    </div>
</section>
