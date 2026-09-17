<?php
/** Editable, page-local tokens. The site's root palette and shell are untouched. */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }

$tokens = ['paper' => '--fm-bg', 'ink' => '--fm-ink', 'accent' => '--fm-accent', 'panel' => '--fm-bg-2'];
$style = '';
foreach ($tokens as $key => $token) {
    $value = (string) ($attributes[$key] ?? '');
    $color = preg_match('/^#[a-f0-9]{6}$/i', $value) ? sanitize_hex_color($value) : '';
    if ($color) { $style .= $token . ':' . $color . ';'; }
}
$extra = $style !== '' ? ['style' => $style] : [];
if (!empty($attributes['anchor'])) { $extra['id'] = sanitize_title((string) $attributes['anchor']); }
?>
<div <?php echo fm_wrapper(['fm-editorial-frame'], $extra); ?>>
    <?php echo $content; // Already rendered by WordPress, including nested dynamic blocks. ?>
</div>
