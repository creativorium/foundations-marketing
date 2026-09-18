<?php if (!defined('ABSPATH')) { exit; } ?>
<section class="fm-pk-privacy" data-pk-part="privacy">
  <div class="fm-pk-privacy__inner">
    <h1><?php echo esc_html($fields['heading']); ?></h1>
    <p class="fm-pk-privacy__updated"><?php echo esc_html($fields['updated']); ?></p>
    <div class="fm-pk-privacy__sections">
      <?php foreach ($lists['sections'] as $section) : ?><article><h2><?php echo esc_html($section['title']); ?></h2><p><?php echo esc_html($section['body']); ?></p></article><?php endforeach; ?>
    </div>
  </div>
</section>
