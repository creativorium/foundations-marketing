<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
require_once __DIR__ . '/helpers.php';
$defaults = fm_pa_defaults('pulse-atelier');
$settings = is_array($attributes['settings'] ?? null) ? $attributes['settings'] : [];
?>
<div <?php echo fm_wrapper(['fm-pulse-atelier']); ?> data-pa-root>
<?php fm_pa_part('header', $defaults, is_array($settings['header'] ?? null) ? $settings['header'] : [], __DIR__); ?>
<div id="top" data-pa-view="home"><?php echo $content; ?></div>
<?php
foreach (['contact', 'privacy', 'footer'] as $part) {
    fm_pa_part($part, $defaults, is_array($settings[$part] ?? null) ? $settings[$part] : [], __DIR__);
}
?>
</div>
