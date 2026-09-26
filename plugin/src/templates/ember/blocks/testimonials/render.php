<?php
if (!defined('ABSPATH')) { exit; }
$a = $attributes;
?>
<section class="ember ember-testimonials" id="testimonials">
  <div class="ember-testimonials-inner">
    <div class="ember-testimonials-header">
      <p class="ember-section-eyebrow"><?php echo esc_html((string) ($a['eyebrow'] ?? 'Client words')); ?></p>
      <h2 class="ember-section-title"><?php echo esc_html((string) ($a['heading'] ?? 'What they say')); ?></h2>
    </div>
    <div class="ember-quotes-grid">
      <?php foreach ((array) ($a['items'] ?? []) as $item) : ?>
        <div class="ember-quote-card">
          <div class="ember-quote-mark" aria-hidden="true">&ldquo;</div>
          <blockquote class="ember-quote-text"><?php echo esc_html((string) ($item['quote'] ?? '')); ?></blockquote>
          <p class="ember-quote-author"><?php echo esc_html((string) ($item['author'] ?? '')); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
