<?php
/**
 * 404 — page not found.
 *
 * SEO notes (strategy §4):
 *  - This must return a real HTTP 404. WordPress does that for us; never "helpfully"
 *    redirect a missing URL to the homepage — Google calls that a soft 404 and it
 *    hides broken links instead of reporting them.
 *  - The page is noindex: a 404 should never appear in search results.
 *  - It is still a real page for a real person who has landed somewhere wrong, so it
 *    offers search and the routes they most likely wanted. Those internal links also
 *    keep crawlers moving rather than dead-ending.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<section class="fm-error">
    <div class="fm-error__rule">
        <span><?php esc_html_e('Error 404', 'foundations'); ?></span>
        <span class="fm-error__rule-aside"><?php esc_html_e('Page not found', 'foundations'); ?></span>
    </div>

    <div class="fm-error__grid">
        <div>
            <h1 class="fm-error__title">
                <?php esc_html_e('This page', 'foundations'); ?>
                <span class="fm-error__title-accent"><?php esc_html_e('moved on', 'foundations'); ?></span>
            </h1>

            <p class="fm-error__lede">
                <?php esc_html_e(
                    'The link is broken or the page has been retired. Nothing is wrong on your end — try a search, or pick up one of the routes below.',
                    'foundations'
                ); ?>
            </p>

            <form class="fm-error__search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                <label class="fm-sr-only" for="fm-404-search">
                    <?php esc_html_e('Search this site', 'foundations'); ?>
                </label>
                <input
                    class="fm-error__search-field"
                    id="fm-404-search"
                    type="search"
                    name="s"
                    value="<?php echo esc_attr(get_search_query()); ?>"
                    placeholder="<?php esc_attr_e('Search…', 'foundations'); ?>"
                    autocomplete="off">
                <button class="fm-error__search-button" type="submit">
                    <?php esc_html_e('Search', 'foundations'); ?>
                </button>
            </form>
        </div>

        <nav class="fm-error__routes" aria-label="<?php esc_attr_e('Helpful links', 'foundations'); ?>">
            <?php
            $routes = [
                ['/#templates', __('The templates', 'foundations'),  __('Browse the library by niche', 'foundations')],
                ['/#price',     __('Pricing', 'foundations'),        __('One number, no tiers', 'foundations')],
                ['/#how',       __('How it works', 'foundations'),   __('Chosen to launched in three steps', 'foundations')],
                ['/#faq',       __('Questions', 'foundations'),      __('Answered up front', 'foundations')],
            ];

            foreach ($routes as [$url, $label, $hint]) :
                ?>
                <a class="fm-error__route" href="<?php echo esc_url(home_url($url)); ?>">
                    <span class="fm-error__route-label"><?php echo esc_html($label); ?></span>
                    <span class="fm-error__route-hint"><?php echo esc_html($hint); ?></span>
                    <span class="fm-error__route-arrow" aria-hidden="true">&rarr;</span>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
</section>
<?php
get_footer();
