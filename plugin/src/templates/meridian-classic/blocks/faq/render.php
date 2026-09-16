<?php
if (!defined('ABSPATH')) { exit; }
$a = $attributes;
$text = static fn($key) => esc_html((string)($a[$key] ?? ''));
$items = array_values(array_filter((array)($a['items'] ?? []), 'is_array'));
$anchor = sanitize_title((string)($a['anchor'] ?? ''));
?>
<section class="meridian meridian-section meridian-faq-wrap" id="<?php echo esc_attr($anchor); ?>"><div class="meridian-faq"><div class="meridian-centered"><p class="meridian-eyebrow"><?php echo $text('eyebrow'); ?></p><h2><?php echo $text('heading'); ?></h2></div><?php foreach($items as $item): ?><details><summary><?php echo esc_html($item['title']??''); ?><svg class="meridian-faq__icon" aria-hidden="true" viewBox="0 0 18 18"><path d="M9 3v12M3 9h12"/></svg></summary><p><?php echo esc_html($item['body']??''); ?></p></details><?php endforeach; ?></div></section>
