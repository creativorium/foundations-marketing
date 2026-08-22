<?php
/**
 * Quotes — the founder statement alongside client testimonials.
 *
 * <blockquote> + <figcaption> so the attribution is programmatically tied to the quote
 * rather than just sitting near it. The decorative quotation marks are in CSS, not in
 * the text, so they are never read aloud.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$founder_quote = (string) ($attributes['founderQuote'] ?? '');
$items         = is_array($attributes['items'] ?? null) ? $attributes['items'] : [];

// Drop testimonials with no actual quote in them.
$items = array_values(array_filter(
    $items,
    static fn ($item): bool => trim((string) ($item['text'] ?? '')) !== ''
));

if ($founder_quote === '' && $items === []) {
    return;
}

$number    = (string) ($attributes['number'] ?? '');
$label     = (string) ($attributes['label'] ?? '');
$aside     = (string) ($attributes['aside'] ?? '');
$name      = (string) ($attributes['founderName'] ?? '');
$role      = (string) ($attributes['founderRole'] ?? '');
$media_id  = (int) ($attributes['founderMediaId'] ?? 0);
$media_alt = (string) ($attributes['founderMediaAlt'] ?? '');
$open_slot = (string) ($attributes['openSlot'] ?? '');
?>
<section <?php echo fm_wrapper(['fm-quotes']); ?>>
    <?php if ($number !== '' || $label !== '') : ?>
        <?php echo fm_section_rule($number, $label, $aside); ?>
    <?php endif; ?>

    <div class="fm-quotes__grid">
        <?php if ($founder_quote !== '') : ?>
            <figure class="fm-quotes__founder">
                <div class="fm-quotes__portrait">
                    <?php if ($media_id > 0) : ?>
                        <?php echo fm_image($media_id, 'medium_large', ['alt' => $media_alt, 'class' => 'fm-quotes__portrait-img']); ?>
                    <?php else : ?>
                        <span class="fm-quotes__placeholder">
                            <span class="fm-quotes__tag">[ photo ]</span>
                            <span class="fm-quotes__hint"><?php echo esc_html($name); ?></span>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="fm-quotes__founder-body">
                    <blockquote class="fm-quotes__founder-text">
                        <p><?php echo esc_html($founder_quote); ?></p>
                    </blockquote>
                    <figcaption class="fm-quotes__attribution">
                        <span class="fm-quotes__name"><?php echo esc_html($name); ?></span>
                        <span class="fm-quotes__role"><?php echo esc_html($role); ?></span>
                    </figcaption>
                </div>
            </figure>
        <?php endif; ?>

        <div class="fm-quotes__list">
            <?php foreach ($items as $item) : ?>
                <figure class="fm-quotes__item">
                    <blockquote>
                        <p><?php echo esc_html((string) $item['text']); ?></p>
                    </blockquote>
                    <figcaption class="fm-quotes__attribution">
                        <span class="fm-quotes__name"><?php echo esc_html((string) ($item['name'] ?? '')); ?></span>
                        <span class="fm-quotes__role"><?php echo esc_html((string) ($item['role'] ?? '')); ?></span>
                    </figcaption>
                </figure>
            <?php endforeach; ?>

            <?php if ($open_slot !== '') : ?>
                <?php /* The deliberately empty "your testimonial goes here" card. */ ?>
                <p class="fm-quotes__open"><?php echo esc_html($open_slot); ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>
