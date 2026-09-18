<?php
/**
 * Shared helpers for block render.php files.
 *
 * Front-end contributors call these instead of touching the database directly —
 * that is what keeps a component folder self-contained (see CONTRIBUTING.md §3).
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Build a class attribute from the block wrapper plus extra classes.
 * Always use this so alignment, custom colours and editor-set classes survive.
 */
function fm_wrapper(array $extra_classes = [], array $extra_attributes = []): string
{
    $classes = array_filter($extra_classes);

    return get_block_wrapper_attributes(array_merge(
        $classes ? ['class' => implode(' ', $classes)] : [],
        $extra_attributes
    ));
}

/**
 * Section eyebrow — the "06 — Questions" rule that opens most bands in the design.
 * Returns escaped markup; pass plain strings.
 */
function fm_section_rule(string $number, string $label, string $aside = ''): string
{
    ob_start(); ?>
    <div class="fm-section-rule">
        <span><?php echo esc_html($number . ' — ' . $label); ?></span>
        <?php if ($aside !== '') : ?>
            <span class="fm-section-rule__aside"><?php echo esc_html($aside); ?></span>
        <?php endif; ?>
    </div>
    <?php
    return (string) ob_get_clean();
}

/**
 * Responsive image by attachment id, sized for a block, with width/height so the
 * browser reserves space (no layout shift) and lazy/async by default.
 *
 * Pass $eager = true for anything above the fold — the hero image must NOT be lazy,
 * it is the LCP element.
 */
function fm_image(int $attachment_id, string $size = 'large', array $attr = [], bool $eager = false): string
{
    if ($attachment_id <= 0) {
        return '';
    }

    $defaults = $eager
        ? ['loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async']
        : ['loading' => 'lazy', 'decoding' => 'async'];

    return (string) wp_get_attachment_image($attachment_id, $size, false, array_merge($defaults, $attr));
}

/** Escaped internal link, falling back to '#' so a half-configured block never breaks the page. */
function fm_url(string $url): string
{
    return $url !== '' ? esc_url($url) : '#';
}

/**
 * The site templates offered in the catalogue.
 *
 * Reads the `site_template` CPT registered by the Foundation Packages plugin, so blocks
 * never touch the database or WP_Query themselves — that is the boundary that lets a
 * front-end contributor work in one folder (CONTRIBUTING.md §3).
 *
 * Returns a plain array of scalars, ready to echo. Every value is already the raw string;
 * escape at the point of output, not here.
 *
 * @param int $limit Maximum templates to return. -1 for all.
 *
 * @return array<int, array{
 *     id:int, name:string, niche:string, description:string, category:string,
 *     sections:array<int,string>, url:string, thumb_id:int, packages:array<int,string>
 * }>
 */
