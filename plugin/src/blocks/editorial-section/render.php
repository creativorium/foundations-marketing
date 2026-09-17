<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
$tone = in_array($attributes['tone'] ?? '', ['paper', 'panel', 'accent'], true) ? $attributes['tone'] : 'paper';
$layout = ($attributes['layout'] ?? '') === 'split' ? 'split' : 'stack';
$extra = !empty($attributes['anchor']) ? ['id' => sanitize_title((string) $attributes['anchor'])] : [];
?>
<section <?php echo fm_wrapper(['fm-editorial-section', 'fm-editorial-section--' . $tone, 'fm-editorial-section--' . $layout], $extra); ?>>
    <div class="fm-editorial-section__intro">
        <?php if (!empty($attributes['eyebrow'])) : ?><p class="fm-editorial-section__eyebrow"><?php echo esc_html($attributes['eyebrow']); ?></p><?php endif; ?>
        <?php if (!empty($attributes['heading'])) : ?><h2 class="fm-editorial-section__heading"><?php echo esc_html($attributes['heading']); ?></h2><?php endif; ?>
        <?php if (!empty($attributes['intro'])) : ?><p><?php echo esc_html($attributes['intro']); ?></p><?php endif; ?>
    </div>
    <div class="fm-editorial-section__content"><?php echo $content; ?></div>
</section>
