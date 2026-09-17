<?php if (!defined('ABSPATH')) { exit; } ?>
<section class="fm-pa-booking__section-1" style="background-image:url('<?= esc_url(fm_pa_image($images, 'booking')); ?>')" id="book" data-pa-part="booking">
    <div class="fm-pa-booking__div-2"></div>
    <div class="fm-pa-booking__div-3">
      <h2 class="fm-pa-booking__h2-4"><?= esc_html($fields['your_first_class']); ?><br class="fm-pa-booking__br-5"><?= esc_html($fields['is_10']); ?></h2>
      <p class="fm-pa-booking__p-6"><?= esc_html($fields['six_mats_a_session_most_weeks_they_go_by_friday']); ?></p>
      <a class="fm-pa-booking__a-7" href="<?= esc_url($fields['href_contact']); ?>" data-pa-nav="contact"><?= esc_html($fields['book_your_place']); ?></a>
    </div>
  </section>
