<?php if (!defined('ABSPATH')) { exit; } ?>
<section class="fm-pa-sessions__section-1" id="sessions" data-pa-part="sessions">
    <div class="fm-pa-sessions__div-2">
      <div class="fm-pa-sessions__div-3">
        <div class="fm-pa-sessions__div-4"><?= esc_html($fields['03_sessions']); ?></div>
        <h2 class="fm-pa-sessions__h2-5"><?= esc_html($fields['four_ways_to_work']); ?><br class="fm-pa-sessions__br-6"><?= esc_html($fields['with_me']); ?></h2>
      </div>
      <p class="fm-pa-sessions__p-7"><?= esc_html($fields['no_contracts_class_packs_never_expire_within_twe']); ?></p>
    </div>
    <?php foreach ($lists['sessions'] as $s_index => $s): ?>
      <a class="fm-pa-sessions__a-8" href="<?= esc_url($fields['href_book']); ?>">
        <div class="fm-pa-sessions__div-9"><span><?= esc_html($s['name']); ?></span></div>
        <div class="fm-pa-sessions__div-10"><span><?= esc_html($s['desc']); ?></span></div>
        <div class="fm-pa-sessions__div-11"><span><?= esc_html($s['length']); ?></span></div>
        <div class="fm-pa-sessions__div-12"><span><?= esc_html($s['price']); ?></span></div>
      </a>
    <?php endforeach; ?>
    <div class="fm-pa-sessions__div-13"></div>
  </section>
