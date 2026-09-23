<?php
/**
 * Quartz site footer. Everything comes from Site Settings; the credit is the
 * "Website by Foundations Marketing" backlink the SEO strategy asks for on client builds.
 */
if (!defined('ABSPATH')) {
    exit;
}

$name     = (string) fm_setting('site_name', get_bloginfo('name'));
$brand    = (string) fm_setting('footer_brand', $name);
$location = (string) fm_setting('location_line', '');
$nav      = fm_nav('nav_footer');
?>
<div class="quartz-footer">
    <p class="quartz-footer__brand"><?php echo esc_html($brand); ?></p>
    <p class="quartz-footer__copy"><?php echo esc_html(implode(' · ', array_filter(['© ' . wp_date('Y'), $location]))); ?></p>
    <?php if ($nav) : ?>
        <nav class="quartz-footer__nav" aria-label="<?php esc_attr_e('Footer', 'foundations'); ?>">
            <ul>
                <?php foreach ($nav as $item) : ?>
                    <li><a href="<?php echo esc_url($item['url']); ?>"<?php echo $item['current'] ? ' aria-current="page"' : ''; ?>><?php echo esc_html($item['label']); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
    <?php endif; ?>
    <p class="quartz-footer__credit"><a href="https://foundationsmarketing.co.uk/" rel="noopener">Website by Foundations Marketing</a></p>
</div>
