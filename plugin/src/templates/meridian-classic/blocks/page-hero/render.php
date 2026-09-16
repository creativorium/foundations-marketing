<?php
if (!defined('ABSPATH')) { exit; }
$a = $attributes;
$text = static fn($key) => esc_html((string)($a[$key] ?? ''));
$items = array_values(array_filter((array)($a['items'] ?? []), 'is_array'));
$anchor = sanitize_title((string)($a['anchor'] ?? ''));
?>
<section class="meridian meridian-page-hero"><div class="meridian-container"><p class="meridian-eyebrow"><?php echo $text('eyebrow'); ?></p><h1><?php echo $text('heading'); ?></h1><p class="meridian-body"><?php echo $text('body'); ?></p></div></section>
