<?php
if (!defined('ABSPATH')) { exit; }
$a = $attributes;
?>
<section class="ember ember-treatments" id="treatments">
  <div class="ember-treatments-inner">
    <div class="ember-treatments-header">
      <p class="ember-section-eyebrow"><?php echo esc_html((string) ($a['eyebrow'] ?? 'Treatments')); ?></p>
      <h2 class="ember-section-title"><?php echo esc_html((string) ($a['heading'] ?? 'What I offer')); ?></h2>
    </div>
    <?php foreach ((array) ($a['items'] ?? []) as $item) : ?>
      <div class="ember-treatment-row">
        <div class="ember-treatment-name"><?php echo esc_html((string) ($item['name'] ?? '')); ?></div>
        <div class="ember-treatment-description"><?php echo esc_html((string) ($item['description'] ?? '')); ?></div>
        <div class="ember-treatment-duration"><?php echo esc_html((string) ($item['duration'] ?? '')); ?></div>
        <div class="ember-treatment-price"><?php echo esc_html((string) ($item['price'] ?? '')); ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
