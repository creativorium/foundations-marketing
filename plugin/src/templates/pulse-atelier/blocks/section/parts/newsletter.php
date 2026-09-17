<?php if (!defined('ABSPATH')) { exit; } ?>
<section class="fm-pa-newsletter__section-1" data-pa-part="newsletter">
    <div class="fm-pa-newsletter__div-2">
      <div class="fm-pa-newsletter__div-3">
        <h2 class="fm-pa-newsletter__h2-4"><?= esc_html($fields['the_five_minute']); ?><br class="fm-pa-newsletter__br-5"><?= esc_html($fields['morning_reset']); ?></h2>
        <p class="fm-pa-newsletter__p-6"><?= esc_html($fields['a_free_video_sequence_for_stiff_backs_and_desk_s']); ?></p>
      </div>
      <div class="fm-pa-newsletter__div-7">
        <div class="fm-pa-newsletter__div-8">
          <input class="fm-pa-newsletter__input-9" type="email" placeholder="<?= esc_attr($fields['placeholder_your_email']); ?>" aria-label="<?= esc_attr($fields['label_your_email']); ?>">
          <button class="fm-pa-newsletter__button-10" type="button"><?= esc_html($fields['send_it']); ?></button>
        </div>
        <div class="fm-pa-newsletter__div-11"><?= esc_html($fields['unsubscribe_any_time_see_the']); ?><a class="fm-pa-newsletter__a-12" href="<?= esc_url($fields['href_privacy']); ?>" data-pa-nav="privacy"><?= esc_html($fields['privacy_policy']); ?></a><?= esc_html($fields['text']); ?></div>
      </div>
    </div>
  </section>
