<?php
if (!defined('ABSPATH')) { exit; } $a=$attributes; $text=static fn(string $key): string => esc_html((string)($a[$key]??'')); $anchor=sanitize_title((string)($a['anchor']??'contact')); $email=(string)fm_setting('email','');
?>
<section class="bloom bloom-contact" id="<?php echo esc_attr($anchor); ?>"><div><h2><?php echo $text('heading'); ?></h2><p><?php echo $text('body'); ?></p></div><div class="bloom-contact__action"><?php if($email!==''): ?><a href="mailto:<?php echo esc_attr(antispambot($email)); ?>"><?php echo esc_html(antispambot($email)); ?></a><?php endif; ?><span><?php echo $text('note'); ?></span></div></section>
