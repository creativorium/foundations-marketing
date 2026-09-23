<?php
/**
 * Quartz site header. The primary navigation is split in half around the wordmark on
 * wide screens; under 768px it collapses behind a toggle (view.js). Without JavaScript
 * the links simply stay visible, stacked under the wordmark.
 */
if (!defined('ABSPATH')) {
    exit;
}

$nav   = fm_nav('nav_primary');
$half  = (int) ceil(count($nav) / 2);
$lists = [array_slice($nav, 0, $half), array_slice($nav, $half)];
$brand = (string) fm_setting('brand_label', (string) fm_setting('site_name', get_bloginfo('name')));
$logo  = (int) fm_setting('logo_id', 0);

$list = static function (array $items, string $modifier): void {
    if (!$items) {
        return;
    }
    ?>
    <ul class="quartz-header__list quartz-header__list--<?php echo esc_attr($modifier); ?>">
        <?php foreach ($items as $item) : ?>
            <li><a href="<?php echo esc_url($item['url']); ?>"<?php echo $item['current'] ? ' aria-current="page"' : ''; ?>><?php echo esc_html($item['label']); ?></a></li>
        <?php endforeach; ?>
    </ul>
    <?php
};
?>
<div class="quartz-header" data-quartz-header>
    <nav class="quartz-header__nav" aria-label="<?php esc_attr_e('Primary', 'foundations'); ?>">
        <a class="quartz-header__brand" href="<?php echo esc_url(home_url('/')); ?>">
            <?php if ($logo > 0) : ?>
                <?php echo wp_get_attachment_image($logo, 'medium', false, ['class' => 'quartz-header__logo', 'alt' => $brand, 'loading' => 'eager']); ?>
            <?php else : ?>
                <?php echo esc_html($brand); ?>
            <?php endif; ?>
        </a>
        <?php if ($nav) : ?>
            <button class="quartz-header__toggle" type="button" aria-expanded="false" aria-controls="quartz-menu" hidden>
                <span class="quartz-header__bars" aria-hidden="true"><span></span><span></span></span>
                <span class="quartz-header__toggle-label"><?php esc_html_e('Menu', 'foundations'); ?></span>
            </button>
            <div class="quartz-header__menu" id="quartz-menu">
                <?php $list($lists[0], 'start'); ?>
                <?php $list($lists[1], 'end'); ?>
            </div>
        <?php endif; ?>
    </nav>
</div>
