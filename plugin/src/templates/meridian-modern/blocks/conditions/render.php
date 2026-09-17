<?php
if (!defined('ABSPATH')) { exit; }
$a = $attributes;
$text = static fn($key) => esc_html((string)($a[$key] ?? ''));
$items = array_values(array_filter((array)($a['items'] ?? []), 'is_array'));
$anchor = sanitize_title((string)($a['anchor'] ?? ''));
?>
<section class="meridian meridian-section meridian-conditions" id="<?php echo esc_attr($anchor); ?>"><div class="meridian-container"><p class="meridian-eyebrow"><?php echo $text('eyebrow'); ?></p><h2><?php echo $text('heading'); ?></h2><ul class="meridian-pills"><?php foreach($items as $item): ?><li><?php echo esc_html($item['title']??''); ?></li><?php endforeach; ?></ul><p class="meridian-body"><?php echo $text('body'); ?></p></div></section>
