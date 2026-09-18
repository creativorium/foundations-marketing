<?php if (!defined('ABSPATH')) { exit; } ?>
<section id="pricing" class="fm-pk-pricing" data-pk-part="pricing">
  <div class="fm-pk-container">
    <h2><?php echo esc_html($fields['heading']); ?></h2>
    <div class="fm-pk-pricing__grid">
      <?php foreach ($lists['plans'] as $index => $plan) : ?>
        <article class="fm-pk-plan fm-pk-plan--<?php echo esc_attr((string) min($index + 1, 3)); ?>">
          <div class="fm-pk-plan__head"><h3><?php echo esc_html($plan['name']); ?></h3><span><?php echo esc_html($plan['tag']); ?></span></div>
          <div class="fm-pk-plan__price"><?php echo esc_html($plan['price']); ?><small><?php echo esc_html($plan['per']); ?></small></div>
          <ul><?php foreach ($plan['items'] as $item) : ?><li><span aria-hidden="true">—</span><?php echo esc_html($item['text']); ?></li><?php endforeach; ?></ul>
          <a href="<?php echo esc_url($fields['book_url']); ?>"><?php echo esc_html($plan['cta']); ?></a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
