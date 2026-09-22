<?php
if (!defined('ABSPATH')) { exit; }
$a=$attributes; $text=static fn(string $key): string => esc_html((string)($a[$key]??''));
?>
<section class="bloom bloom-hero" id="top"><div class="bloom-hero__text"><p class="bloom-label"><?php echo $text('eyebrow'); ?></p><h1><?php echo $text('heading'); ?> <em><?php echo $text('highlight'); ?></em></h1><p class="bloom-hero__body"><?php echo $text('body'); ?></p></div><div class="bloom-hero__visual" aria-hidden="true"><span class="bloom-blob bloom-blob--one"></span><span class="bloom-blob bloom-blob--two"></span><span class="bloom-blob bloom-blob--three"></span><figure class="bloom-hero__photo"><?php echo wp_get_attachment_image((int)($a['imageId']??0),'full',false,['loading'=>'eager','fetchpriority'=>'high']); ?></figure></div></section>