function fm_get_templates(int $limit = 9): array
{
    if (!post_type_exists('site_template')) {
        return (array) apply_filters('fm_catalogue_templates', [], $limit);
    }

    $query = new WP_Query([
        'post_type'              => 'site_template',
        'post_status'            => 'publish',
        'posts_per_page'         => $limit,
        'orderby'                => 'menu_order title',
        'order'                  => 'ASC',
        'ignore_sticky_posts'    => true,
        // We only need the post rows plus meta; skip the term cache entirely.
        'update_post_term_cache' => false,
        'no_found_rows'          => true,
    ]);

    $templates = [];

    foreach ($query->posts as $post) {
        $packages = get_post_meta($post->ID, 'tpl_packages', true);
        $sections = get_post_meta($post->ID, 'tpl_sections', true);

        // Sections may be stored as an array or as one-per-line text, depending on
        // how the template was entered. Accept either.
        if (is_string($sections) && $sections !== '') {
            $sections = preg_split('/

|
|
/', $sections) ?: [];
        }

        $sections = is_array($sections)
            ? array_values(array_filter(array_map('trim', array_map('strval', $sections))))
            : [];

        $templates[] = [
            'id'          => (int) $post->ID,
            'name'        => (string) $post->post_title,
            // The stable public handle. The checkout is reached as ?template=<slug>,
            // which stays readable and survives an id changing between environments.
            'slug'        => (string) $post->post_name,
            'niche'       => (string) get_post_meta($post->ID, 'tpl_niche', true),
            'description' => (string) get_post_meta($post->ID, 'tpl_description', true),
            'category'    => (string) get_post_meta($post->ID, 'tpl_category', true),
            'sections'    => $sections,
            'url'         => (string) get_post_meta($post->ID, 'tpl_demo_url', true),
            'thumb_id'    => (int) get_post_thumbnail_id($post->ID),
            'packages'    => is_array($packages) ? array_map('strval', $packages) : [],
        ];
    }

    wp_reset_postdata();

    return (array) apply_filters('fm_catalogue_templates', $templates, $limit);
}

/** The marketing site's custom enquiry form, shared by header and CTA links. */
function fm_contact_form_url(): string
{
    $page = get_page_by_path('contact-page', OBJECT, 'page');

    return $page instanceof WP_Post && $page->post_status === 'publish'
        ? (string) get_permalink($page)
        : home_url('/contact-page/');
}

/**
 * The template the buyer arrived with, from `?template=<slug>`.
 *
 * The slug is matched against the real catalogue rather than trusted: a query string is
 * reader-supplied, and everything downstream — the preview image, the name printed on the
 * order line — would otherwise be whatever the URL said it was.
 *
 * Falls back to the first template in the catalogue so the checkout is never empty when
 * someone lands on it directly. Returns [] only when there is no catalogue at all.
 *
 * @return array<string, mixed>
 */
function fm_selected_template(): array
{
    $templates = fm_get_templates(50);

    if ($templates === []) {
        return [];
    }

    // Unslash then sanitise: WordPress adds slashes to superglobals.
    $wanted = isset($_GET['template'])
        ? sanitize_title(wp_unslash((string) $_GET['template']))
        : '';

    if ($wanted !== '') {
        foreach ($templates as $template) {
            if (($template['slug'] ?? '') === $wanted) {
                return $template;
            }
        }
    }

    return $templates[0];
}

/**
 * Where a template card should send the reader.
 *
 * One place decides this, because it is asked in two blocks — template-grid and
 * template-library — and they must never disagree about where a card goes.
 *
 * The card leads to the builder, not to the demo: choosing a template is the step the
 * catalogue exists to produce, and the demo is offered beside it as its own link
 * (fm_template_demo_url()) so looking around never costs the buyer their place.
 *
 * Filterable so the destination can go back to the demo without editing a block:
 * `add_filter('fm_template_card_url', fn($url, $t) => $t['url']);`
 *
 * @param array<string, mixed> $template
 */
function fm_template_card_url(array $template): string
{
    $builder = fm_builder_url();
    $slug    = (string) ($template['slug'] ?? '');

    $url = ($builder !== '' && $slug !== '')
        ? add_query_arg('template', $slug, $builder)
        : (string) ($template['url'] ?? '');

    /**
     * Filters where a template card links.
     *
     * @param string               $url      The resolved destination.
     * @param array<string, mixed> $template The template row.
     */
    return (string) apply_filters('fm_template_card_url', $url, $template);
}

/**
 * The standalone demo for a template, or '' when it has none.
 *
 * The card's demo link and the builder's preview both ask this, and both must stay
 * pointed at the same mini site — the buyer compares the two side by side.
 *
 * @param array<string, mixed> $template
 */
function fm_template_demo_url(array $template): string
{
    $url = (string) ($template['url'] ?? '');

    /**
     * Filters a template's demo URL.
     *
     * @param string               $url      The demo destination, '' when there is none.
     * @param array<string, mixed> $template The template row.
     */
    return (string) apply_filters('fm_template_demo_url', $url, $template);
}

/**
 * The checkout page URL, preferring WooCommerce's own setting over a guessed path.
 */
function fm_checkout_url(): string
{
    if (function_exists('wc_get_checkout_url')) {
        $url = (string) wc_get_checkout_url();

        if ($url !== '') {
            return $url;
        }
    }

    return (string) home_url('/checkout/');
}

/**
 * The page the package builder sits on.
 *
 * Deliberately NOT the WooCommerce checkout page. The builder's form posts to the real
 * checkout to take payment, so if the builder lived there it would post to itself and
 * the buyer would be handed the configurator again instead of a payment form.
 *
 * The id is recorded whenever a page containing the block is saved (below), so the owner
 * can put the builder on any page with any slug and the template cards follow it. Falls
 * back to nothing, which makes the cards link to the demo as they always did — a missing
 * builder page degrades to the old behaviour rather than to a dead link.
 */
function fm_builder_url(): string
{
    $page_id = (int) get_option('fm_builder_page_id', 0);

    if ($page_id > 0 && get_post_status($page_id) === 'publish') {
        return (string) get_permalink($page_id);
    }

    return '';
}

/**
 * The page holding the catalogue — where "back" from a demo should land.
 *
 * Found the same way as the builder rather than assumed to be `/templates/`. That
 * assumption was wrong on the dev site, which has no such page, so every demo's back
 * link led to a 404 — and a hardcoded path is wrong again the moment the page is
 * renamed. Falls back to the homepage, which always exists.
 */
function fm_catalogue_url(): string
{
    $page_id = (int) get_option('fm_catalogue_page_id', 0);

    // The option is written when a page is saved, so a site whose catalogue page was
    // published before this existed has nothing recorded yet. Look once, remember the
    // answer, and never search again — re-saving every page to teach the site where
    // its own catalogue is would be a silly thing to ask of anyone.
    if ($page_id <= 0) {
        $found = get_posts([
            'post_type'        => 'page',
            'post_status'      => 'publish',
            'numberposts'      => 1,
            's'                => 'wp:foundations/template-library',
            'fields'           => 'ids',
            'suppress_filters' => true,
        ]);

        if ($found !== []) {
            $page_id = (int) $found[0];
            update_option('fm_catalogue_page_id', $page_id, false);
        }
    }

    if ($page_id > 0 && get_post_status($page_id) === 'publish') {
        return (string) get_permalink($page_id);
    }

    return (string) home_url('/');
}

/**
 * Remember which page holds the builder, and which holds the catalogue.
 *
 * Cheaper and more reliable than searching post content on the front end: the answer
 * changes only when a page is saved, so that is when it is worked out.
 *
 * The catalogue is the page carrying `template-library` — the full, filterable list.
 * `template-grid` is deliberately not accepted: it appears as a short teaser on the
 * homepage and the services page, and sending a reader "back to the catalogue" should
 * not land them on a three-card excerpt of it.
 */
function fm_track_builder_page(int $post_id, WP_Post $post): void
{
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }

    $published = $post->post_status === 'publish';

    foreach ([
        'fm_builder_page_id'   => 'foundations/package-builder',
        'fm_catalogue_page_id' => 'foundations/template-library',
    ] as $option => $block) {
        $stored = (int) get_option($option, 0);

        if ($published && has_block($block, $post)) {
            update_option($option, $post_id, false);
            continue;
        }

        // The block was removed, or the page was unpublished — stop pointing at it.
        if ($stored === $post_id) {
            delete_option($option);
        }
    }
}
add_action('save_post_page', 'fm_track_builder_page', 10, 2);
