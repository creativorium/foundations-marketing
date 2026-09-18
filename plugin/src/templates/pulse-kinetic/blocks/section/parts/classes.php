<?php if (!defined('ABSPATH')) { exit; }
$class_images = [
  ['class-barre', 900, 600, 'Barre Burn class at Pulse Studio'],
  ['class-mat', 900, 720, 'Mat Method Pilates class at Pulse Studio'],
  ['class-mobility', 900, 1046, 'Slow Mobility class at Pulse Studio'],
];
?>
<section id="classes" class="fm-pk-classes" data-pk-part="classes">
  <div class="fm-pk-container">
    <div class="fm-pk-section-head"><h2><?php echo esc_html($fields['heading']); ?></h2><p><?php echo esc_html($fields['intro']); ?></p></div>
    <div class="fm-pk-classes__grid">
      <?php foreach ($lists['classes'] as $index => $item) : $image = $class_images[$index] ?? $class_images[0]; ?>
        <article class="fm-pk-class-card">
          <div class="fm-pk-class-card__image"><?php fm_pk_img($images, $image[0], $image[1], $image[2], $image[3]); ?></div>
          <div class="fm-pk-class-card__body">
            <div class="fm-pk-class-card__title"><h3><?php echo esc_html($item['name']); ?></h3><span><?php echo esc_html($item['level']); ?></span></div>
            <p><?php echo esc_html($item['desc']); ?></p>
            <div class="fm-pk-class-card__meta"><span><?php echo esc_html($item['length']); ?></span><a href="<?php echo esc_url($fields['book_url']); ?>"><?php echo esc_html($fields['book_label']); ?></a></div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
