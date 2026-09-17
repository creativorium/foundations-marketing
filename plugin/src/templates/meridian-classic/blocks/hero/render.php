<?php
if (!defined('ABSPATH')) { exit; }
$a = $attributes;
$text = static fn($key) => esc_html((string)($a[$key] ?? ''));
?>
<section class="meridian meridian-hero" id="top">
  <div class="meridian-hero__image"><?php echo wp_get_attachment_image((int)($a['imageId'] ?? 0), 'full', false, ['loading' => 'eager', 'fetchpriority' => 'high']); ?></div>
  <div class="meridian-hero__shade" aria-hidden="true"></div>
  <div class="meridian-hero__text"><p class="meridian-eyebrow"><?php echo $text('eyebrow'); ?></p><h1><?php echo $text('heading'); ?> <em><?php echo $text('highlight'); ?></em> <?php echo $text('ending'); ?></h1></div>
</section>
<section class="meridian meridian-hero-intro"><div><p><?php echo $text('body'); ?></p><div class="meridian-actions"><?php foreach (['primary', 'secondary'] as $kind) : if (!empty($a[$kind . 'Url']) && !empty($a[$kind . 'Label'])) : ?><a href="<?php echo esc_url($a[$kind . 'Url']); ?>"><?php echo $text($kind . 'Label'); ?></a><?php endif; endforeach; ?></div></div></section>
