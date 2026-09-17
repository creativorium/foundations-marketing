<?php if (!defined('ABSPATH')) { exit; } ?>
<section class="fm-pa-hero__section-1" data-pa-part="hero">
    <div class="fm-pa-hero__div-2">
      <div class="fm-pa-hero__div-3"><?= esc_html($fields['reformer_mat_pilates_brighton']); ?></div>
      <h1 class="fm-pa-hero__h1-4"><?= esc_html($fields['strength_that']); ?><br class="fm-pa-hero__br-5"><em class="fm-pa-hero__em-6"><?= esc_html($fields['feels_like']); ?></em><?= esc_html($fields['calm']); ?></h1>
      <p class="fm-pa-hero__p-7"><?= esc_html($fields['small_group_and_one_to_one_sessions_for_people_w']); ?></p>
      <div class="fm-pa-hero__div-8">
        <a class="fm-pa-hero__a-9" href="<?= esc_url($fields['href_book']); ?>"><?= esc_html($fields['book_your_first_class']); ?></a>
        <a class="fm-pa-hero__a-10" href="<?= esc_url($fields['href_sessions']); ?>"><?= esc_html($fields['see_sessions_prices']); ?></a>
      </div>
      <div class="fm-pa-hero__div-11">
        <span class="fm-pa-hero__span-12"></span><?= esc_html($fields['first_class_10_no_membership_cancel_anytime']); ?></div>
    </div>
    <div class="fm-pa-hero__div-13" style="background-image:url('<?= esc_url(fm_pa_image($images, 'hero')); ?>')"></div>
  </section>
