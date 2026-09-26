<?php
if (!defined('ABSPATH')) { exit; }
$a = $attributes;
$paragraphs = (array) ($a['paragraphs'] ?? []);
$tags = (array) ($a['tags'] ?? []);
?>
<section class="ember ember-about" id="about">
  <div class="ember-about-inner">
    <div class="ember-about-portrait" aria-hidden="true"><div class="ember-portrait-frame"></div></div>
    <div class="ember-about-text">
      <p class="ember-section-eyebrow"><?php echo esc_html((string) ($a['eyebrow'] ?? 'About')); ?></p>
      <h2 class="ember-section-title"><?php echo esc_html((string) ($a['heading'] ?? 'Sofía Rivera')); ?></h2>
      <?php foreach ($paragraphs as $index => $paragraph) : ?>
        <?php $copy = is_array($paragraph) ? (string) ($paragraph['paragraph'] ?? '') : (string) $paragraph; ?>
        <p<?php echo $index === 0 ? ' class="ember-about-intro"' : ''; ?>><?php echo esc_html($copy); ?></p>
      <?php endforeach; ?>
      <div class="ember-about-tags">
        <?php foreach ($tags as $tag) : ?>
          <?php $copy = is_array($tag) ? (string) ($tag['tag'] ?? '') : (string) $tag; ?>
          <span class="ember-tag"><?php echo esc_html($copy); ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
