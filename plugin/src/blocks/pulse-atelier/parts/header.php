<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="fm-pa-header__div-1" data-pa-part="header">
  <a class="fm-pa-header__a-2" href="<?= esc_url($fields['href_top']); ?>" data-pa-nav="home"><?= esc_html($fields['pulse']); ?><span class="fm-pa-header__span-3"><?= esc_html($fields['text']); ?></span></a>
  <div class="fm-pa-header__div-4">
    <a class="fm-pa-header__a-5" href="<?= esc_url($fields['href_practice']); ?>" data-pa-nav="home"><?= esc_html($fields['practice']); ?></a>
    <a class="fm-pa-header__a-6" href="<?= esc_url($fields['href_teacher']); ?>" data-pa-nav="home"><?= esc_html($fields['teacher']); ?></a>
    <a class="fm-pa-header__a-7" href="<?= esc_url($fields['href_sessions']); ?>" data-pa-nav="home"><?= esc_html($fields['sessions']); ?></a>
    <a class="fm-pa-header__a-8" href="<?= esc_url($fields['href_faq']); ?>" data-pa-nav="home"><?= esc_html($fields['faq']); ?></a>
    <a class="fm-pa-header__a-9" href="<?= esc_url($fields['href_book']); ?>" data-pa-nav="home"><?= esc_html($fields['book_a_class']); ?></a>
  </div>
</div>
