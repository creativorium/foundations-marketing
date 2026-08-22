<?php
/**
 * Pricing band — one headline number, what is included, and the add-ons.
 *
 * The two lists are real <ul> elements so a screen reader announces "list, 6 items"
 * rather than reading a wall of divs. The "+" bullets are decorative and aria-hidden.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$price = (string) ($attributes['price'] ?? '');

if ($price === '') {
    return;
}

$number     = (string) ($attributes['number'] ?? '');
$label      = (string) ($attributes['label'] ?? '');
$aside      = (string) ($attributes['aside'] ?? '');
$prefix     = (string) ($attributes['prefix'] ?? '');
$lede       = (string) ($attributes['lede'] ?? '');
$cta_text   = (string) ($attributes['ctaText'] ?? '');
$cta_url    = (string) ($attributes['ctaUrl'] ?? '');
$smallprint = (string) ($attributes['smallprint'] ?? '');

$included_title = (string) ($attributes['includedTitle'] ?? '');
$addons_title   = (string) ($attributes['addonsTitle'] ?? '');
$addons_note    = (string) ($attributes['addonsNote'] ?? '');

$included = is_array($attributes['included'] ?? null) ? $attributes['included'] : [];
$addons   = is_array($attributes['addons'] ?? null) ? $attributes['addons'] : [];
?>
<section <?php echo fm_wrapper(['fm-pricing']); ?>>
    <?php if ($number !== '' || $label !== '') : ?>
        <?php echo fm_section_rule($number, $label, $aside); ?>
    <?php endif; ?>

    <div class="fm-pricing__grid">
        <div class="fm-pricing__headline">
            <p class="fm-pricing__figure">
                <?php if ($prefix !== '') : ?>
                    <span class="fm-pricing__prefix"><?php echo esc_html($prefix); ?></span>
                <?php endif; ?>
                <span class="fm-pricing__amount"><?php echo esc_html($price); ?></span>
            </p>

            <?php if ($lede !== '') : ?>
                <p class="fm-pricing__lede"><?php echo esc_html($lede); ?></p>
            <?php endif; ?>

            <?php if ($cta_text !== '') : ?>
                <a class="fm-pricing__cta" href="<?php echo fm_url($cta_url); ?>">
                    <?php echo esc_html($cta_text); ?>
                    <span aria-hidden="true">&rarr;</span>
                </a>
            <?php endif; ?>

            <?php if ($smallprint !== '') : ?>
                <p class="fm-pricing__smallprint"><?php echo esc_html($smallprint); ?></p>
            <?php endif; ?>
        </div>

        <div class="fm-pricing__cards">
            <?php if ($included !== []) : ?>
                <div class="fm-pricing__card fm-pricing__card--included">
                    <h3 class="fm-pricing__card-title"><?php echo esc_html($included_title); ?></h3>
                    <ul class="fm-pricing__list">
                        <?php foreach ($included as $item) : ?>
                            <?php $text = is_array($item) ? (string) ($item['text'] ?? '') : (string) $item; ?>
                            <?php if ($text === '') { continue; } ?>
                            <li class="fm-pricing__row">
                                <span class="fm-pricing__plus" aria-hidden="true">+</span>
                                <span><?php echo esc_html($text); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($addons !== []) : ?>
                <div class="fm-pricing__card fm-pricing__card--addons">
                    <h3 class="fm-pricing__card-title"><?php echo esc_html($addons_title); ?></h3>
                    <ul class="fm-pricing__list">
                        <?php foreach ($addons as $addon) : ?>
                            <?php
                            $name  = (string) ($addon['name'] ?? '');
                            $value = (string) ($addon['price'] ?? '');
                            ?>
                            <?php if ($name === '') { continue; } ?>
                            <li class="fm-pricing__row fm-pricing__row--split">
                                <span><?php echo esc_html($name); ?></span>
                                <span class="fm-pricing__addon-price"><?php echo esc_html($value); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <?php if ($addons_note !== '') : ?>
                        <p class="fm-pricing__note"><?php echo esc_html($addons_note); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
