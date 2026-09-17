<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
require_once dirname(__DIR__) . '/editorial-hero/media.php';
$layout = in_array($attributes['layout'] ?? '', ['statement', 'profile', 'audience'], true) ? $attributes['layout'] : 'profile';
$items = array_values(array_filter(is_array($attributes['items'] ?? null) ? $attributes['items'] : [], 'is_array'));
$paragraphs = array_values(array_filter(is_array($attributes['paragraphs'] ?? null) ? $attributes['paragraphs'] : [], 'is_array'));
$gallery = array_values(array_filter(is_array($attributes['gallery'] ?? null) ? $attributes['gallery'] : [], 'is_array'));
$extra = !empty($attributes['anchor']) ? ['id' => sanitize_title((string) $attributes['anchor'])] : [];
?>
<section <?php echo fm_wrapper(['fm-editorial-story', 'fm-editorial-story--' . $layout], $extra); ?>>
    <?php if ($layout === 'statement') : ?>
        <p class="fm-editorial-story__eyebrow"><?php echo esc_html((string) ($attributes['eyebrow'] ?? '')); ?></p>
    <?php else : ?>
        <figure class="fm-editorial-story__photo"><?php echo fm_editorial_image($attributes); ?></figure>
    <?php endif; ?>
    <div class="fm-editorial-story__copy">
        <?php if ($layout !== 'statement' && !empty($attributes['eyebrow'])) : ?><p class="fm-editorial-story__eyebrow"><?php echo esc_html($attributes['eyebrow']); ?></p><?php endif; ?>
        <?php if (!empty($attributes['heading'])) : ?><h2 class="fm-editorial-story__heading"><?php echo esc_html($attributes['heading']); ?></h2><?php endif; ?>
        <?php if (!empty($attributes['intro'])) : ?><p><?php echo esc_html($attributes['intro']); ?></p><?php endif; ?>
        <?php if ($paragraphs) : ?><div class="fm-editorial-story__paragraphs">
            <?php foreach ($paragraphs as $paragraph) : ?><?php if (!empty($paragraph['text'])) : ?><p><?php echo esc_html((string) $paragraph['text']); ?></p><?php endif; ?><?php endforeach; ?>
        </div><?php endif; ?>
        <?php if ($items && $layout === 'profile') : ?>
            <dl class="fm-editorial-story__stats">
                <?php foreach ($items as $item) : ?><div><dt><?php echo esc_html((string) ($item['body'] ?? '')); ?></dt><dd><?php echo esc_html((string) ($item['title'] ?? '')); ?></dd></div><?php endforeach; ?>
            </dl>
        <?php elseif ($items) : ?>
            <ul class="fm-editorial-story__list">
                <?php foreach ($items as $item) : ?><li><h3><?php echo esc_html((string) ($item['title'] ?? '')); ?></h3><p><?php echo esc_html((string) ($item['body'] ?? '')); ?></p></li><?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <?php if ($layout === 'statement' && $gallery) : ?>
        <div class="fm-editorial-story__gallery">
            <?php foreach ($gallery as $photo) : ?><figure><?php echo fm_editorial_image($photo); ?></figure><?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
