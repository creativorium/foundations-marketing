<?php
if (!defined('ABSPATH')) { exit; }
$a = $attributes;
$text = static fn($key) => esc_html((string)($a[$key] ?? ''));
$items = array_values(array_filter((array)($a['items'] ?? []), 'is_array'));
$anchor = sanitize_title((string)($a['anchor'] ?? ''));
?>
<?php
$setting = static fn($key) => function_exists('fm_setting') ? (string) fm_setting($key) : '';
$name = $setting('site_name') ?: 'Wren';
$privacy = [
  ['Who we are', 'Wren is a sole practitioner clinic based at 14 Ship Street, Brighton BN1 1AD. For any question about your data, write to hello@wrenacupuncture.co.uk.'],
  ['What we collect', 'Contact details submitted through this website, and the clinical information you share during consultation and treatment.'],
  ['Why we hold it', 'To provide safe treatment, keep required clinical records, and contact you about appointments. We do not sell data or send marketing without consent.'],
  ['How long we keep it', "Clinical notes are retained for eight years after your last appointment. Enquiries that don't lead to treatment are deleted within twelve months."],
  ['Your rights', "You may request a copy of your records, ask us to correct them, or object to how they're used. Complaints can be raised with the ICO at ico.org.uk."],
];
?>
<div class="meridian meridian-footer" data-meridian-footer>
  <div class="meridian-container">
    <div class="meridian-footer__row">
      <div><p class="meridian-brand"><?php echo esc_html($name); ?></p><p><?php echo nl2br(esc_html($setting('address'))); ?><br><a href="<?php echo esc_url('mailto:' . $setting('email')); ?>"><?php echo esc_html($setting('email')); ?></a></p></div>
      <nav aria-label="Footer navigation">
        <button type="button" data-overlay-open="contact">Contact</button>
        <button type="button" data-overlay-open="privacy">Privacy policy</button>
        <a href="<?php echo esc_url($setting('cta_url')); ?>">Book a session</a>
      </nav>
    </div>
    <p class="meridian-footer__credit">© <?php echo esc_html(wp_date('Y')); ?> <?php echo esc_html($name); ?> — Meridian template by <a href="https://foundationsmarketing.co.uk/">Foundations Marketing</a></p>
  </div>

  <div class="meridian-overlay meridian-overlay--contact" data-overlay="contact" id="contact" hidden aria-hidden="true">
    <button type="button" class="meridian-overlay__close" data-overlay-close aria-label="Close contact details">×</button>
    <div class="meridian-overlay__inner" role="dialog" aria-modal="true" aria-labelledby="meridian-contact-title">
      <p class="meridian-eyebrow">Contact</p><h2 id="meridian-contact-title">Get in touch.</h2>
      <dl class="meridian-overlay__contact">
        <?php foreach (['address' => 'Clinic', 'email' => 'Email', 'phone' => 'Phone', 'opening_hours' => 'Hours'] as $key => $label) : $value = $setting($key); if (!$value) continue; ?>
          <div><dt><?php echo esc_html($label); ?></dt><dd><?php if ($key === 'email' || $key === 'phone') : ?><a href="<?php echo esc_url(($key === 'email' ? 'mailto:' : 'tel:') . ($key === 'phone' ? preg_replace('/[^+0-9]/', '', $value) : $value)); ?>"><?php echo esc_html($value); ?></a><?php else : echo nl2br(esc_html($value)); endif; ?></dd></div>
        <?php endforeach; ?>
      </dl>
    </div>
  </div>

  <div class="meridian-overlay meridian-overlay--privacy" data-overlay="privacy" id="privacy" hidden aria-hidden="true">
    <button type="button" class="meridian-overlay__close" data-overlay-close aria-label="Close privacy policy">×</button>
    <div class="meridian-overlay__inner meridian-overlay__inner--privacy" role="dialog" aria-modal="true" aria-labelledby="meridian-privacy-title">
      <p class="meridian-eyebrow">Legal</p><h2 id="meridian-privacy-title">Privacy policy</h2><p class="meridian-overlay__date">Last updated 1 January 2026</p>
      <div class="meridian-overlay__policy"><?php foreach ($privacy as [$title, $body]) : ?><section><h3><?php echo esc_html($title); ?></h3><p><?php echo esc_html($body); ?></p></section><?php endforeach; ?></div>
    </div>
  </div>
</div>
