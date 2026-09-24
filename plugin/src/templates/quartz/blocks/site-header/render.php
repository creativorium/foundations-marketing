<?php
/**
 * Quartz site header. The primary navigation is split in half around the wordmark on
 * wide screens; under 768px the links are hidden and only the wordmark shows, exactly
 * as the source design does.
 */
if (!defined('ABSPATH')) {
    exit;
}

$nav   = fm_nav('nav_primary');
$half  = (int) ceil(count($nav) / 2);
$lists = [array_slice($nav, 0, $half), array_slice($nav, $half)];
$brand = (string) fm_setting('brand_label', (string) fm_setting('site_name', get_bloginfo('name')));
$logo  = (int) fm_setting('logo_id', 0);
$home = home_url('/');
// A catalogue preview must stay inside this design when its wordmark is clicked.
$context = (int) ($GLOBALS['fm_delivery_context'] ?? 0);
if ($context) {
    $content = (array) get_post_meta($context, '_fm_content', true);
    if (!empty($content['homepage'])) {
        $home = get_permalink((int) $content['homepage']);
    }
}

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
        <a class="quartz-header__brand" href="<?php echo esc_url($home); ?>">
            <?php if ($logo > 0) : ?>
                <?php echo wp_get_attachment_image($logo, 'medium', false, ['class' => 'quartz-header__logo', 'alt' => $brand, 'loading' => 'eager']); ?>
            <?php else : ?>
                <?php echo esc_html($brand); ?>
            <?php endif; ?>
        </a>
        <?php $list($lists[0], 'start'); ?>
        <?php $list($lists[1], 'end'); ?>
    </nav>
</div>
