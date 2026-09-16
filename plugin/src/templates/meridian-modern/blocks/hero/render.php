<?php
if (!defined('ABSPATH')) { exit; }
$a = $attributes;
$text = static fn($key) => esc_html((string)($a[$key] ?? ''));
$items = array_values(array_filter((array)($a['items'] ?? []), 'is_array'));
$anchor = sanitize_title((string)($a['anchor'] ?? ''));
?>
<section class="meridian meridian-hero" id="top"><div class="meridian-hero__text"><p class="meridian-eyebrow"><?php echo $text('eyebrow'); ?></p><h1><?php echo $text('heading'); ?><br><em><?php echo $text('highlight'); ?></em> <?php echo $text('ending'); ?></h1><p class="meridian-body"><?php echo $text('body'); ?></p><div class="meridian-actions"><?php foreach (['primary','secondary'] as $kind): if (!empty($a[$kind.'Url']) && !empty($a[$kind.'Label'])): ?><a class="meridian-button <?php echo $kind === 'secondary' ? 'meridian-button--outline' : ''; ?>" href="<?php echo esc_url($a[$kind.'Url']); ?>"><?php echo $text($kind.'Label'); ?></a><?php endif; endforeach; ?></div></div><div class="meridian-hero__image"><?php echo wp_get_attachment_image((int)($a['imageId']??0),'full',false,['loading'=>'eager','fetchpriority'=>'high']); ?><?php if (!empty($a['badge'])): ?><div class="meridian-badge"><strong><?php echo $text('badge'); ?></strong><span><?php echo $text('badgeLabel'); ?></span></div><?php endif; ?></div></section>
