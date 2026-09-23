<?php
/**
 * Quartz privacy policy. Paragraphs inside a section are separated by a blank line.
 */
if (!defined('ABSPATH')) {
    exit;
}

$a        = $attributes;
$text     = static fn(string $key): string => trim((string) ($a[$key] ?? ''));
$sections = array_values(array_filter((array) ($a['sections'] ?? []), 'is_array'));
?>
<article class="quartz quartz-policy">
    <header class="quartz-policy__header">
        <?php if ($text('eyebrow') !== '') : ?>
            <p class="quartz-eyebrow"><?php echo esc_html($text('eyebrow')); ?></p>
        <?php endif; ?>
        <h1><?php echo esc_html($text('heading')); ?></h1>
        <?php if ($text('intro') !== '') : ?>
            <p class="quartz-policy__intro"><?php echo esc_html($text('intro')); ?></p>
        <?php endif; ?>
        <?php if ($text('updated') !== '') : ?>
            <p class="quartz-policy__updated"><?php echo esc_html($text('updated')); ?></p>
        <?php endif; ?>
    </header>
    <?php foreach ($sections as $section) : ?>
        <section class="quartz-policy__section">
            <h2><?php echo esc_html((string) ($section['title'] ?? '')); ?></h2>
            <?php foreach (array_filter(array_map('trim', preg_split('/\R{2,}/', (string) ($section['body'] ?? '')))) as $paragraph) : ?>
                <p><?php echo esc_html($paragraph); ?></p>
            <?php endforeach; ?>
        </section>
    <?php endforeach; ?>
</article>
