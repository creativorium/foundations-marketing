<?php if (!defined('ABSPATH')) { exit; } ?>
<section class="fm-pa-testimonials__section-1" data-pa-part="testimonials">
    <div class="fm-pa-testimonials__div-2"><?= esc_html($fields['07_in_their_words']); ?></div>
    <p class="fm-pa-testimonials__p-3" data-pa-quote-text><?= esc_html($fields['text']); ?><span><?= esc_html(($lists['quotes'][0]['text'] ?? '')); ?></span><?= esc_html($fields['text_2']); ?></p>
    <div class="fm-pa-testimonials__div-4" data-pa-quote-author><span><?= esc_html(($lists['quotes'][0]['author'] ?? '')); ?></span></div>
    <div class="fm-pa-testimonials__div-5">
      <?php foreach ($lists['quotes'] as $d_index => $d): ?>
        <button class="fm-pa-testimonials__button-6" data-pa-quote="<?= (int) $d_index; ?>" aria-label="<?= esc_attr('Show testimonial '.($d_index + 1)); ?>" aria-pressed="<?= $d_index === 0 ? 'true' : 'false'; ?>" type="button"></button>
      <?php endforeach; ?>
    </div>
  </section>
