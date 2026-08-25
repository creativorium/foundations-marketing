<?php
/**
 * The checkout: preview the chosen template, add extras, review and pay.
 *
 * Everything is server-rendered, including all three steps and every price. The
 * JavaScript in builder.js only shows, hides and adds up — so with scripting off the
 * page is still a complete, readable description of what is being bought, and the form
 * still submits. That also keeps the whole thing crawlable, which matters because this
 * is where the template pages point.
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

$base_price  = max(0, (int) ($attributes['basePrice'] ?? 249));
$base_label  = (string) ($attributes['baseLabel'] ?? 'base build');
$page_price  = max(0, (int) ($attributes['extraPagePrice'] ?? 50));
$product_id  = (int) ($attributes['productId'] ?? 0);

$included    = (array) ($attributes['included'] ?? []);
$free_items  = (array) ($attributes['freeItems'] ?? []);
$addons      = (array) ($attributes['addons'] ?? []);
$extra_pages = (array) ($attributes['extraPages'] ?? []);
$after       = (array) ($attributes['afterSteps'] ?? []);

// Which template the buyer came in with. The template cards link here with ?template=
// so the choice survives the jump; fm_selected_template() validates the slug against the
// real catalogue rather than trusting the query string.
$template = fm_selected_template();

$steps = [
    ['n' => '1', 'label' => __('Preview', 'foundations')],
    ['n' => '2', 'label' => __('Add extras', 'foundations')],
    ['n' => '3', 'label' => __('Review & pay', 'foundations')],
];

$devices = [
    ['id' => 'desktop', 'label' => __('Desktop', 'foundations')],
    ['id' => 'tablet',  'label' => __('Tablet', 'foundations')],
    ['id' => 'mobile',  'label' => __('Mobile', 'foundations')],
];

$action = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/checkout/');
?>
<div <?php echo fm_wrapper(['fm-builder']); ?> data-fm-builder data-step="1"
     data-base-price="<?php echo esc_attr((string) $base_price); ?>"
     data-page-price="<?php echo esc_attr((string) $page_price); ?>"
     data-currency="&pound;">

    <form class="fm-builder__form" method="post" action="<?php echo esc_url($action); ?>">
        <?php wp_nonce_field('fm_build_order', 'fm_build_nonce'); ?>
        <input type="hidden" name="fm_build" value="1">
        <?php if ($product_id > 0) : ?>
            <input type="hidden" name="add-to-cart" value="<?php echo esc_attr((string) $product_id); ?>">
        <?php endif; ?>
        <input type="hidden" name="fm_template" value="<?php echo esc_attr($template['slug'] ?? ''); ?>">

        <?php // --- The sticky progress bar ------------------------------------ ?>
        <div class="fm-builder__bar">
            <ol class="fm-builder__steps">
                <?php foreach ($steps as $i => $step) : $n = $i + 1; ?>
                    <li class="fm-builder__step" data-step-item="<?php echo esc_attr((string) $n); ?>">
                        <button type="button" class="fm-builder__step-btn"
                                data-fm-step="<?php echo esc_attr((string) $n); ?>"
                                <?php echo $n === 1 ? 'aria-current="step"' : ''; ?>>
                            <span class="fm-builder__step-dot" aria-hidden="true"><?php echo esc_html($step['n']); ?></span>
                            <span class="fm-builder__step-label"><?php echo esc_html($step['label']); ?></span>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ol>

            <div class="fm-builder__bar-end">
                <p class="fm-builder__running">
                    <span class="fm-builder__running-label"><?php esc_html_e('Running total', 'foundations'); ?></span>
                    <span class="fm-builder__running-total" data-fm-total>&pound;<?php echo esc_html((string) $base_price); ?></span>
                </p>
                <button type="button" class="fm-builder__next" data-fm-next>
                    <?php esc_html_e('Add extras', 'foundations'); ?> &rarr;
                </button>
            </div>
        </div>

        <?php // --- Step 1: preview ---------------------------------------------- ?>
        <section class="fm-builder__panel" data-fm-panel="1"
                 aria-label="<?php esc_attr_e('Preview your template', 'foundations'); ?>">

            <div class="fm-builder__preview-head">
                <div>
                    <p class="fm-builder__eyebrow"><?php echo esc_html((string) ($attributes['previewHeading'] ?? 'Your template')); ?></p>
                    <h2 class="fm-builder__template-name">
                        <?php echo esc_html($template['name'] ?? __('No template chosen', 'foundations')); ?>
                    </h2>
                </div>

                <?php if (!empty($template['url'])) : ?>
                    <a class="fm-builder__live" href="<?php echo fm_url($template['url']); ?>">
                        <?php esc_html_e('View live site', 'foundations'); ?> &#8599;
                    </a>
                <?php endif; ?>
            </div>

            <div class="fm-builder__devices" role="group"
                 aria-label="<?php esc_attr_e('Preview size', 'foundations'); ?>">
                <?php foreach ($devices as $i => $device) : ?>
                    <button type="button" class="fm-builder__device"
                            data-fm-device="<?php echo esc_attr($device['id']); ?>"
                            aria-pressed="<?php echo $i === 0 ? 'true' : 'false'; ?>">
                        <?php echo esc_html($device['label']); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="fm-builder__frame" data-fm-frame data-device="desktop">
                <?php if (!empty($template['thumb_id'])) : ?>
                    <?php
                    // Eager: on this page the preview IS the reason the buyer is here,
                    // so it is the LCP element and must not be lazy.
                    echo fm_image(
                        (int) $template['thumb_id'],
                        'large',
                        ['alt' => sprintf(
                            /* translators: 1: template name, 2: brand. */
                            __('%1$s website template by %2$s', 'foundations'),
                            (string) ($template['name'] ?? ''),
                            defined('FM_BRAND') ? FM_BRAND : 'Foundations Marketing'
                        )],
                        true
                    );
                    ?>
                <?php else : ?>
                    <div class="fm-builder__frame-empty" aria-hidden="true"></div>
                <?php endif; ?>
            </div>

            <div class="fm-builder__included">
                <h3 class="fm-builder__sub">
                    <?php
                    printf(
                        /* translators: %s: the base price, already formatted. */
                        esc_html__('What you are getting for %s', 'foundations'),
                        '&pound;' . esc_html((string) $base_price)
                    );
                    ?>
                </h3>

                <?php if ($included !== []) : ?>
                    <p class="fm-builder__list-label"><?php esc_html_e('Included as standard', 'foundations'); ?></p>
                    <ul class="fm-builder__ticks">
                        <?php foreach ($included as $item) : ?>
                            <li><?php echo esc_html((string) $item); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if ($free_items !== []) : ?>
                    <p class="fm-builder__list-label"><?php esc_html_e('Also free, no decision needed', 'foundations'); ?></p>
                    <ul class="fm-builder__ticks fm-builder__ticks--free">
                        <?php foreach ($free_items as $item) : ?>
                            <li><?php echo esc_html((string) $item); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </section>

        <?php // --- Step 2: extras ------------------------------------------------ ?>
        <section class="fm-builder__panel" data-fm-panel="2"
                 aria-label="<?php esc_attr_e('Add extras', 'foundations'); ?>" hidden>

            <h2 class="fm-builder__heading"><?php echo esc_html((string) ($attributes['extrasHeading'] ?? '')); ?></h2>

            <?php if ($addons !== []) : ?>
                <p class="fm-builder__list-label"><?php esc_html_e('Services', 'foundations'); ?></p>
                <ul class="fm-builder__cards">
                    <?php foreach ($addons as $addon) :
                        $id = (string) ($addon['id'] ?? '');
                        if ($id === '') {
                            continue;
                        }
                        $price = max(0, (int) ($addon['price'] ?? 0));
                        ?>
                        <li>
                            <?php // A real button, so it is keyboard reachable and announces its state. ?>
                            <button type="button" class="fm-builder__card"
                                    data-fm-addon="<?php echo esc_attr($id); ?>"
                                    data-price="<?php echo esc_attr((string) $price); ?>"
                                    data-name="<?php echo esc_attr((string) ($addon['name'] ?? '')); ?>"
                                    aria-pressed="false">
                                <span class="fm-builder__card-top">
                                    <span class="fm-builder__card-name"><?php echo esc_html((string) ($addon['name'] ?? '')); ?></span>
                                    <span class="fm-builder__card-price">&pound;<?php echo esc_html((string) $price); ?></span>
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
                <p class="fm-builder__list-label">
                    <?php
                    printf(
                        /* translators: %s: price of one extra page, already formatted. */
                        esc_html__('Extra pages — %s each', 'foundations'),
                        '&pound;' . esc_html((string) $page_price)
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
                                <span class="fm-builder__card-top">
                                    <span class="fm-builder__card-name"><?php echo esc_html((string) ($page['name'] ?? '')); ?></span>
                                    <span class="fm-builder__card-price">&pound;<?php echo esc_html((string) $page_price); ?></span>
                                    <span class="fm-builder__mark" aria-hidden="true"></span>
                                </span>
                                <span class="fm-builder__card-body"><?php echo esc_html((string) ($page['body'] ?? '')); ?></span>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <?php // --- Step 3: review ------------------------------------------------ ?>
        <section class="fm-builder__panel" data-fm-panel="3"
                 aria-label="<?php esc_attr_e('Review and pay', 'foundations'); ?>" hidden>

            <h2 class="fm-builder__heading"><?php echo esc_html((string) ($attributes['reviewHeading'] ?? '')); ?></h2>

            <div class="fm-builder__summary">
                <h3 class="fm-builder__sub"><?php esc_html_e('Order summary', 'foundations'); ?></h3>

                <?php // Rewritten by builder.js as choices change; this is the no-JS state. ?>
                <ul class="fm-builder__lines" data-fm-lines>
                    <li class="fm-builder__line">
                        <span class="fm-builder__line-label">
                            <?php
                            printf(
                                /* translators: 1: template name, 2: what the base price covers. */
                                esc_html__('%1$s template — %2$s', 'foundations'),
                                esc_html($template['name'] ?? __('Template', 'foundations')),
                                esc_html($base_label)
                            );
                            ?>
                        </span>
                        <span class="fm-builder__line-amount">&pound;<?php echo esc_html((string) $base_price); ?></span>
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
                    <span data-fm-total>&pound;<?php echo esc_html((string) $base_price); ?></span>
                </p>
            </div>

            <?php if ($after !== []) : ?>
                <div class="fm-builder__after">
                    <h3 class="fm-builder__sub"><?php esc_html_e('What happens after you pay', 'foundations'); ?></h3>
                    <ol class="fm-builder__after-list">
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
                    '<span data-fm-total>&pound;' . esc_html((string) $base_price) . '</span>'
                );
                ?>
                &rarr;
            </button>

            <?php if ($product_id <= 0) : ?>
                <p class="fm-builder__warn">
                    <?php esc_html_e('No product is linked yet — set one in the block sidebar, or this button cannot take payment.', 'foundations'); ?>
                </p>
            <?php endif; ?>
        </section>
    </form>
</div>
