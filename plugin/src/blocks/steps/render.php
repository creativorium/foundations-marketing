<?php
/**
 * Steps — the numbered process cards.
 *
 * Rendered as an ordered list so the sequence is conveyed to assistive technology, not
 * only by the visual "01 / 02 / 03". The numerals themselves are decorative once the
 * list carries the order, so they are aria-hidden.
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
<section <?php echo fm_wrapper(['fm-steps']); ?>>
    <?php if ($number !== '' || $label !== '') : ?>
        <?php echo fm_section_rule($number, $label, $aside); ?>
    <?php endif; ?>

    <?php if ($heading !== '') : ?>
        <h2 class="fm-steps__heading"><?php echo esc_html($heading); ?></h2>
    <?php endif; ?>

    <ol class="fm-steps__list">
        <?php foreach ($items as $i => $item) : ?>
            <li class="fm-steps__item" style="--fm-step-tint: var(--fm-tint-<?php echo esc_attr((string) (($i % 4) + 1)); ?>)">
                <?php if (($item['n'] ?? '') !== '') : ?>
                    <span class="fm-steps__n" aria-hidden="true"><?php echo esc_html((string) $item['n']); ?></span>
                <?php endif; ?>

                <?php if (($item['title'] ?? '') !== '') : ?>
                    <h3 class="fm-steps__title"><?php echo esc_html((string) $item['title']); ?></h3>
                <?php endif; ?>

                <?php if (($item['body'] ?? '') !== '') : ?>
                    <p class="fm-steps__body"><?php echo esc_html((string) $item['body']); ?></p>
                <?php endif; ?>

                <?php if (($item['aside'] ?? '') !== '') : ?>
                    <p class="fm-steps__aside"><?php echo esc_html((string) $item['aside']); ?></p>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</section>
