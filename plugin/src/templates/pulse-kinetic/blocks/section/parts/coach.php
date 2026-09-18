<?php if (!defined('ABSPATH')) { exit; } ?>
<section id="coach" class="fm-pk-coach" data-pk-part="coach">
  <div class="fm-pk-container fm-pk-coach__grid">
    <div class="fm-pk-coach__copy">
      <div class="fm-pk-eyebrow"><?php echo esc_html($fields['eyebrow']); ?></div>
      <h2><?php echo esc_html($fields['first_name']); ?><br><?php echo esc_html($fields['last_name']); ?></h2>
      <p><?php echo esc_html($fields['body_1']); ?></p>
      <p class="fm-pk-coach__muted"><?php echo esc_html($fields['body_2']); ?></p>
      <ul><?php foreach ($lists['credentials'] as $item) : ?><li><?php echo esc_html($item['text']); ?></li><?php endforeach; ?></ul>
    </div>
    <div class="fm-pk-coach__image"><?php fm_pk_img($images, 'coach', 1000, 667, 'Pulse Studio coach Nadia Okonkwo'); ?></div>
  </div>
</section>
