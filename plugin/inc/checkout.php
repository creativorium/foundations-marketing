<?php
/**
 * The WooCommerce side of the package builder.
 *
 * The builder block posts nothing but ids: which template, which add-ons, which extra
 * pages. Every price is looked up here, from the block's own attributes on the checkout
 * page, and never read from the request. A posted price is a price the buyer chose.
 *
 * The chosen extras ride along as cart item data, which WooCommerce copies onto the order
 * line at checkout — so the build team sees exactly what was bought without a second
 * system to keep in sync.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * The package-builder block's attributes, read from the checkout page itself.
 *
 * This is the price list. It lives in one place — the block on the page — so editing the
 * add-on prices in the editor changes what is charged, with no code deploy.
 *
 * @return array<string, mixed>
 */
function fm_builder_config(): array
{
    static $config = null;

    if ($config !== null) {
        return $config;
    }

    $config = [];

    $page_id = function_exists('wc_get_page_id') ? (int) wc_get_page_id('checkout') : 0;

    if ($page_id > 0) {
        $post = get_post($page_id);

        if ($post instanceof WP_Post && has_blocks($post->post_content)) {
            foreach (parse_blocks($post->post_content) as $block) {
                if (($block['blockName'] ?? '') === 'foundations/package-builder') {
                    $config = (array) ($block['attrs'] ?? []);
                    break;
                }
            }
        }
    }

    return $config;
}

/**
 * Price of one add-on or extra page, by id. Returns null for anything not on the list —
 * which is how an invented id gets dropped rather than charged at zero.
 */
function fm_builder_price(string $group, string $id): ?int
{
    $config = fm_builder_config();

    if ($group === 'pages') {
        foreach ((array) ($config['extraPages'] ?? []) as $page) {
            if ((string) ($page['id'] ?? '') === $id) {
                return max(0, (int) ($config['extraPagePrice'] ?? 50));
            }
        }

        return null;
    }

    foreach ((array) ($config['addons'] ?? []) as $addon) {
        if ((string) ($addon['id'] ?? '') === $id) {
            return max(0, (int) ($addon['price'] ?? 0));
        }
    }

    return null;
}

/**
 * Human label for an id, for the order line.
 */
function fm_builder_label(string $group, string $id): string
{
    $config = fm_builder_config();
    $list   = $group === 'pages' ? ($config['extraPages'] ?? []) : ($config['addons'] ?? []);

    foreach ((array) $list as $row) {
        if ((string) ($row['id'] ?? '') === $id) {
            return (string) ($row['name'] ?? $id);
        }
    }

    return $id;
}

/**
 * Attach the builder's choices to the cart item.
 *
 * Runs on add-to-cart. Unknown ids are discarded here rather than later, so nothing
 * invalid ever reaches the cart in the first place.
 *
 * @param array<string, mixed> $cart_item_data
 * @return array<string, mixed>
 */
function fm_builder_cart_item_data(array $cart_item_data): array
{
    if (empty($_POST['fm_build'])) {
        return $cart_item_data;
    }

    if (!isset($_POST['fm_build_nonce'])
        || !wp_verify_nonce(sanitize_text_field(wp_unslash((string) $_POST['fm_build_nonce'])), 'fm_build_order')) {
        return $cart_item_data;
    }

    $chosen = [];

    foreach (['addons' => 'fm_addons', 'pages' => 'fm_pages'] as $group => $field) {
        foreach ((array) ($_POST[$field] ?? []) as $raw) {
            $id    = sanitize_key(wp_unslash((string) $raw));
            $price = fm_builder_price($group, $id);

            if ($price === null) {
                continue;
            }

            $chosen[] = [
                'group' => $group,
                'id'    => $id,
                'label' => fm_builder_label($group, $id),
                'price' => $price,
            ];
        }
    }

    $template = isset($_POST['fm_template'])
        ? sanitize_title(wp_unslash((string) $_POST['fm_template']))
        : '';

    if ($template !== '') {
        $cart_item_data['fm_template'] = $template;
    }

    if ($chosen !== []) {
        $cart_item_data['fm_extras'] = $chosen;
    }

    // Without this, WooCommerce merges two differently-configured builds into one line
    // with quantity 2, and one of the configurations is silently lost.
    if (isset($cart_item_data['fm_template']) || isset($cart_item_data['fm_extras'])) {
        $cart_item_data['fm_key'] = md5(wp_json_encode([$template, $chosen]) ?: '');
    }

    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'fm_builder_cart_item_data');

/**
 * Add the extras to the line price.
 *
 * `woocommerce_before_calculate_totals` is the only correct place: it runs before every
 * total is worked out, including at checkout and on the order, so the figure the buyer
 * sees and the figure they are charged cannot drift apart.
 */
function fm_builder_apply_prices(WC_Cart $cart): void
{
    if (is_admin() && !wp_doing_ajax()) {
        return;
    }

    foreach ($cart->get_cart() as $item) {
        if (empty($item['fm_extras']) || !isset($item['data']) || !$item['data'] instanceof WC_Product) {
            continue;
        }

        $extra = array_sum(array_column((array) $item['fm_extras'], 'price'));

        if ($extra > 0) {
            $item['data']->set_price((float) $item['data']->get_price() + (float) $extra);
        }
    }
}
add_action('woocommerce_before_calculate_totals', 'fm_builder_apply_prices');

/**
 * Show the choices in the cart, at checkout and on the order.
 *
 * @param array<int, array<string, string>> $data
 * @param array<string, mixed>              $item
 * @return array<int, array<string, string>>
 */
function fm_builder_item_data(array $data, array $item): array
{
    if (!empty($item['fm_template'])) {
        $data[] = [
            'key'   => __('Template', 'foundations'),
            'value' => esc_html(ucwords(str_replace('-', ' ', (string) $item['fm_template']))),
        ];
    }

    foreach ((array) ($item['fm_extras'] ?? []) as $extra) {
        $data[] = [
            'key'   => esc_html((string) ($extra['label'] ?? '')),
            'value' => esc_html(strip_tags(wc_price((float) ($extra['price'] ?? 0)))),
        ];
    }

    return $data;
}
add_filter('woocommerce_get_item_data', 'fm_builder_item_data', 10, 2);

/**
 * Copy the choices onto the order line, so they survive the cart being emptied.
 *
 * @param array<string, mixed> $values
 */
function fm_builder_order_item_meta(WC_Order_Item_Product $item, string $cart_item_key, array $values): void
{
    if (!empty($values['fm_template'])) {
        $item->add_meta_data(__('Template', 'foundations'), (string) $values['fm_template']);
    }

    foreach ((array) ($values['fm_extras'] ?? []) as $extra) {
        $item->add_meta_data(
            (string) ($extra['label'] ?? ''),
            strip_tags(wc_price((float) ($extra['price'] ?? 0)))
        );
    }
}
add_action('woocommerce_checkout_create_order_line_item', 'fm_builder_order_item_meta', 10, 3);
