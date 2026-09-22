<?php
if (!defined('ABSPATH')) { exit; } $a=$attributes; $text=static fn(string $key): string => esc_html((string)($a[$key]??'')); $items=array_values(array_filter((array)($a['items']??[]),'is_array')); $anchor=sanitize_title((string)($a['anchor']??'who'));
?>
<section class="bloom bloom-audience" id="<?php echo esc_attr($anchor); ?>"><p class="bloom-label"><?php echo $text('eyebrow'); ?></p><h2><?php echo $text('heading'); ?></h2><div class="bloom-audience__grid"><?php foreach($items as $item): ?><article><span class="bloom-leaf" aria-hidden="true"></span><h3><?php echo esc_html((string)($item['title']??'')); ?></h3><p><?php echo esc_html((string)($item['body']??'')); ?></p></article><?php endforeach; ?></div></section>
