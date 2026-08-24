<?php
/**
 * Add-ons — priced cards, each with what it does and when it is worth buying.
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
$note    = (string) ($attributes['note'] ?? '');
?>
<section <?php echo fm_wrapper(['fm-addons']); ?>>
    <?php if ($number !== '' || $label !== '') : ?>
        <?php echo fm_section_rule($number, $label, $aside); ?>
    <?php endif; ?>

    <?php if ($heading !== '' || $intro !== '') : ?>
        <div class="fm-addons__intro">
            <?php if ($heading !== '') : ?>
                <h2 class="fm-addons__heading"><?php echo esc_html($heading); ?></h2>
            <?php endif; ?>
            <?php if ($intro !== '') : ?>
                <p class="fm-addons__lede"><?php echo esc_html($intro); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <ul class="fm-addons__list">
        <?php foreach ($items as $i => $item) : ?>
            <?php
            $name = (string) ($item['name'] ?? '');

            if ($name === '') {
                continue;
            }

            // Cards cycle through the four tints, as the canvas has them.
            $tint = 'var(--fm-tint-' . (($i % 4) + 1) . ')';
            ?>
            <li class="fm-addons__item" style="--fm-addon-tint: <?php echo esc_attr($tint); ?>">
                <div class="fm-addons__head">
                    <h3 class="fm-addons__name"><?php echo esc_html($name); ?></h3>
                    <span class="fm-addons__price"><?php echo esc_html((string) ($item['price'] ?? '')); ?></span>
                </div>

                <?php if ((string) ($item['body'] ?? '') !== '') : ?>
                    <p class="fm-addons__body"><?php echo esc_html((string) $item['body']); ?></p>
                <?php endif; ?>

                <?php if ((string) ($item['when'] ?? '') !== '') : ?>
                    <p class="fm-addons__when"><?php echo esc_html((string) $item['when']); ?></p>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if ($note !== '') : ?>
        <p class="fm-addons__note"><?php echo esc_html($note); ?></p>
    <?php endif; ?>
</section>
