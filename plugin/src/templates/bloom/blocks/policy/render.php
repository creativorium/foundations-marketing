<?php
if (!defined('ABSPATH')) { exit; } $a=$attributes; $sections=array_values(array_filter((array)($a['sections']??[]),'is_array'));
?>
<article class="bloom bloom-policy"><header><p class="bloom-label bloom-label--clay">Legal</p><h1><?php echo esc_html((string)($a['heading']??'')); ?></h1><p><?php echo esc_html((string)($a['intro']??'')); ?></p></header><?php foreach($sections as $section): ?><section><h2><?php echo esc_html((string)($section['title']??'')); ?></h2><p><?php echo esc_html((string)($section['body']??'')); ?></p></section><?php endforeach; ?></article>
