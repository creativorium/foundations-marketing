<?php if (!defined('ABSPATH')) { exit; }
$address = (string) fm_setting('address', 'Unit 9, North Laine Yard, Brighton BN1 4GD');
$email = (string) fm_setting('email', 'hey@pulsestudio.co.uk');
$phone = (string) fm_setting('phone', '07700 900 118');
$instagram = (string) fm_setting('instagram_handle', '@pulse.studio');
$hours = (string) fm_setting('opening_hours', "Mon–Fri 06:00 – 20:00\nSaturday 08:00 – 13:00\nSunday closed");
?>
<section class="fm-pk-contact" data-pk-part="contact">
  <div class="fm-pk-contact__inner">
    <h1><?php echo esc_html($fields['heading']); ?></h1>
    <div class="fm-pk-contact__grid">
      <form class="fm-pk-contact__form" data-pk-inert-form>
        <label><span class="screen-reader-text"><?php echo esc_html($fields['name_placeholder']); ?></span><input name="name" autocomplete="name" placeholder="<?php echo esc_attr($fields['name_placeholder']); ?>"></label>
        <label><span class="screen-reader-text"><?php echo esc_html($fields['email_placeholder']); ?></span><input type="email" name="email" autocomplete="email" placeholder="<?php echo esc_attr($fields['email_placeholder']); ?>"></label>
        <label><span class="screen-reader-text"><?php echo esc_html($fields['request_placeholder']); ?></span><input name="request" placeholder="<?php echo esc_attr($fields['request_placeholder']); ?>"></label>
        <label><span class="screen-reader-text"><?php echo esc_html($fields['message_placeholder']); ?></span><textarea name="message" rows="5" placeholder="<?php echo esc_attr($fields['message_placeholder']); ?>"></textarea></label>
        <button type="submit"><?php echo esc_html($fields['button_label']); ?></button>
      </form>
      <div class="fm-pk-contact__cards">
        <article class="fm-pk-contact__card fm-pk-contact__card--studio"><h2><?php echo esc_html($fields['studio_heading']); ?></h2><p><?php echo nl2br(esc_html($address), false); ?><br><br><?php echo esc_html($fields['studio_note']); ?></p></article>
        <article class="fm-pk-contact__card fm-pk-contact__card--reach"><h2><?php echo esc_html($fields['reach_heading']); ?></h2><p><a href="<?php echo esc_url('mailto:' . $email); ?>"><?php echo esc_html($email); ?></a><br><a href="<?php echo esc_url('tel:' . preg_replace('/[^0-9+]/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a><br><?php echo esc_html($instagram); ?></p></article>
        <article class="fm-pk-contact__card fm-pk-contact__card--hours"><h2><?php echo esc_html($fields['hours_heading']); ?></h2><p><?php echo nl2br(esc_html($hours), false); ?></p></article>
      </div>
    </div>
  </div>
</section>
