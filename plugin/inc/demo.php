<?php
/**
 * Live demos for the sold templates.
 *
 * A template is a self-contained mini site with its own header, hero and footer, so a
 * demo cannot be an ordinary page on this site: wrapping it in the Foundations Marketing
 * header and footer would show the buyer our chrome around their site and hide the very
 * thing they are judging. The demo is therefore rendered as a standalone document with
 * no theme header or footer at all.
 *
 * Two URLs, deliberately not one:
 *
 *   /templates/<keyword-slug>/          the CPT single — our branded detail page. Owns the
 *                                       target phrase, carries the description, screenshot
 *                                       and the CTA. This is what ranks. (See §10.)
 *   /templates/<keyword-slug>/demo/     this file — the standalone mini site.
 *
 * The demo is noindex: it is the same content with no branding, no description and no
 * internal links, so letting it compete with the page built to rank would split the
 * signal for the phrase and land buyers somewhere with no way to buy. Yoast owns robots
 * for real posts, but this route is not a post and Yoast never sees it, so emitting the
 * tag here is not the duplicate §10 warns about.
 *
 * Content comes from the template's own content.blocks.txt ON DISK, not from the
 * database. That is what makes a contributor's demo appear the moment the file exists —
 * no page to create, nothing to paste, no WP-CLI — and it is the same path that serves
 * buyers in production, so what a contributor signs off is what ships.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Every template that can be demoed: its folder slug, the URL slug it answers on, and
 * its display name.
 *
 * The URL slug comes from template.json (`demoSlug`, falling back to `slug`) so a
 * template answers on its SEO phrase — /templates/pilates-website-design/demo/ — rather
 * than on the folder name. Without one it falls back to the folder, which keeps a
 * half-finished template previewable instead of 404ing while someone fills in metadata.
 *
 * @return array<string, array{dir: string, name: string, folder: string}> url slug => template
 */
function fm_demo_templates(): array
{
    static $templates = null;

    if ($templates !== null) {
        return $templates;
    }

    $templates = [];

    foreach (glob(FM_BLOCKS_DIR . 'src/templates/*', GLOB_ONLYDIR) ?: [] as $dir) {
        if (!is_readable($dir . '/content.blocks.txt')) {
            continue;
        }

        $folder = basename($dir);
        $slug   = $folder;
        $name   = ucwords(str_replace('-', ' ', $folder));

        if (is_readable($dir . '/template.json')) {
            $data = json_decode((string) file_get_contents($dir . '/template.json'), true);

            if (is_array($data)) {
                foreach (['demoSlug', 'slug'] as $key) {
                    if (!empty($data[$key]) && is_string($data[$key])) {
                        $slug = sanitize_title($data[$key]);
                        break;
                    }
                }

                if (!empty($data['name']) && is_string($data['name'])) {
                    $name = $data['name'];
                }
            }
        }

        $templates[$slug] = ['dir' => $dir, 'name' => $name, 'folder' => $folder];
    }

    return $templates;
}

/**
 * `/templates/<slug>/demo/` — matched ahead of the CPT's own single, which would
 * otherwise swallow the /demo/ suffix as part of the post name and 404.
 */
function fm_demo_rewrite(): void
{
    add_rewrite_rule(
        '^templates/([^/]+)/demo/?$',
        'index.php?fm_demo=$matches[1]',
        'top'
    );
}
add_action('init', 'fm_demo_rewrite');

function fm_demo_query_var(array $vars): array
{
    $vars[] = 'fm_demo';

    return $vars;
}
add_filter('query_vars', 'fm_demo_query_var');

/**
 * Rewrite rules live in the database, so a rule added by a code deploy does nothing until
 * they are flushed. Flushing on every request is expensive, so we flush once, when the
 * set of demoable templates changes — which is exactly when a new template lands.
 */
