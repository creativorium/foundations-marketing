<?php
if (!defined('ABSPATH')) { exit; } $a=$attributes; $text=static fn(string $key): string => esc_html((string)($a[$key]??''));
?>
<figure class="bloom bloom-testimonial"><span aria-hidden="true">“</span><blockquote><?php echo $text('quote'); ?></blockquote><figcaption><strong><?php echo $text('name'); ?></strong><small><?php echo $text('detail'); ?></small></figcaption></figure>
