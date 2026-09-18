<?php if (!defined('ABSPATH')) { exit; } ?>
<section class="fm-pk-metrics" data-pk-part="metrics">
  <div class="fm-pk-container">
    <h2><?php echo esc_html($fields['heading']); ?></h2>
    <div class="fm-pk-metrics__grid">
      <article class="fm-pk-metric fm-pk-metric--mats"><strong><?php echo esc_html($fields['mats_value']); ?></strong><div><h3><?php echo esc_html($fields['mats_title']); ?></h3><p><?php echo esc_html($fields['mats_body']); ?></p></div></article>
      <article class="fm-pk-metric fm-pk-metric--duration"><strong><?php echo esc_html($fields['duration_value']); ?></strong><p><?php echo esc_html($fields['duration_body']); ?></p></article>
      <div class="fm-pk-metric fm-pk-metric--photo"><?php fm_pk_img($images, 'studio-sunset', 800, 930, 'Brighton movement studio at sunset'); ?></div>
      <div class="fm-pk-metric fm-pk-metric--photo"><?php fm_pk_img($images, 'studio-stretch', 800, 1200, 'Client stretching in the Pulse movement studio'); ?></div>
      <article class="fm-pk-metric fm-pk-metric--fee"><strong><?php echo esc_html($fields['fee_value']); ?></strong><p><?php echo esc_html($fields['fee_body']); ?></p></article>
      <article class="fm-pk-metric fm-pk-metric--hours"><strong><?php echo esc_html($fields['hours_value']); ?></strong><div><h3><?php echo esc_html($fields['hours_title']); ?></h3><p><?php echo esc_html($fields['hours_body']); ?></p></div></article>
    </div>
  </div>
</section>
