<?php if (!defined('ABSPATH')) { exit; } ?>
<section id="faq" class="fm-pk-faq" data-pk-part="faq">
  <div class="fm-pk-faq__inner">
    <h2><?php echo esc_html($fields['heading']); ?></h2>
    <div class="fm-pk-faq__list">
      <?php foreach ($lists['faqs'] as $index => $faq) : $id = $uid . 'faq-' . $index; $open = $index === 0; ?>
        <div class="fm-pk-faq__item">
          <button type="button" data-pk-faq aria-expanded="<?php echo $open ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr($id); ?>"><span><?php echo esc_html($faq['q']); ?></span><b aria-hidden="true" data-pk-faq-sign><?php echo $open ? '−' : '+'; ?></b></button>
          <p id="<?php echo esc_attr($id); ?>"<?php echo $open ? '' : ' hidden'; ?>><?php echo esc_html($faq['a']); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
