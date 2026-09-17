<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
$items = array_values(array_filter(is_array($attributes['items'] ?? null) ? $attributes['items'] : [], static fn ($item): bool => is_array($item) && trim((string) ($item['text'] ?? '')) !== ''));
if (!$items) { return; }
$uid = wp_unique_id('fm-editorial-quotes-');
$extra = !empty($attributes['anchor']) ? ['id' => sanitize_title((string) $attributes['anchor'])] : [];
?>
<section <?php echo fm_wrapper(['fm-editorial-quotes'], $extra); ?>>
    <?php if (!empty($attributes['eyebrow'])) : ?><p class="fm-editorial-quotes__eyebrow"><?php echo esc_html($attributes['eyebrow']); ?></p><?php endif; ?>
    <?php if (!empty($attributes['heading'])) : ?><h2 class="fm-editorial-quotes__heading"><?php echo esc_html($attributes['heading']); ?></h2><?php endif; ?>
    <?php if (!empty($attributes['intro'])) : ?><p><?php echo esc_html($attributes['intro']); ?></p><?php endif; ?>
    <fieldset class="fm-editorial-quotes__switcher">
        <legend class="fm-sr-only"><?php esc_html_e('Choose a testimonial. Use the arrow keys to switch.', 'foundations'); ?></legend>
        <?php foreach ($items as $i => $item) : ?>
            <?php $id = $uid . '-' . $i; ?>
            <input class="fm-editorial-quotes__radio" type="radio" name="<?php echo esc_attr($uid); ?>" id="<?php echo esc_attr($id); ?>" <?php checked($i, 0); ?> aria-controls="<?php echo esc_attr($id . '-quote'); ?>">
            <label class="fm-editorial-quotes__dot" for="<?php echo esc_attr($id); ?>"><span class="fm-sr-only"><?php echo esc_html(sprintf(__('Testimonial %1$d: %2$s', 'foundations'), $i + 1, (string) ($item['name'] ?? ''))); ?></span></label>
            <figure class="fm-editorial-quotes__quote" id="<?php echo esc_attr($id . '-quote'); ?>">
                <blockquote><p><?php echo esc_html((string) $item['text']); ?></p></blockquote>
                <figcaption><?php echo esc_html((string) ($item['name'] ?? '')); ?></figcaption>
            </figure>
        <?php endforeach; ?>
    </fieldset>
</section>
