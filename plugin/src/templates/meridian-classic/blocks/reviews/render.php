<?php
if (!defined('ABSPATH')) { exit; }
$items = array_values(array_filter((array)($attributes['items'] ?? []), 'is_array'));
?>
<?php if ($items) : ?><section class="meridian meridian-section meridian-reviews" aria-label="Client experiences"><div class="meridian-review" data-meridian-reviews><span class="meridian-review__quote" aria-hidden="true">“</span><div aria-live="polite" aria-atomic="true"><?php foreach ($items as $i => $item) : ?><figure data-review <?php echo $i ? 'hidden' : ''; ?>><blockquote><?php echo esc_html($item['title'] ?? ''); ?></blockquote><figcaption><?php echo esc_html($item['body'] ?? ''); ?></figcaption></figure><?php endforeach; ?></div><div class="meridian-review__nav"><button type="button" data-review-prev>← Prev</button><span data-review-counter>1 / <?php echo esc_html((string)count($items)); ?></span><button type="button" data-review-next>Next →</button></div></div></section><?php endif; ?>
