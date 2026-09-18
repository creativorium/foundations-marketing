<?php if (!defined('ABSPATH')) { exit; }
$address = (string) fm_setting('booking_address', 'Unit 9, North Laine Yard, Brighton');
$email = (string) fm_setting('email', 'hey@pulsestudio.co.uk');
?>
<section id="book" class="fm-pk-booking" data-pk-part="booking">
  <div class="fm-pk-container fm-pk-booking__grid">
    <div class="fm-pk-booking__copy">
      <h2><?php echo esc_html($fields['heading_line_1']); ?><br><?php echo esc_html($fields['heading_line_2']); ?></h2>
      <p><?php echo esc_html($fields['body']); ?></p>
      <div class="fm-pk-booking__details">
        <div><span><?php echo esc_html($fields['studio_label']); ?></span><strong><?php echo esc_html($address); ?></strong></div>
        <div><span><?php echo esc_html($fields['email_label']); ?></span><a href="<?php echo esc_url('mailto:' . $email); ?>"><?php echo esc_html($email); ?></a></div>
      </div>
    </div>
    <form class="fm-pk-form" data-pk-inert-form>
      <label><span class="screen-reader-text"><?php echo esc_html($fields['name_placeholder']); ?></span><input name="name" autocomplete="name" placeholder="<?php echo esc_attr($fields['name_placeholder']); ?>"></label>
      <label><span class="screen-reader-text"><?php echo esc_html($fields['contact_placeholder']); ?></span><input name="contact" autocomplete="email" placeholder="<?php echo esc_attr($fields['contact_placeholder']); ?>"></label>
      <label><span class="screen-reader-text"><?php echo esc_html($fields['availability_placeholder']); ?></span><input name="availability" placeholder="<?php echo esc_attr($fields['availability_placeholder']); ?>"></label>
      <button type="submit"><?php echo esc_html($fields['button_label']); ?></button>
      <div><?php echo esc_html($fields['privacy_prefix']); ?> <a href="<?php echo esc_url($fields['privacy_url']); ?>"><?php echo esc_html($fields['privacy_label']); ?></a>.</div>
    </form>
  </div>
</section>
