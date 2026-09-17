<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
require_once __DIR__ . '/media.php';
$layout = ($attributes['layout'] ?? '') === 'banner' ? 'banner' : 'split';
$tag = $layout === 'split' ? 'h1' : 'h2';
if (trim((string) ($attributes['heading'] ?? '') . (string) ($attributes['accentText'] ?? '')) === '') { return; }
$extra = !empty($attributes['anchor']) ? ['id' => sanitize_title((string) $attributes['anchor'])] : [];
?>
<section <?php echo fm_wrapper(['fm-editorial-hero', 'fm-editorial-hero--' . $layout], $extra); ?>>
    <div class="fm-editorial-hero__copy">
        <?php if (!empty($attributes['eyebrow'])) : ?><p class="fm-editorial-hero__eyebrow"><?php echo esc_html($attributes['eyebrow']); ?></p><?php endif; ?>
        <<?php echo $tag; ?> class="fm-editorial-hero__heading"><?php echo esc_html((string) ($attributes['heading'] ?? '')); ?><?php if (!empty($attributes['accentText'])) : ?><em><?php echo esc_html($attributes['accentText']); ?></em><?php endif; ?><?php echo esc_html((string) ($attributes['headingEnd'] ?? '')); ?></<?php echo $tag; ?>>
        <?php if (!empty($attributes['intro'])) : ?><p class="fm-editorial-hero__intro"><?php echo esc_html($attributes['intro']); ?></p><?php endif; ?>
        <div class="fm-editorial-hero__actions">
            <?php foreach (['primary', 'secondary'] as $action) : ?>
                <?php if (!empty($attributes[$action . 'Text']) && !empty($attributes[$action . 'Url'])) : ?>
                    <a class="fm-editorial-hero__<?php echo $action; ?>" href="<?php echo fm_url($attributes[$action . 'Url']); ?>"><?php echo esc_html($attributes[$action . 'Text']); ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php if (!empty($attributes['footnote'])) : ?><p class="fm-editorial-hero__footnote"><?php echo esc_html($attributes['footnote']); ?></p><?php endif; ?>
    </div>
    <div class="fm-editorial-hero__photo"><?php echo fm_editorial_image($attributes, $layout === 'split'); ?></div>
</section>
