<?php if (!defined('ABSPATH')) { exit; } ?>
<section class="fm-pk-testimonials" data-pk-part="testimonials">
  <div class="fm-pk-container">
    <h2><?php echo esc_html($fields['heading']); ?></h2>
    <div class="fm-pk-testimonials__grid">
      <?php foreach ($lists['quotes'] as $quote) : ?><blockquote><span aria-hidden="true">“</span><p><?php echo esc_html($quote['q']); ?></p><cite><?php echo esc_html($quote['author']); ?></cite></blockquote><?php endforeach; ?>
    </div>
  </div>
</section>
