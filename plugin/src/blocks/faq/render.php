<?php
/**
 * FAQ.
 *
 * Laid out as the canvas has it: every question and answer visible, side by side,
 * separated by the same hairline rule the rest of the layout uses. No disclosure
 * widget — nothing to click, nothing to discover, and no state to get wrong.
 *
 * That is also the better answer for search. Google only surfaces FAQ content it can
 * see, and answers hidden behind a toggle are worth less than answers simply on the
 * page. The FAQPage schema below describes exactly what a visitor already reads.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$items = is_array($attributes['items'] ?? null) ? $attributes['items'] : [];

// Drop any half-filled rows before we count, render or emit schema.
$items = array_values(array_filter(
    $items,
    static fn ($item): bool => trim((string) ($item['q'] ?? '')) !== ''
        && trim((string) ($item['a'] ?? '')) !== ''
));

if ($items === []) {
    return;
}

$number  = (string) ($attributes['number'] ?? '');
$label   = (string) ($attributes['label'] ?? '');
$aside   = (string) ($attributes['aside'] ?? '');
$heading = (string) ($attributes['heading'] ?? '');
$accent  = (string) ($attributes['headingAccent'] ?? '');

$has_heading = $heading !== '' || $accent !== '';

if ((bool) ($attributes['emitSchema'] ?? true)) {
    $schema = [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => array_map(
            static fn (array $item): array => [
                '@type'          => 'Question',
                'name'           => (string) $item['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => (string) $item['a'],
                ],
            ],
            $items
        ),
    ];

    printf(
        '<script type="application/ld+json">%s</script>',
        wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    );
}

$classes = ['fm-faq'];

// Without a heading the list runs the full width instead of sitting in a column
// beside it — that is how the services page uses this block.
if (!$has_heading) {
    $classes[] = 'fm-faq--full';
}
?>
<section <?php echo fm_wrapper($classes); ?>>
    <?php if ($number !== '' || $label !== '') : ?>
        <?php echo fm_section_rule($number, $label, $aside); ?>
    <?php endif; ?>

    <div class="fm-faq__grid">
        <?php if ($has_heading) : ?>
            <h2 class="fm-faq__heading">
                <?php echo esc_html($heading); ?>
                <?php if ($accent !== '') : ?>
                    <span class="fm-faq__heading-accent"><?php echo esc_html($accent); ?></span>
                <?php endif; ?>
            </h2>
        <?php endif; ?>

        <div class="fm-faq__list">
            <?php foreach ($items as $item) : ?>
                <div class="fm-faq__item">
                    <h3 class="fm-faq__q"><?php echo esc_html((string) $item['q']); ?></h3>
                    <p class="fm-faq__a"><?php echo esc_html((string) $item['a']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
