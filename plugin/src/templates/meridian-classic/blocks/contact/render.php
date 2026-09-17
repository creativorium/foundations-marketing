<?php
if (!defined('ABSPATH')) { exit; }
$a = $attributes;
$text = static fn($key) => esc_html((string)($a[$key] ?? ''));
$items = array_values(array_filter((array)($a['items'] ?? []), 'is_array'));
$anchor = sanitize_title((string)($a['anchor'] ?? ''));
?>
<section class="meridian meridian-section"><dl class="meridian-container meridian-contact"><?php foreach(['address'=>'Clinic','email'=>'Email','phone'=>'Phone','opening_hours'=>'Hours'] as $key=>$label): $value=function_exists('fm_setting')?(string)fm_setting($key):''; if(!$value)continue; ?><div><dt><?php echo esc_html($label); ?></dt><dd><?php if($key==='email'||$key==='phone'): ?><a href="<?php echo esc_url(($key==='email'?'mailto:':'tel:').($key==='phone'?preg_replace('/[^+0-9]/','',$value):$value)); ?>"><?php echo esc_html($value); ?></a><?php else: echo nl2br(esc_html($value)); endif; ?></dd></div><?php endforeach; ?></dl></section>
