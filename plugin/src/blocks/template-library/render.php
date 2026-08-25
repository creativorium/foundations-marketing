<?php
/**
 * Template library — the full catalogue with niche filtering.
 *
 * Filtering is progressive enhancement, and deliberately so: every card is rendered
 * into the page, and the filter only hides some of them. With JavaScript off, or
 * before it runs, the visitor still sees the whole library — and, just as important,
 * so does a crawler. A JS-built grid would leave the catalogue invisible to search,
 * which for this site is the catalogue that has to rank.
 *
 * The filter bar is only rendered when the templates actually carry categories, so
 * it never appears as a row of controls that do nothing.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$limit     = (int) ($attributes['limit'] ?? -1);
$templates = fm_get_templates($limit);

if ($templates === []) {
    if (is_admin()) {
        printf(
            '<p class="fm-library__empty">%s</p>',
            esc_html__('No Site Templates are published yet.', 'foundations')
        );
    }

    return;
}

$number     = (string) ($attributes['number'] ?? '');
$label      = (string) ($attributes['label'] ?? '');
$aside      = (string) ($attributes['aside'] ?? '');
$price_from = (string) ($attributes['priceFrom'] ?? '');
$note       = (string) ($attributes['note'] ?? '');
$all_label  = (string) ($attributes['allLabel'] ?? __('All', 'foundations'));

// Categories, in the order they first appear, with no duplicates.
$categories = [];

foreach ($templates as $template) {
    $category = trim($template['category']);

    if ($category !== '' && !in_array($category, $categories, true)) {
        $categories[] = $category;
    }
}

$show_filters = (bool) ($attributes['showFilters'] ?? true) && count($categories) > 1;
?>
<section <?php echo fm_wrapper(['fm-library']); ?> data-fm-library>
    <?php if ($number !== '' || $label !== '') : ?>
        <?php echo fm_section_rule($number, $label, $aside); ?>
    <?php endif; ?>

    <?php if ($show_filters) : ?>
        <?php
        /*
         * A toolbar of toggles, not links: each button reports its state with
         * aria-pressed so the current filter is announced, not just coloured in.
         */
        ?>
        <div class="fm-library__filters" role="group" aria-label="<?php esc_attr_e('Filter templates by niche', 'foundations'); ?>">
            <button class="fm-library__filter" type="button" data-fm-filter="" aria-pressed="true">
                <?php echo esc_html($all_label); ?>
            </button>
            <?php foreach ($categories as $category) : ?>
                <button
                    class="fm-library__filter"
                    type="button"
                    data-fm-filter="<?php echo esc_attr($category); ?>"
                    aria-pressed="false">
                    <?php echo esc_html($category); ?>
                </button>
            <?php endforeach; ?>
        </div>

        <?php /* Announces "4 of 9 templates" when the filter changes. */ ?>
        <p class="fm-sr-only" role="status" data-fm-library-status></p>
    <?php endif; ?>

    <ul class="fm-library__list">
        <?php foreach ($templates as $i => $template) : ?>
            <?php
            $tint   = 'var(--fm-tint-' . (($i % 4) + 1) . ')';
            $screen = 'var(--fm-screen-' . (($i % 9) + 1) . ')';
            ?>
            <li
                class="fm-library__item"
                style="--fm-card-tint: <?php echo esc_attr($tint); ?>"
                data-fm-category="<?php echo esc_attr($template['category']); ?>">
                <a class="fm-template-card" href="<?php echo fm_url(fm_template_card_url($template)); ?>">
                    <span class="fm-template-card__frame">
                        <span class="fm-template-card__device">
                            <span class="fm-template-card__dot" aria-hidden="true"></span>
                            <span class="fm-template-card__screen" style="--fm-card-screen: <?php echo esc_attr($screen); ?>">
                                <?php if ($template['thumb_id'] > 0) : ?>
                                    <?php
                                    echo fm_image(
                                        $template['thumb_id'],
                                        'medium_large',
                                        [
                                            'class' => 'fm-template-card__shot',
                                            /* translators: %s: template name. */
                                            'alt'   => sprintf(
                                                __('%s website template by Foundations Marketing', 'foundations'),
                                                $template['name']
                                            ),
                                        ]
                                    );
                                    ?>
                                <?php else : ?>
                                    <span class="fm-template-card__skeleton" aria-hidden="true"></span>
                                <?php endif; ?>
                            </span>
                        </span>
                    </span>

                    <span class="fm-template-card__body">
                        <?php if ($template['niche'] !== '') : ?>
                            <span class="fm-template-card__niche"><?php echo esc_html($template['niche']); ?></span>
                        <?php endif; ?>

                        <span class="fm-template-card__name"><?php echo esc_html($template['name']); ?></span>

                        <?php if ($template['description'] !== '') : ?>
                            <span class="fm-template-card__desc"><?php echo esc_html($template['description']); ?></span>
                        <?php endif; ?>

                        <?php if ($template['sections'] !== []) : ?>
                            <span class="fm-library__sections">
                                <?php foreach ($template['sections'] as $section) : ?>
                                    <span class="fm-library__section"><?php echo esc_html($section); ?></span>
                                <?php endforeach; ?>
                            </span>
                        <?php endif; ?>

                        <span class="fm-template-card__meta">
                            <span><?php echo esc_html($price_from); ?></span>
                            <span class="fm-template-card__view">
                                <?php esc_html_e('View demo', 'foundations'); ?>
                                <span aria-hidden="true">&#8599;</span>
                            </span>
                        </span>
                    </span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if ($note !== '') : ?>
        <div class="fm-library__note">
            <p><?php echo esc_html($note); ?></p>
        </div>
    <?php endif; ?>
</section>
