<?php if (!defined('ABSPATH')) { exit; } ?>
<section class="fm-pk-timetable" data-pk-part="timetable">
  <div class="fm-pk-container">
    <div class="fm-pk-timetable__head"><h2><?php echo esc_html($fields['heading']); ?></h2><a href="<?php echo esc_url($fields['link_url']); ?>"><?php echo esc_html($fields['link_label']); ?></a></div>
    <div class="fm-pk-timetable__week">
      <?php foreach ($lists['week'] as $day) : ?>
        <div class="fm-pk-day"><h3><?php echo esc_html($day['day']); ?></h3><div><?php foreach ($day['slots'] as $slot) : if ($slot['text'] === '') { continue; } ?><span><?php echo esc_html($slot['text']); ?></span><?php endforeach; ?></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
