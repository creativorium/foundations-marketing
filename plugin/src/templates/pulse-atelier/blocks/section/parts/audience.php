<?php if (!defined('ABSPATH')) { exit; } ?>
<section class="fm-pa-audience__section-1" data-pa-part="audience">
    <div class="fm-pa-audience__div-2">
      <div class="fm-pa-audience__div-3" style="background-image:url('<?= esc_url(fm_pa_image($images, 'audience')); ?>')"></div>
      <div class="fm-pa-audience__div-4">
        <div class="fm-pa-audience__div-5"><?= esc_html($fields['05_who_it_s_for']); ?></div>
        <h2 class="fm-pa-audience__h2-6"><?= esc_html($fields['you_ll_fit_in_here_if']); ?></h2>
        <?php foreach ($lists['audience'] as $a_index => $a): ?>
          <div class="fm-pa-audience__div-7">
            <div class="fm-pa-audience__div-8"></div>
            <div class="fm-pa-audience__div-9"><div class="fm-pa-audience__div-10"><span><?= esc_html($a['title']); ?></span></div><div class="fm-pa-audience__div-11"><span><?= esc_html($a['body']); ?></span></div></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
