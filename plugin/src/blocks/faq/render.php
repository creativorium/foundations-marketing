<?php
/**
 * FAQ.
 *
 * Uses <details>/<summary> so questions are collapsible with **no JavaScript at all** —
 * keyboard accessible and announced correctly by screen readers for free. The first one
 * is open so the section never reads as an empty stack of headings.
 *
 * SEO: emits FAQPage schema (strategy §4, "basic schema markup"). Yoast does not generate
 * this from block content, so it is ours to add — but only once per page, and only when
 * the questions are genuinely visible on the page, which is Google's requirement.
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
?>
<section <?php echo fm_wrapper(['fm-faq']); ?>>
    <?php if ($number !== '' || $label !== '') : ?>
        <?php echo fm_section_rule($number, $label, $aside); ?>
    <?php endif; ?>

    <div class="fm-faq__grid">
        <?php if ($heading !== '' || $accent !== '') : ?>
            <h2 class="fm-faq__heading">
                <?php echo esc_html($heading); ?>
                <?php if ($accent !== '') : ?>
                    <span class="fm-faq__heading-accent"><?php echo esc_html($accent); ?></span>
                <?php endif; ?>
            </h2>
        <?php endif; ?>

        <div class="fm-faq__list">
            <?php foreach ($items as $i => $item) : ?>
                <details class="fm-faq__item"<?php echo $i === 0 ? ' open' : ''; ?>>
                    <summary class="fm-faq__q">
                        <span><?php echo esc_html((string) $item['q']); ?></span>
                        <span class="fm-faq__marker" aria-hidden="true"></span>
                    </summary>
                    <p class="fm-faq__a"><?php echo esc_html((string) $item['a']); ?></p>
                </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>
