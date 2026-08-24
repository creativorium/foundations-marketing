<?php
/**
 * Split list — the two facing cards that set out who does what.
 *
 * Both columns are real <ul> elements. The "+" and arrow markers are decorative and
 * aria-hidden: the list already conveys that these are items, and a screen reader
 * reading "plus" before every line is noise.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$left  = is_array($attributes['leftItems'] ?? null) ? $attributes['leftItems'] : [];
$right = is_array($attributes['rightItems'] ?? null) ? $attributes['rightItems'] : [];

if ($left === [] && $right === []) {
    return;
}

$number  = (string) ($attributes['number'] ?? '');
$label   = (string) ($attributes['label'] ?? '');
$aside   = (string) ($attributes['aside'] ?? '');
$heading = (string) ($attributes['heading'] ?? '');
$intro   = (string) ($attributes['intro'] ?? '');

/**
 * One card. Both sides share everything except palette and marker, so they share
 * a renderer too — two near-identical blocks of markup would drift apart.
 */
$render_card = static function (
    string $side,
    string $eyebrow,
    string $title,
    array $items,
    string $marker,
    string $note
): void {
    if ($items === [] && $title === '') {
        return;
    }
    ?>
    <div class="fm-split-list__card fm-split-list__card--<?php echo esc_attr($side); ?>">
        <?php if ($eyebrow !== '') : ?>
            <p class="fm-split-list__eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <?php endif; ?>

        <?php if ($title !== '') : ?>
            <h3 class="fm-split-list__title"><?php echo esc_html($title); ?></h3>
        <?php endif; ?>

        <?php if ($items !== []) : ?>
            <ul class="fm-split-list__items">
                <?php foreach ($items as $item) : ?>
                    <?php $text = is_array($item) ? (string) ($item['text'] ?? '') : (string) $item; ?>
                    <?php if ($text === '') { continue; } ?>
                    <li class="fm-split-list__item">
                        <span class="fm-split-list__marker" aria-hidden="true"><?php echo esc_html($marker); ?></span>
                        <span><?php echo esc_html($text); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if ($note !== '') : ?>
            <div class="fm-split-list__note">
                <p><?php echo esc_html($note); ?></p>
            </div>
        <?php endif; ?>
    </div>
    <?php
};
?>
<section <?php echo fm_wrapper(['fm-split-list']); ?>>
    <?php if ($number !== '' || $label !== '') : ?>
        <?php echo fm_section_rule($number, $label, $aside); ?>
    <?php endif; ?>

    <?php if ($heading !== '' || $intro !== '') : ?>
        <div class="fm-split-list__intro">
            <?php if ($heading !== '') : ?>
                <h2 class="fm-split-list__heading"><?php echo esc_html($heading); ?></h2>
            <?php endif; ?>
            <?php if ($intro !== '') : ?>
                <p class="fm-split-list__lede"><?php echo esc_html($intro); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="fm-split-list__cards">
        <?php
        $render_card(
            'left',
            (string) ($attributes['leftEyebrow'] ?? ''),
            (string) ($attributes['leftTitle'] ?? ''),
            $left,
            '+',
            ''
        );

        $render_card(
            'right',
            (string) ($attributes['rightEyebrow'] ?? ''),
            (string) ($attributes['rightTitle'] ?? ''),
            $right,
            '→',
            (string) ($attributes['rightNote'] ?? '')
        );
        ?>
    </div>
</section>
