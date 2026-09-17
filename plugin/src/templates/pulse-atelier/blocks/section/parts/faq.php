<?php if (!defined('ABSPATH')) { exit; } ?>
<section class="fm-pa-faq__section-1" id="faq" data-pa-part="faq">
    <div class="fm-pa-faq__div-2">
      <div class="fm-pa-faq__div-3"><?= esc_html($fields['08_before_you_book']); ?></div>
      <h2 class="fm-pa-faq__h2-4"><?= esc_html($fields['questions_people_ask']); ?></h2>
      <?php foreach ($lists['faqs'] as $f_index => $f): ?>
        <div class="fm-pa-faq__div-5">
          <button class="fm-pa-faq__button-6" data-pa-faq aria-expanded="<?= $f_index === 0 ? 'true' : 'false'; ?>" aria-controls="<?= esc_attr($uid . '-answer-' . $f_index); ?>" type="button">
            <span class="fm-pa-faq__span-7"><span><?= esc_html($f['q']); ?></span></span>
            <span class="fm-pa-faq__span-8" data-pa-faq-sign><?= $f_index === 0 ? '−' : '+'; ?></span>
          </button>
          
            <p class="fm-pa-faq__p-9" id="<?= esc_attr($uid . '-answer-' . $f_index); ?>" <?= $f_index === 0 ? '' : 'hidden'; ?>><span><?= esc_html($f['a']); ?></span></p>
          
        </div>
      <?php endforeach; ?>
      <div class="fm-pa-faq__div-10"></div>
    </div>
  </section>
