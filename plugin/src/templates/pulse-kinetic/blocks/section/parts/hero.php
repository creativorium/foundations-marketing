<?php if (!defined('ABSPATH')) { exit; } ?>
<div id="top" class="fm-pk-hero" data-pk-part="hero">
  <section class="fm-pk-hero__section">
    <div class="fm-pk-hero__copy">
      <div class="fm-pk-hero__availability"><span aria-hidden="true"></span><?php echo esc_html($fields['availability']); ?></div>
      <h1><?php echo esc_html($fields['heading_line_1']); ?><br><?php echo esc_html($fields['heading_line_2']); ?></h1>
      <div class="fm-pk-hero__intro-row">
        <p><?php echo esc_html($fields['intro']); ?></p>
        <div class="fm-pk-hero__actions">
          <a class="fm-pk-button fm-pk-button--ink" href="<?php echo esc_url($fields['primary_url']); ?>"><?php echo esc_html($fields['primary_label']); ?></a>
          <a class="fm-pk-button fm-pk-button--outline" href="<?php echo esc_url($fields['secondary_url']); ?>"><?php echo esc_html($fields['secondary_label']); ?></a>
        </div>
      </div>
    </div>
    <div class="fm-pk-hero__media">
      <div class="fm-pk-hero__image fm-pk-hero__image--arch"><?php fm_pk_img($images, 'hero-barre', 1200, 800, 'Barre class in the Pulse studio — barre and pilates studio website template UK', true); ?></div>
      <div class="fm-pk-hero__image"><?php fm_pk_img($images, 'hero-mat', 900, 720, 'Mat Pilates at Pulse Studio', true); ?></div>
      <div class="fm-pk-hero__image"><?php fm_pk_img($images, 'hero-mobility', 900, 600, 'Mobility training at Pulse Studio', true); ?></div>
    </div>
  </section>
  <div class="fm-pk-marquee" aria-label="Studio offering">
    <div class="fm-pk-marquee__track">
      <?php for ($copy = 0; $copy < 2; $copy++) : ?>
        <span class="fm-pk-marquee__group"<?php echo $copy ? ' aria-hidden="true"' : ''; ?>>
          <?php foreach ($lists['marquee'] as $item) : ?><span><?php echo esc_html($item['text']); ?></span><b aria-hidden="true">✦</b><?php endforeach; ?>
        </span>
      <?php endfor; ?>
    </div>
  </div>
</div>
