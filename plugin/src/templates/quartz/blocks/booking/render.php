<?php
/**
 * Quartz booking band. The button link is the block's own URL, else the Site Settings
 * booking link, else the phone/email so the button never points nowhere.
 */
if (!defined('ABSPATH')) {
    exit;
}

$a      = $attributes;
$text   = static fn(string $key): string => trim((string) ($a[$key] ?? ''));
$anchor = sanitize_title($text('anchor') ?: 'book');
$url    = $text('buttonUrl');

if ($url === '' && function_exists('fm_setting')) {
    $url = (string) fm_setting('booking_url', '');
    if ($url === '' && (string) fm_setting('email', '') !== '') {
        $url = 'mailto:' . antispambot((string) fm_setting('email'));
    }
}

$external = $url !== '' && !str_starts_with($url, '#') && !str_starts_with($url, 'mailto:') && wp_parse_url($url, PHP_URL_HOST) && wp_parse_url($url, PHP_URL_HOST) !== wp_parse_url(home_url(), PHP_URL_HOST);
?>
<section class="quartz quartz-booking" id="<?php echo esc_attr($anchor); ?>">
    <div class="quartz-booking__copy">
        <?php if ($text('eyebrow') !== '') : ?>
            <p class="quartz-eyebrow"><?php echo esc_html($text('eyebrow')); ?></p>
        <?php endif; ?>
        <h2><?php echo esc_html($text('heading')); ?></h2>
        <?php if ($text('body') !== '') : ?>
            <p><?php echo esc_html($text('body')); ?></p>
        <?php endif; ?>
    </div>
    <div class="quartz-booking__box">
        <?php if ($text('boxText') !== '') : ?>
            <p><?php echo esc_html($text('boxText')); ?></p>
        <?php endif; ?>
        <?php if ($text('buttonLabel') !== '' && $url !== '') : ?>
            <a class="quartz-button quartz-button--fill" href="<?php echo esc_url($url); ?>"<?php echo $external ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html($text('buttonLabel')); ?></a>
        <?php endif; ?>
    </div>
</section>
