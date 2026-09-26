<?php
if (!defined('ABSPATH')) { exit; }
$a = $attributes;
?>
<section class="ember ember-booking" id="book">
  <div class="ember-booking-inner">
    <div class="ember-booking-copy">
      <p class="ember-section-eyebrow"><?php echo esc_html((string) ($a['eyebrow'] ?? 'Bookings')); ?></p>
      <h2 class="ember-section-title"><?php echo esc_html((string) ($a['heading'] ?? 'Reserve your session')); ?></h2>
      <p><?php echo esc_html((string) ($a['body'] ?? '')); ?></p>
      <div class="ember-booking-details">
        <?php foreach ((array) ($a['details'] ?? []) as $detail) : ?>
          <div class="ember-booking-detail"><span><?php echo esc_html((string) ($detail['label'] ?? '')); ?></span><span><?php echo esc_html((string) ($detail['value'] ?? '')); ?></span></div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="ember-widget-box">
      <div class="ember-widget-icon" aria-hidden="true">◇</div>
      <p><?php echo nl2br(esc_html((string) ($a['widgetLabel'] ?? "Booking widget\nplaceholder"))); ?></p>
      <p class="ember-widget-examples"><?php echo esc_html((string) ($a['widgetExamples'] ?? 'e.g. Fresha · Treatwell · Mindbody')); ?></p>
    </div>
  </div>
</section>
