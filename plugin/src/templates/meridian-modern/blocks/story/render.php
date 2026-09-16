<?php
if (!defined('ABSPATH')) { exit; }
$a = $attributes;
$text = static fn($key) => esc_html((string)($a[$key] ?? ''));
$items = array_values(array_filter((array)($a['items'] ?? []), 'is_array'));
$anchor = sanitize_title((string)($a['anchor'] ?? ''));
?>
<section class="meridian meridian-section" id="<?php echo esc_attr($anchor); ?>"><div class="meridian-container meridian-story"><div class="meridian-story__image"><?php echo wp_get_attachment_image((int)($a['imageId']??0),'large',false,['loading'=>'lazy']); ?></div><div><p class="meridian-eyebrow"><?php echo $text('eyebrow'); ?></p><h2><?php echo $text('heading'); ?></h2><p class="meridian-body"><?php echo $text('body'); ?></p><p class="meridian-body"><?php echo $text('body2'); ?></p><p class="meridian-signature"><?php echo $text('signature'); ?></p></div></div></section>
