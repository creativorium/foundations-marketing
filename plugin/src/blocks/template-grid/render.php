<?php
/**
 * Template grid — the catalogue.
 *
 * Reads the published Site Templates through fm_get_templates() rather than querying
 * anything here, so this file stays inside the component boundary.
 *
 * SEO: the card's name is the link, and it leads to the package builder with this
 * template chosen. The demo sits beside it as a second link that opens in its own tab,
 * so a buyer can look at the mini site without losing the page they were choosing from.
 * Those internal links are the strategy's §4 "homepage to services to each template"
 * requirement, and they are how the niche demo pages get discovered.
 *
 * Performance: card screenshots are below the fold, so they stay lazy. Only the hero
 * image is eager.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$number     = (string) ($attributes['number'] ?? '');
$label      = (string) ($attributes['label'] ?? '');
$aside      = (string) ($attributes['aside'] ?? '');
$heading    = (string) ($attributes['heading'] ?? '');
$intro      = (string) ($attributes['intro'] ?? '');
$price_from = (string) ($attributes['priceFrom'] ?? '');
$limit      = min(8, max(1, (int) ($attributes['limit'] ?? 8)));
$more_text  = (string) ($attributes['moreText'] ?? '');
$more_url   = (string) ($attributes['moreUrl'] ?? '');
$more_url   = fm_marketing_cta_url($more_text, $more_url);
$compact    = (bool) ($attributes['compact'] ?? false);
$tone       = (string) ($attributes['tone'] ?? 'plain');

$templates = fm_get_templates($limit);

if ($templates === []) {
    // Nothing published yet. Say so in the editor, but render nothing on the front end
    // rather than leaving an empty band with a heading and no content.
    if (is_admin()) {
        printf(
            '<p class="fm-template-grid__empty">%s</p>',
            esc_html__('No Site Templates are published yet.', 'foundations')
        );
    }

    return;
}
?>
<?php
$classes = ['fm-template-grid', 'fm-template-grid--' . sanitize_html_class($tone)];

// The compact card drops the description and price, showing only the name and
// niche — that is how the services page lists them.
if ($compact) {
    $classes[] = 'fm-template-grid--compact';
}
?>
<section <?php echo fm_wrapper($classes); ?>>
    <?php if ($number !== '' || $label !== '') : ?>
        <?php echo fm_section_rule($number, $label, $aside); ?>
    <?php endif; ?>

    <?php if ($heading !== '' || $intro !== '') : ?>
        <div class="fm-template-grid__intro">
            <?php if ($heading !== '') : ?>
                <h2 class="fm-template-grid__heading"><?php echo esc_html($heading); ?></h2>
            <?php endif; ?>
            <?php if ($intro !== '') : ?>
                <p class="fm-template-grid__lede"><?php echo esc_html($intro); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <ul class="fm-template-grid__list">
        <?php foreach ($templates as $i => $template) : ?>
            <?php
            // The nine tint/screen tokens cycle, matching the client's canvas.
            $tint   = 'var(--fm-tint-' . (($i % 4) + 1) . ')';
            $screen = 'var(--fm-screen-' . (($i % 9) + 1) . ')';
            ?>
            <li class="fm-template-grid__item" style="--fm-card-tint: <?php echo esc_attr($tint); ?>">
                <?php
                /*
                 * Two destinations, so the card cannot be one anchor: an anchor inside an
                 * anchor is invalid and browsers unnest it. The name carries the primary
                 * link and its ::after stretches over the whole card, which keeps the
                 * whole card clickable while the demo link stays separately reachable —
                 * by mouse, by keyboard and by a screen reader's link list.
                 */
                $demo = fm_template_demo_url($template);
                ?>
                <div class="fm-template-card">
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

                        <span class="fm-template-card__name">
                            <a class="fm-template-card__link" href="<?php echo fm_url(fm_template_card_url($template)); ?>">
                                <?php echo esc_html($template['name']); ?>
                            </a>
                        </span>

                        <?php if (!$compact) : ?>
                            <?php if ($template['description'] !== '') : ?>
                                <span class="fm-template-card__desc"><?php echo esc_html($template['description']); ?></span>
                            <?php endif; ?>

                            <span class="fm-template-card__meta">
                                <span><?php echo esc_html($price_from); ?></span>
                                <span class="fm-template-card__actions">
                                    <?php if ($demo !== '') : ?>
                                        <?php // data-fm-demo: main.js opens these with a script, so the demo tab can close itself again. ?>
                                        <a class="fm-template-card__demo" href="<?php echo fm_url($demo); ?>"
                                           target="_blank" rel="noopener" data-fm-demo>
                                            <?php esc_html_e('View demo', 'foundations'); ?>
                                            <span aria-hidden="true">&#8599;</span>
                                            <span class="fm-sr-only"><?php esc_html_e('(opens in a new tab)', 'foundations'); ?></span>
                                        </a>
                                    <?php endif; ?>
                                    <span class="fm-template-card__view">
                                        <?php esc_html_e('Build this site', 'foundations'); ?>
                                        <span aria-hidden="true">&rarr;</span>
                                    </span>
                                </span>
                            </span>
                        <?php endif; ?>
                    </span>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if ($more_text !== '') : ?>
        <p class="fm-template-grid__more">
            <a class="fm-template-grid__more-link" href="<?php echo fm_url($more_url); ?>">
                <?php echo esc_html($more_text); ?>
                <span aria-hidden="true">&rarr;</span>
            </a>
        </p>
    <?php endif; ?>
</section>
