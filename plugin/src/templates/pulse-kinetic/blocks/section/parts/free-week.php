<?php if (!defined('ABSPATH')) { exit; } ?>
<section class="fm-pk-free-week" data-pk-part="free-week">
  <div class="fm-pk-free-week__panel">
    <div class="fm-pk-free-week__copy">
      <div class="fm-pk-eyebrow"><?php echo esc_html($fields['eyebrow']); ?></div>
      <h2><?php echo esc_html($fields['heading_line_1']); ?><br><?php echo esc_html($fields['heading_line_2']); ?><br><?php echo esc_html($fields['heading_line_3']); ?></h2>
      <p><?php echo esc_html($fields['body']); ?></p>
    </div>
    <ol class="fm-pk-free-week__steps">
      <?php foreach ($lists['steps'] as $step) : ?><li><strong><?php echo esc_html($step['number']); ?></strong><span><?php echo esc_html($step['text']); ?></span></li><?php endforeach; ?>
    </ol>
  </div>
</section>
