<?php
/**
 * Closing CTA — the full-bleed band that ends a page.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$heading = (string) ($attributes['heading'] ?? '');
$accent  = (string) ($attributes['headingAccent'] ?? '');

if ($heading === '' && $accent === '') {
    return;
}

$lede     = (string) ($attributes['lede'] ?? '');
$footnote = (string) ($attributes['footnote'] ?? '');

$primary_text   = (string) ($attributes['primaryText'] ?? '');
$primary_url    = (string) ($attributes['primaryUrl'] ?? '');
$secondary_text = (string) ($attributes['secondaryText'] ?? '');
$secondary_url  = (string) ($attributes['secondaryUrl'] ?? '');

// Never an H1 — the hero owns that (see how-to-work.md §10).
$level = max(2, min(6, (int) ($attributes['level'] ?? 2)));
$tag   = 'h' . $level;
?>
<section <?php echo fm_wrapper(['fm-cta']); ?>>
    <<?php echo esc_attr($tag); ?> class="fm-cta__heading">
        <?php echo esc_html($heading); ?>
        <?php if ($accent !== '') : ?>
            <span class="fm-cta__heading-accent"><?php echo esc_html($accent); ?></span>
        <?php endif; ?>
    </<?php echo esc_attr($tag); ?>>

    <?php if ($lede !== '') : ?>
        <p class="fm-cta__lede"><?php echo esc_html($lede); ?></p>
    <?php endif; ?>

    <?php if ($primary_text !== '' || $secondary_text !== '') : ?>
        <p class="fm-cta__actions">
            <?php if ($primary_text !== '') : ?>
                <a class="fm-cta__button" href="<?php echo fm_url($primary_url); ?>">
                    <?php echo esc_html($primary_text); ?>
                    <span aria-hidden="true">&rarr;</span>
                </a>
            <?php endif; ?>
            <?php if ($secondary_text !== '') : ?>
                <a class="fm-cta__link" href="<?php echo fm_url($secondary_url); ?>">
                    <?php echo esc_html($secondary_text); ?>
                </a>
            <?php endif; ?>
        </p>
    <?php endif; ?>

    <?php if ($footnote !== '') : ?>
        <p class="fm-cta__footnote"><?php echo esc_html($footnote); ?></p>
    <?php endif; ?>
</section>
