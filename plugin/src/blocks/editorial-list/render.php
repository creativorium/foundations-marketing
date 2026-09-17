<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
$layout = in_array($attributes['layout'] ?? '', ['credentials', 'services', 'process'], true) ? $attributes['layout'] : 'services';
$items = array_values(array_filter(is_array($attributes['items'] ?? null) ? $attributes['items'] : [], static fn ($item): bool => is_array($item) && trim((string) ($item['title'] ?? '')) !== ''));
if (!$items) { return; }
$extra = !empty($attributes['anchor']) ? ['id' => sanitize_title((string) $attributes['anchor'])] : [];
$tag = $layout === 'process' ? 'ol' : 'ul';
?>
<section <?php echo fm_wrapper(['fm-editorial-list', 'fm-editorial-list--' . $layout], $extra); ?>>
    <?php if (!empty($attributes['eyebrow'])) : ?><p class="fm-editorial-list__eyebrow"><?php echo esc_html($attributes['eyebrow']); ?></p><?php endif; ?>
    <?php if (!empty($attributes['heading']) || !empty($attributes['intro'])) : ?><div class="fm-editorial-list__intro">
        <?php if (!empty($attributes['heading'])) : ?><h2 class="fm-editorial-list__heading"><?php echo esc_html($attributes['heading']); ?></h2><?php endif; ?>
        <?php if (!empty($attributes['intro'])) : ?><p><?php echo esc_html($attributes['intro']); ?></p><?php endif; ?>
    </div><?php endif; ?>
    <<?php echo $tag; ?> class="fm-editorial-list__items">
        <?php foreach ($items as $i => $item) : ?>
            <li class="fm-editorial-list__item">
                <?php if ($layout === 'process') : ?><span class="fm-editorial-list__number" aria-hidden="true"><?php echo esc_html(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)); ?></span><?php endif; ?>
                <?php if ($layout === 'credentials') : ?>
                    <span><?php echo esc_html($item['title']); ?></span>
                <?php else : ?>
                    <h3 class="fm-editorial-list__title"><?php if (!empty($item['url'])) : ?><a href="<?php echo fm_url($item['url']); ?>"><?php echo esc_html($item['title']); ?><span aria-hidden="true"> &rarr;</span></a><?php else : ?><?php echo esc_html($item['title']); ?><?php endif; ?></h3>
                    <p class="fm-editorial-list__body"><?php echo esc_html((string) ($item['body'] ?? '')); ?></p>
                    <?php if ($layout === 'services') : ?><span class="fm-editorial-list__duration"><?php echo esc_html((string) ($item['duration'] ?? '')); ?></span><span class="fm-editorial-list__price"><?php echo esc_html((string) ($item['price'] ?? '')); ?></span><?php endif; ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </<?php echo $tag; ?>>
</section>
