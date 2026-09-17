<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
$days = array_values(array_filter(is_array($attributes['days'] ?? null) ? $attributes['days'] : [], static fn ($day): bool => is_array($day) && !empty($day['day'])));
if (!$days) { return; }
$extra = !empty($attributes['anchor']) ? ['id' => sanitize_title((string) $attributes['anchor'])] : [];
?>
<section <?php echo fm_wrapper(['fm-editorial-timetable'], $extra); ?>>
    <?php if (!empty($attributes['eyebrow'])) : ?><p class="fm-editorial-timetable__eyebrow"><?php echo esc_html($attributes['eyebrow']); ?></p><?php endif; ?>
    <?php if (!empty($attributes['heading'])) : ?><h2 class="fm-editorial-timetable__heading"><?php echo esc_html($attributes['heading']); ?></h2><?php endif; ?>
    <?php if (!empty($attributes['intro'])) : ?><p><?php echo esc_html($attributes['intro']); ?></p><?php endif; ?>
    <ul class="fm-editorial-timetable__days">
        <?php foreach ($days as $day) : ?>
            <li class="fm-editorial-timetable__day">
                <h3><?php echo esc_html((string) $day['day']); ?></h3>
                <ul>
                    <?php foreach (is_array($day['slots'] ?? null) ? $day['slots'] : [] as $slot) : ?>
                        <?php if (!is_array($slot)) { continue; } $time = (string) ($slot['time'] ?? ''); ?>
                        <li>
                            <?php if (preg_match('/\A(?:[01][0-9]|2[0-3]):[0-5][0-9]\z/', $time)) : ?><time datetime="<?php echo esc_attr($time); ?>"><?php echo esc_html($time); ?></time><?php else : ?><span class="fm-editorial-timetable__time"><?php echo esc_html($time); ?></span><?php endif; ?>
                            <span><?php echo esc_html((string) ($slot['name'] ?? '')); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php if (!empty($attributes['note'])) : ?><p class="fm-editorial-timetable__note"><?php echo esc_html($attributes['note']); ?></p><?php endif; ?>
</section>
