<?php
if (!defined('ABSPATH')) { exit; }
$a = $attributes;
$text = static fn(string $key, string $fallback = ''): string => esc_html((string) ($a[$key] ?? $fallback));
?>
<section class="ember ember-hero" id="home">
  <div class="ember-photo-print">
    <div class="ember-photo-art" aria-hidden="true">
      <svg width="220" height="300" viewBox="0 0 220 300" fill="none" xmlns="http://www.w3.org/2000/svg">
        <line x1="110" y1="20" x2="110" y2="280" stroke="#F0E8D6" stroke-width="0.8"/>
        <path d="M110 80 Q140 60 160 40M110 80 Q80 60 60 40M110 120 Q148 95 175 75M110 120 Q72 95 45 75M110 160 Q150 135 180 115M110 160 Q70 135 40 115" stroke="#F0E8D6" stroke-width="0.7" fill="none"/>
        <circle cx="110" cy="78" r="3" fill="#F0E8D6" opacity="0.6"/><circle cx="110" cy="118" r="2.5" fill="#F0E8D6" opacity="0.5"/><circle cx="110" cy="158" r="2" fill="#F0E8D6" opacity="0.4"/>
        <ellipse cx="162" cy="39" rx="8" ry="5" stroke="#F0E8D6" stroke-width="0.6" transform="rotate(-30 162 39)"/><ellipse cx="58" cy="39" rx="8" ry="5" stroke="#F0E8D6" stroke-width="0.6" transform="rotate(30 58 39)"/>
        <ellipse cx="177" cy="74" rx="9" ry="5" stroke="#F0E8D6" stroke-width="0.6" transform="rotate(-25 177 74)"/><ellipse cx="43" cy="74" rx="9" ry="5" stroke="#F0E8D6" stroke-width="0.6" transform="rotate(25 43 74)"/>
      </svg>
    </div>
    <div class="ember-photo-caption"><?php echo $text('photoCaption', 'where stillness becomes medicine'); ?></div>
  </div>
  <p class="ember-photo-date"><?php echo $text('dateLine', 'Est. 2026 · London'); ?></p>
  <div class="ember-hero-headline">
    <h1><?php echo $text('headingLineOne', 'Touch that'); ?><br><?php echo $text('headingLineTwo', 'restores'); ?></h1>
    <p><?php echo $text('tagline', 'Therapeutic massage & bodywork'); ?></p>
    <a href="<?php echo esc_url((string) ($a['ctaUrl'] ?? '#book')); ?>" class="ember-hero-cta"><?php echo $text('ctaLabel', 'Reserve a session'); ?></a>
  </div>
</section>
