<?php
/**
 * The checkout: preview the chosen template, add extras, review and pay.
 *
 * Laid out as the client canvas has it — two columns, the template preview held on the
 * left across all three steps while the right column changes. See
 * doc/client-html/main site/Foundations Checkout (standalone).html.
 *
 * Everything is server-rendered, all three steps included. builder.js shows, hides and
 * adds up; it never builds the page. With scripting off every step is readable and the
 * form still submits, which also keeps the page crawlable — the template pages link here.
 *
 * Money is never trusted from the browser. The form posts ids only; the prices are read
 * back from these same attributes server-side in plugin/inc/checkout.php. The totals
 * rendered here are for the reader, not for the till.
 *
 * @var array $attributes
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$attr = static fn (string $key, $fallback = '') => $attributes[$key] ?? $fallback;

$base_price = max(0, (int) $attr('basePrice', 249));
$base_label = (string) $attr('baseLabel', 'base build');
$page_price = max(0, (int) $attr('extraPagePrice', 50));
$product_id = (int) $attr('productId', 0);

$included    = (array) $attr('included', []);
$free_items  = (array) $attr('freeItems', []);
$addons      = (array) $attr('addons', []);
$extra_pages = (array) $attr('extraPages', []);
$after       = (array) $attr('afterSteps', []);

// Which template the buyer came in with. fm_selected_template() validates the slug
// against the real catalogue rather than trusting the query string.
$template = fm_selected_template();

$steps = [
    __('Preview', 'foundations'),
    __('Add extras', 'foundations'),
    __('Review & pay', 'foundations'),
];

$devices = [
    'desktop' => __('Desktop', 'foundations'),
    'tablet'  => __('Tablet', 'foundations'),
    'mobile'  => __('Mobile', 'foundations'),
];

$action = fm_checkout_url();
$money  = static fn (int $n): string => '&pound;' . number_format_i18n($n);
?>
<div <?php echo fm_wrapper(['fm-builder']); ?> data-fm-builder data-step="1"
     data-base-price="<?php echo esc_attr((string) $base_price); ?>">

    <form class="fm-builder__form" method="post" action="<?php echo esc_url($action); ?>">
        <?php wp_nonce_field('fm_build_order', 'fm_build_nonce'); ?>
        <input type="hidden" name="fm_build" value="1">
        <?php if ($product_id > 0) : ?>
            <input type="hidden" name="add-to-cart" value="<?php echo esc_attr((string) $product_id); ?>">
        <?php endif; ?>
        <input type="hidden" name="fm_template" value="<?php echo esc_attr((string) ($template['slug'] ?? '')); ?>">

        <?php // --- Sticky progress bar ------------------------------------------ ?>
        <div class="fm-builder__bar">
            <ol class="fm-builder__steps">
                <?php foreach ($steps as $i => $label) : $n = $i + 1; ?>
                    <li class="fm-builder__step">
                        <button type="button" class="fm-builder__step-btn" data-fm-step="<?php echo esc_attr((string) $n); ?>"
                                <?php echo $n === 1 ? 'aria-current="step"' : ''; ?>>
                            <span class="fm-builder__step-dot" aria-hidden="true"><?php echo esc_html((string) $n); ?></span>
                            <span class="fm-builder__step-label"><?php echo esc_html($label); ?></span>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ol>

            <div class="fm-builder__bar-end">
                <p class="fm-builder__running">
                    <span class="fm-builder__running-label"><?php esc_html_e('Running total', 'foundations'); ?></span>
                    <span class="fm-builder__running-total" data-fm-total><?php echo $money($base_price); ?></span>
                </p>
                <button type="button" class="fm-builder__next" data-fm-next>
                    <?php esc_html_e('Add extras', 'foundations'); ?> &rarr;
                </button>
            </div>
        </div>

        <div class="fm-builder__split">

            <?php // --- Left: the preview, held across all three steps ------------ ?>
            <aside class="fm-builder__aside">
                <div class="fm-builder__aside-head">
                    <div>
                        <p class="fm-builder__eyebrow"><?php echo esc_html((string) $attr('previewHeading', 'Your template')); ?></p>
                        <p class="fm-builder__template">
                            <span class="fm-builder__template-name">
                                <?php echo esc_html((string) ($template['name'] ?? __('No template chosen', 'foundations'))); ?>
                            </span>
                            <?php if (!empty($template['url'])) : ?>
                                <a class="fm-builder__live" href="<?php echo fm_url((string) $template['url']); ?>">
                                    <?php esc_html_e('View live site', 'foundations'); ?> &#8599;
                                </a>
                            <?php endif; ?>
                        </p>
                    </div>

                    <?php // A segmented control: one bordered pill, buttons flush inside it. ?>
                    <div class="fm-builder__devices" role="group"
                         aria-label="<?php esc_attr_e('Preview size', 'foundations'); ?>">
                        <?php $first = true; foreach ($devices as $id => $label) : ?>
                            <button type="button" class="fm-builder__device" data-fm-device="<?php echo esc_attr($id); ?>"
                                    aria-pressed="<?php echo $first ? 'true' : 'false'; ?>">
                                <?php echo esc_html($label); ?>
                            </button>
                        <?php $first = false; endforeach; ?>
                    </div>
                </div>

                <?php if (!empty($template['description'])) : ?>
                    <p class="fm-builder__template-desc"><?php echo esc_html((string) $template['description']); ?></p>
                <?php endif; ?>

                <?php
                /*
                 * The preview is drawn, not photographed. The canvas shows a wireframe
                 * inside a device bezel, and drawing it means every template previews
                 * identically at every size with no screenshot to commission, no image
                 * to download, and nothing to shift the layout while it loads.
                 *
                 * Decorative to the last pixel, so the whole thing is aria-hidden — a
                 * screen reader gets the caption underneath, which says what it is.
                 */
                ?>
                <div class="fm-builder__stage">
                    <div class="fm-builder__frame" data-fm-frame data-device="desktop" aria-hidden="true">
                        <div class="fm-builder__bezel">
                            <span class="fm-builder__camera"></span>
                            <div class="fm-builder__screen">
                                <div class="fm-builder__screen-bar">
                                    <i class="fm-builder__pip"></i>
                                    <i class="fm-builder__pip fm-builder__pip--sm"></i>
                                    <i class="fm-builder__pip fm-builder__pip--sm"></i>
                                    <i class="fm-builder__pip fm-builder__pip--btn"></i>
                                </div>
                                <div class="fm-builder__screen-hero">
                                    <i class="fm-builder__line fm-builder__line--w1"></i>
                                    <i class="fm-builder__line fm-builder__line--w2"></i>
                                    <i class="fm-builder__line fm-builder__line--w3"></i>
                                    <i class="fm-builder__line fm-builder__line--btn"></i>
                                </div>
                                <div class="fm-builder__screen-block"></div>
                                <div class="fm-builder__screen-cols">
                                    <i></i><i></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="fm-builder__note"><?php echo esc_html((string) $attr('previewNote')); ?></p>
                </div>
            </aside>

            <?php // --- Right: the step that is showing --------------------------- ?>
            <div class="fm-builder__pane">

                <?php // Step 1 ?>
                <section class="fm-builder__panel" data-fm-panel="1"
                         aria-label="<?php esc_attr_e('What you are getting', 'foundations'); ?>">
                    <h2 class="fm-builder__heading"><?php echo esc_html((string) $attr('step1Heading')); ?></h2>
                    <p class="fm-builder__intro"><?php echo esc_html((string) $attr('step1Intro')); ?></p>

                    <?php if ($included !== []) : ?>
                        <div class="fm-builder__box fm-builder__box--pastel">
                            <p class="fm-builder__box-label"><?php echo esc_html((string) $attr('includedLabel')); ?></p>
                            <ul class="fm-builder__plus">
                                <?php foreach ($included as $item) : ?>
                                    <li><?php echo esc_html((string) $item); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ((string) $attr('freeBody') !== '') : ?>
                        <div class="fm-builder__box fm-builder__box--warm">
                            <p class="fm-builder__box-label fm-builder__box-label--warm">
                                <?php echo esc_html((string) $attr('freeHeading')); ?>
                            </p>
                            <p class="fm-builder__box-body"><?php echo esc_html((string) $attr('freeBody')); ?></p>
                        </div>
                    <?php endif; ?>
                </section>

                <?php // Step 2 ?>
                <section class="fm-builder__panel" data-fm-panel="2"
                         aria-label="<?php esc_attr_e('Add extras', 'foundations'); ?>" hidden>
                    <h2 class="fm-builder__heading"><?php echo esc_html((string) $attr('extrasHeading')); ?></h2>
                    <p class="fm-builder__intro"><?php echo esc_html((string) $attr('step2Intro')); ?></p>

                    <?php if ($addons !== []) : ?>
                        <p class="fm-builder__rule-label"><?php esc_html_e('Services', 'foundations'); ?></p>
                        <ul class="fm-builder__cards">
                            <?php foreach ($addons as $addon) :
                                $id = (string) ($addon['id'] ?? '');
                                if ($id === '') {
                                    continue;
                                }
                                $price = max(0, (int) ($addon['price'] ?? 0));
                                ?>
                                <li>
                                    <button type="button" class="fm-builder__card"
                                            data-fm-addon="<?php echo esc_attr($id); ?>"
                                            data-price="<?php echo esc_attr((string) $price); ?>"
                                            data-name="<?php echo esc_attr((string) ($addon['name'] ?? '')); ?>"
                                            aria-pressed="false">
                                        <span class="fm-builder__card-name"><?php echo esc_html((string) ($addon['name'] ?? '')); ?></span>
                                        <span class="fm-builder__card-end">
                                            <span class="fm-builder__card-price"><?php echo $money($price); ?></span>
                                            <span class="fm-builder__mark" aria-hidden="true"></span>
                                        </span>
                                        <span class="fm-builder__card-body"><?php echo esc_html((string) ($addon['body'] ?? '')); ?></span>
                                        <?php if (!empty($addon['when'])) : ?>
                                            <span class="fm-builder__card-when"><?php echo esc_html((string) $addon['when']); ?></span>
                                        <?php endif; ?>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if ($extra_pages !== []) : ?>
                        <p class="fm-builder__rule-label">
                            <?php
                            printf(
                                /* translators: %s: price of one extra page, already formatted. */
                                esc_html__('Extra pages — %s each', 'foundations'),
                                $money($page_price) // phpcs:ignore WordPress.Security.EscapeOutput
                            );
                            ?>
                        </p>
                        <ul class="fm-builder__cards fm-builder__cards--pages">
                            <?php foreach ($extra_pages as $page) :
                                $id = (string) ($page['id'] ?? '');
                                if ($id === '') {
                                    continue;
                                }
                                ?>
                                <li>
                                    <button type="button" class="fm-builder__card fm-builder__card--page"
                                            data-fm-page="<?php echo esc_attr($id); ?>"
                                            data-price="<?php echo esc_attr((string) $page_price); ?>"
                                            data-name="<?php echo esc_attr((string) ($page['name'] ?? '')); ?>"
                                            aria-pressed="false">
                                        <span class="fm-builder__box-tick" aria-hidden="true"></span>
                                        <span class="fm-builder__card-name"><?php echo esc_html((string) ($page['name'] ?? '')); ?></span>
                                        <span class="fm-builder__card-price"><?php echo $money($page_price); ?></span>
                                        <span class="fm-builder__card-body"><?php echo esc_html((string) ($page['body'] ?? '')); ?></span>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <p class="fm-builder__fine"><?php echo esc_html((string) $attr('pagesNote')); ?></p>
                    <?php endif; ?>
                </section>

                <?php // Step 3 ?>
                <section class="fm-builder__panel" data-fm-panel="3"
                         aria-label="<?php esc_attr_e('Review and pay', 'foundations'); ?>" hidden>
                    <h2 class="fm-builder__heading"><?php echo esc_html((string) $attr('reviewHeading')); ?></h2>
                    <p class="fm-builder__intro"><?php echo esc_html((string) $attr('step3Intro')); ?></p>

                    <div class="fm-builder__box fm-builder__box--summary">
                        <p class="fm-builder__box-label"><?php esc_html_e('Order summary', 'foundations'); ?></p>

                        <ul class="fm-builder__lines" data-fm-lines>
                            <li class="fm-builder__line">
                                <span class="fm-builder__line-label">
                                    <?php
                                    printf(
                                        /* translators: 1: template name, 2: what the base price covers. */
                                        esc_html__('%1$s template — %2$s', 'foundations'),
                                        esc_html((string) ($template['name'] ?? __('Template', 'foundations'))),
                                        esc_html($base_label)
                                    );
                                    ?>
                                </span>
                                <span class="fm-builder__line-amount"><?php echo $money($base_price); ?></span>
                                <?php // Sends the reader back to step 1 rather than being a second submit. ?>
                                <button type="button" class="fm-builder__change" data-fm-step="1">
                                    <?php esc_html_e('Change', 'foundations'); ?>
                                </button>
                            </li>

                            <?php foreach ($free_items as $item) : ?>
                                <li class="fm-builder__line fm-builder__line--free">
                                    <span class="fm-builder__line-label"><?php echo esc_html((string) $item); ?></span>
                                    <span class="fm-builder__line-amount"><?php esc_html_e('Free', 'foundations'); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <p class="fm-builder__total">
                            <span><?php esc_html_e('Total today', 'foundations'); ?></span>
                            <span class="fm-builder__total-amount" data-fm-total><?php echo $money($base_price); ?></span>
                        </p>
                    </div>

                    <?php if ($after !== []) : ?>
                        <div class="fm-builder__box fm-builder__box--pastel">
                            <p class="fm-builder__box-label"><?php esc_html_e('What happens after you pay', 'foundations'); ?></p>
                            <ol class="fm-builder__after">
                                <?php foreach ($after as $stage) : ?>
                                    <li>
                                        <span class="fm-builder__after-when"><?php echo esc_html((string) ($stage['when'] ?? '')); ?></span>
                                        <span class="fm-builder__after-what"><?php echo esc_html((string) ($stage['what'] ?? '')); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="fm-builder__pay">
                        <?php
                        printf(
                            /* translators: %s: the order total, already formatted. */
                            esc_html__('Pay %s and start my build', 'foundations'),
                            '<span data-fm-total>' . $money($base_price) . '</span>' // phpcs:ignore WordPress.Security.EscapeOutput
                        );
                        ?>
                        &rarr;
                    </button>

                    <p class="fm-builder__fine fm-builder__fine--center"><?php echo esc_html((string) $attr('payNote')); ?></p>

                    <?php if ($product_id <= 0) : ?>
                        <p class="fm-builder__warn">
                            <?php esc_html_e('No product is linked yet — set one in the block sidebar, or this button cannot take payment.', 'foundations'); ?>
                        </p>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </form>
</div>
