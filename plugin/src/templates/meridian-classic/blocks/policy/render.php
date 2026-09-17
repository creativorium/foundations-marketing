<?php
if (!defined('ABSPATH')) { exit; }
$a = $attributes;
$text = static fn($key) => esc_html((string)($a[$key] ?? ''));
$items = array_values(array_filter((array)($a['items'] ?? []), 'is_array'));
$anchor = sanitize_title((string)($a['anchor'] ?? ''));
?>
<section class="meridian meridian-section"><div class="meridian-policy"><?php foreach($items as $item): ?><section><h2><?php echo esc_html($item['title']??''); ?></h2><p><?php echo esc_html($item['body']??''); ?></p></section><?php endforeach; ?></div></section>