function fm_demo_maybe_flush(): void
{
    $signature = md5((string) wp_json_encode(array_keys(fm_demo_templates())));

    if (get_option('fm_demo_routes') !== $signature) {
        flush_rewrite_rules(false);
        update_option('fm_demo_routes', $signature, false);
    }
}
add_action('init', 'fm_demo_maybe_flush', 99);

/**
 * Render the demo, standalone, and stop. Nothing of the theme is loaded — no
 * get_header(), no get_footer() — because the template supplies its own.
 */
function fm_demo_render(): void
{
    $slug = get_query_var('fm_demo');

    if ($slug === '' || $slug === null) {
        return;
    }

    $templates = fm_demo_templates();
    $slug      = sanitize_title((string) $slug);

    // An unknown slug is a real 404, not an empty demo: a dead "Live Preview" link
    // should be visible to us in the logs rather than rendering a blank page that
    // looks like a broken template.
    if (!isset($templates[$slug])) {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        nocache_headers();

        return;
    }

    $template = $templates[$slug];
    $content  = (string) file_get_contents($template['dir'] . '/content.blocks.txt');

    // do_blocks() runs each block's render.php. A template block that was never
    // registered server-side renders as nothing — see inc/register.php.
    $rendered = do_blocks($content);

    status_header(200);
    nocache_headers();

    fm_demo_document($template, $rendered);
    exit;
}
add_action('template_redirect', 'fm_demo_render');

/**
 * The standalone document wrapping a demo.
 *
 * wp_head()/wp_footer() still run, so block styles and any block's own script load
 * exactly as they do on a real page — the demo has to be the real thing, not an
 * approximation of it, or signing one off proves nothing.
 *
 * @param array{dir: string, name: string, folder: string} $template
 */
function fm_demo_document(array $template, string $rendered): void
{
    $brand = defined('FM_BRAND') ? FM_BRAND : 'Foundations Marketing';
    $title = sprintf(
        /* translators: 1: template name, 2: brand name. */
        __('%1$s — live demo | %2$s', 'foundations'),
        $template['name'],
        $brand
    );

    ?><!DOCTYPE html>
<html <?php language_attributes(); ?> data-fm-demo="<?php echo esc_attr($template['folder']); ?>">
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?php echo esc_html($title); ?></title>
<?php wp_head(); ?>
</head>
<body <?php body_class('fm-demo'); ?>>
<?php echo $rendered; // Block output, already escaped by each block's render.php. ?>
<?php echo fm_demo_bar($template); ?>
<?php wp_footer(); ?>
</body>
</html>
<?php
}

/**
 * A fixed bar over the demo, so the buyer is never stranded inside a site that has no
 * link back to ours — every link in a template points at the fictional business, not at
 * us, so without this the only way out is the back button.
 *
 * @param array{dir: string, name: string, folder: string} $template
 */
function fm_demo_bar(array $template): string
{
    $buy = function_exists('fm_checkout_url') ? fm_checkout_url() : home_url('/');
    $buy = add_query_arg('template', rawurlencode($template['folder']), $buy);

    ob_start();
    ?>
<aside class="fm-demo-bar" aria-label="<?php esc_attr_e('Template demo', 'foundations'); ?>">
  <span class="fm-demo-bar__label">
    <?php
    printf(
        /* translators: %s: template name. */
        esc_html__('Demo — %s', 'foundations'),
        '<strong>' . esc_html($template['name']) . '</strong>'
    );
    ?>
  </span>
  <span class="fm-demo-bar__actions">
    <a class="fm-demo-bar__link" href="<?php echo esc_url(home_url('/templates/')); ?>">
      <?php esc_html_e('All templates', 'foundations'); ?>
    </a>
    <a class="fm-demo-bar__cta" href="<?php echo esc_url($buy); ?>">
      <?php esc_html_e('Choose this template', 'foundations'); ?>
    </a>
  </span>
</aside>
    <?php

    return (string) ob_get_clean();
}
