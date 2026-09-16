<?php
if (!defined('ABSPATH')) { exit; }
$a = $attributes;
$text = static fn($key) => esc_html((string)($a[$key] ?? ''));
$items = array_values(array_filter((array)($a['items'] ?? []), 'is_array'));
$anchor = sanitize_title((string)($a['anchor'] ?? ''));
?>
<section class="meridian meridian-section meridian-process" id="<?php echo esc_attr($anchor); ?>"><div class="meridian-container"><div class="meridian-centered"><p class="meridian-eyebrow"><?php echo $text('eyebrow'); ?></p><h2><?php echo $text('heading'); ?></h2></div><ol class="meridian-steps" tabindex="0" aria-label="Treatment process"><?php foreach($items as $i=>$item): ?><li><span class="meridian-step-number" aria-hidden="true"><?php echo esc_html(sprintf('%02d',$i+1)); ?></span><h3><?php echo esc_html($item['title']??''); ?></h3><p><?php echo esc_html($item['body']??''); ?></p></li><?php endforeach; ?></ol></div></section>
