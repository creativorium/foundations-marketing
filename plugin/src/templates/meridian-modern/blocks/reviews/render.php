<?php
if (!defined('ABSPATH')) { exit; }
$a = $attributes;
$text = static fn($key) => esc_html((string)($a[$key] ?? ''));
$items = array_values(array_filter((array)($a['items'] ?? []), 'is_array'));
$anchor = sanitize_title((string)($a['anchor'] ?? ''));
?>
<?php if($items): ?><section class="meridian meridian-section" aria-label="Client experiences"><div class="meridian-review" data-meridian-reviews><?php echo wp_get_attachment_image((int)($a['imageId']??0),'thumbnail',false,['loading'=>'lazy']); ?><div aria-live="polite" aria-atomic="true"><?php foreach($items as $i=>$item): ?><figure data-review <?php echo $i ? 'hidden' : ''; ?>><blockquote><?php echo esc_html($item['title']??''); ?></blockquote><figcaption><?php echo esc_html($item['body']??''); ?></figcaption></figure><?php endforeach; ?></div><div class="meridian-dots"><?php foreach($items as $i=>$item): ?><button type="button" data-review-select="<?php echo esc_attr((string)$i); ?>" aria-label="Show testimonial <?php echo esc_attr((string)($i+1)); ?>" aria-pressed="<?php echo $i ? 'false' : 'true'; ?>"><span></span></button><?php endforeach; ?></div></div></section><?php endif; ?>
