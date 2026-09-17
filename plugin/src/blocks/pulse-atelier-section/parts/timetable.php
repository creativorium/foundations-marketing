<?php if (!defined('ABSPATH')) { exit; } ?>
<section class="fm-pa-timetable__section-1" data-pa-part="timetable">
    <div class="fm-pa-timetable__div-2">
      <div class="fm-pa-timetable__div-3"><?= esc_html($fields['06_this_week']); ?></div>
      <div class="fm-pa-timetable__div-4">
        <?php foreach ($lists['week'] as $d_index => $d): ?>
          <div class="fm-pa-timetable__div-5">
            <div class="fm-pa-timetable__div-6"><span><?= esc_html($d['day']); ?></span></div>
            <?php foreach ($d['slots'] as $sl_index => $sl): ?>
              <div class="fm-pa-timetable__div-7">
                <div class="fm-pa-timetable__div-8"><span><?= esc_html($sl['time']); ?></span></div>
                <div class="fm-pa-timetable__div-9"><span><?= esc_html($sl['name']); ?></span></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="fm-pa-timetable__div-10"><?= esc_html($fields['studio_closed_thursdays_and_sundays_one_to_ones_']); ?></div>
    </div>
  </section>
