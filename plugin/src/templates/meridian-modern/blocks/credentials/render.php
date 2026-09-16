<?php
if (!defined('ABSPATH')) { exit; }
$a = $attributes;
$text = static fn($key) => esc_html((string)($a[$key] ?? ''));
$items = array_values(array_filter((array)($a['items'] ?? []), 'is_array'));
$anchor = sanitize_title((string)($a['anchor'] ?? ''));
?>
<div class="meridian meridian-credentials"><ul><?php foreach($items as $item): ?><li><?php echo esc_html($item['title']??''); ?></li><?php endforeach; ?></ul></div>
