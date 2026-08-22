<?php
/**
 * Benefits — numbered reasons, two columns per row.
 *
 * An ordered list again: the numbering is meaningful, so it belongs in the markup rather
 * than being painted on with CSS. The visible numeral is aria-hidden because <ol> already
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
?>
<section <?php echo fm_wrapper(['fm-benefits']); ?>>
    <?php if ($number !== '' || $label !== '') : ?>
        <?php echo fm_section_rule($number, $label, $aside); ?>
    <?php endif; ?>

    <?php if ($heading !== '') : ?>
        <h2 class="fm-benefits__heading"><?php echo esc_html($heading); ?></h2>
    <?php endif; ?>

    <ol class="fm-benefits__list">
        <?php foreach ($items as $item) : ?>
            <li class="fm-benefits__item">
                <?php if (($item['n'] ?? '') !== '') : ?>
                    <span class="fm-benefits__n" aria-hidden="true"><?php echo esc_html((string) $item['n']); ?></span>
                <?php endif; ?>
                <div class="fm-benefits__body">
                    <?php if (($item['title'] ?? '') !== '') : ?>
                        <h3 class="fm-benefits__title"><?php echo esc_html((string) $item['title']); ?></h3>
                    <?php endif; ?>
                    <?php if (($item['body'] ?? '') !== '') : ?>
                        <p><?php echo esc_html((string) $item['body']); ?></p>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ol>
</section>
