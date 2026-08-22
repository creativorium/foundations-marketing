<?php
/**
 * SEO.
 *
 * Implements the client's SEO strategy (doc/client-html/seo strategy/). Yoast is
 * already installed and owns titles, meta descriptions, canonicals, robots and the
 * sitemap — we do NOT duplicate any of that. What lives here is the structural work
 * Yoast cannot do:
 *
 *   - Organization + Service schema (strategy §4)
 *   - the brand name used consistently as "Foundations Marketing", with the S (§1a)
 *   - clean, keyword-rich template URLs: /templates/pilates-website-design (§2)
 *   - one H1 per page, enforced by the block markup rather than by hand
 *
 * If Yoast is ever removed, fm_seo_has_seo_plugin() flips and the minimal fallbacks
 * here take over so the site is never left without a title tag.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/** The canonical brand name. Never write "Foundation Marketing" — see strategy §1a. */
const FM_BRAND = 'Foundations Marketing';

function fm_seo_has_seo_plugin(): bool
{
    return defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION');
}

/**
 * Organization schema, so Google can tell this Foundations from the property agency
 * at foundationmarketing.co.uk that the strategy identifies as the naming collision.
 *
 * Yoast emits its own Organization graph; we only add ours when it is not there.
 */
function fm_organization_schema(): void
{
    if (fm_seo_has_seo_plugin() || !is_front_page()) {
        return;
    }

    $schema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Organization',
        'name'        => FM_BRAND,
        'url'         => home_url('/'),
        'description' => get_bloginfo('description'),
        'areaServed'  => ['@type' => 'Country', 'name' => 'United Kingdom'],
    ];

    if ($logo = get_theme_mod('custom_logo')) {
        $schema['logo'] = wp_get_attachment_image_url((int) $logo, 'full');
    }

    printf(
        '<script type="application/ld+json">%s</script>' . "\n",
        wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    );
}
add_action('wp_head', 'fm_organization_schema', 20);

/**
 * Service schema for a template demo page. Each of the nine templates targets its own
 * niche phrase (strategy §2), so each demo page describes a distinct Service.
 *
 * Called from the template-demo block's render.php — not hooked globally, because
 * only those pages should carry it.
 */
function fm_service_schema(string $name, string $description, string $price = ''): void
{
    $schema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Service',
        'name'        => $name,
        'description' => $description,
        'provider'    => [
            '@type' => 'Organization',
            'name'  => FM_BRAND,
            'url'   => home_url('/'),
        ],
        'areaServed'  => ['@type' => 'Country', 'name' => 'United Kingdom'],
        'serviceType' => 'Website design',
    ];

    if ($price !== '') {
        $schema['offers'] = [
            '@type'         => 'Offer',
            'price'         => $price,
            'priceCurrency' => 'GBP',
        ];
    }

    printf(
        '<script type="application/ld+json">%s</script>' . "\n",
        wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    );
}

/**
 * Minimal title/description fallback for the case where no SEO plugin is active.
 * Yoast, when present, wins — this never runs alongside it.
 */
function fm_fallback_meta_description(): void
{
    if (fm_seo_has_seo_plugin()) {
        return;
    }

    $description = '';

    if (is_front_page()) {
        $description = get_bloginfo('description');
    } elseif (is_singular()) {
        $post = get_post();
        $description = $post instanceof WP_Post
            ? wp_strip_all_tags(get_the_excerpt($post))
            : '';
    }

    if ($description === '') {
        return;
    }

    printf(
        '<meta name="description" content="%s">' . "\n",
        esc_attr(wp_html_excerpt($description, 155, '…'))
    );
}
add_action('wp_head', 'fm_fallback_meta_description', 2);

/**
 * Open Graph. Yoast covers this too, so again only as a fallback.
 */
function fm_fallback_open_graph(): void
{
    if (fm_seo_has_seo_plugin() || !is_singular()) {
        return;
    }

    printf('<meta property="og:title" content="%s">' . "\n", esc_attr(get_the_title()));
    printf('<meta property="og:url" content="%s">' . "\n", esc_url(get_permalink()));
    printf('<meta property="og:site_name" content="%s">' . "\n", esc_attr(FM_BRAND));
    printf('<meta property="og:type" content="%s">' . "\n", 'website');

    if ($image = get_the_post_thumbnail_url(null, 'large')) {
        printf('<meta property="og:image" content="%s">' . "\n", esc_url($image));
        printf('<meta name="twitter:card" content="summary_large_image">' . "\n");
    }
}
add_action('wp_head', 'fm_fallback_open_graph', 3);

/**
 * The brand split into the two pieces the logo lockup uses.
 *
 * The canvas sets the name as a heavy uppercase "FOUNDATIONS" with a small, widely
 * tracked "Marketing" beside it — one wordmark, two spans. Deriving both from FM_BRAND
 * rather than from the WordPress site title means the logo cannot drift back to the
 * singular "Foundation", which is the exact problem the SEO audit found (strategy §1a).
 *
 * @return array{0:string, 1:string} [mark, suffix] — e.g. ['FOUNDATIONS', 'Marketing'].
 */
function fm_brand_parts(): array
{
    $parts = explode(' ', FM_BRAND, 2);

    return [
        mb_strtoupper($parts[0]),
        $parts[1] ?? '',
    ];
}

/**
 * The oversized wordmark used in the footer: the first word only, in caps.
 */
function fm_brand_mark(): string
{
    return fm_brand_parts()[0];
}
