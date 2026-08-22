<?php
/**
 * Section heading — the "06 — Questions / Before you start" rule plus the display
 * heading beneath it.
 *
 * SEO: `level` exists so a page has exactly one H1 (strategy §4). The hero block
 * renders the H1; every other section should stay at H2 or lower. The editor warns
 * when a second H1 is placed on a page.
 *
 * @var array    $attributes
 * @var string   $content
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$number    = (string) ($attributes['number'] ?? '');
$label     = (string) ($attributes['label'] ?? '');
$aside     = (string) ($attributes['aside'] ?? '');
$heading   = (string) ($attributes['heading'] ?? '');
$highlight = (string) ($attributes['highlight'] ?? '');
$tone      = (string) ($attributes['tone'] ?? 'bg');

// Clamp to a real heading level; never emit <h0> or <h7>.
$level = (int) ($attributes['level'] ?? 2);
$level = max(1, min(6, $level));
$tag   = 'h' . $level;

if ($heading === '' && $label === '') {
    return;
}

$classes = ['fm-section-heading', 'fm-section-heading--' . sanitize_html_class($tone)];
?>
<section <?php echo fm_wrapper($classes); ?>>
    <?php if ($number !== '' || $label !== '') : ?>
        <?php echo fm_section_rule($number, $label, $aside); ?>
    <?php endif; ?>

    <?php if ($heading !== '') : ?>
        <<?php echo esc_attr($tag); ?> class="fm-section-heading__title">
            <?php echo esc_html($heading); ?>
            <?php if ($highlight !== '') : ?>
                <span class="fm-section-heading__highlight"><?php echo esc_html($highlight); ?></span>
            <?php endif; ?>
        </<?php echo esc_attr($tag); ?>>
    <?php endif; ?>

    <?php echo $content; // Inner blocks, already escaped by their own render. ?>
</section>
