<?php
/**
 * Feature list — numbered items divided by hairline rules, with an optional price bar.
 *
 * An <ol>: the items are numbered on screen, so the order is part of the content and
 * belongs in the markup. The visible numerals are aria-hidden because the list already
 * conveys position.
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

$number  = (string) ($attributes['number'] ?? '');
$label   = (string) ($attributes['label'] ?? '');
$aside   = (string) ($attributes['aside'] ?? '');
$heading = (string) ($attributes['heading'] ?? '');
$intro   = (string) ($attributes['intro'] ?? '');
$tone    = (string) ($attributes['tone'] ?? 'warm');

$show_price   = (bool) ($attributes['showPrice'] ?? true);
$price        = (string) ($attributes['price'] ?? '');
$price_prefix = (string) ($attributes['pricePrefix'] ?? '');
$price_note   = (string) ($attributes['priceNote'] ?? '');
$cta_text     = (string) ($attributes['ctaText'] ?? '');
$cta_url      = (string) ($attributes['ctaUrl'] ?? '');
?>
<section <?php echo fm_wrapper(['fm-features', 'fm-features--' . sanitize_html_class($tone)]); ?>>
    <?php if ($number !== '' || $label !== '') : ?>
        <?php echo fm_section_rule($number, $label, $aside); ?>
    <?php endif; ?>

    <?php if ($heading !== '' || $intro !== '') : ?>
        <div class="fm-features__intro">
            <?php if ($heading !== '') : ?>
                <h2 class="fm-features__heading"><?php echo esc_html($heading); ?></h2>
            <?php endif; ?>
            <?php if ($intro !== '') : ?>
                <p class="fm-features__lede"><?php echo esc_html($intro); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <ol class="fm-features__list">
        <?php foreach ($items as $item) : ?>
            <li class="fm-features__item">
                <?php if ((string) ($item['n'] ?? '') !== '') : ?>
                    <span class="fm-features__n" aria-hidden="true"><?php echo esc_html((string) $item['n']); ?></span>
                <?php endif; ?>
                <?php if ((string) ($item['title'] ?? '') !== '') : ?>
                    <h3 class="fm-features__title"><?php echo esc_html((string) $item['title']); ?></h3>
                <?php endif; ?>
                <?php if ((string) ($item['body'] ?? '') !== '') : ?>
                    <p class="fm-features__body"><?php echo esc_html((string) $item['body']); ?></p>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>

    <?php if ($show_price && $price !== '') : ?>
        <div class="fm-features__price">
            <p class="fm-features__figure">
                <?php if ($price_prefix !== '') : ?>
                    <span class="fm-features__price-prefix"><?php echo esc_html($price_prefix); ?></span>
                <?php endif; ?>
                <span class="fm-features__amount"><?php echo esc_html($price); ?></span>
                <?php if ($price_note !== '') : ?>
                    <span class="fm-features__price-note"><?php echo esc_html($price_note); ?></span>
                <?php endif; ?>
            </p>

            <?php if ($cta_text !== '') : ?>
                <a class="fm-features__cta" href="<?php echo fm_url($cta_url); ?>">
                    <?php echo esc_html($cta_text); ?>
                    <span aria-hidden="true">&rarr;</span>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>
