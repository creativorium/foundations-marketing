<?php
/**
 * Halo — the enquiry form.
 *
 * ACCESSIBILITY: every field has a real <label>, not a placeholder standing in for one.
 * The canvas draws placeholder-only inputs; that is a defect, not a style — a placeholder
 * disappears the moment someone types, so a reader who looks away loses the field's name,
 * and several screen readers do not announce it at all. The labels are in the markup and
 * visually hidden; the placeholder stays as the visible hint (how-to-work.md §8).
 *
 * THE FORM DOES NOT SEND ANYTHING BY ITSELF. Nothing in this template processes a
 * submission — form adapters are out of a template's scope and must be implemented and
 * tested separately (team-template-workflow.md, "Testing external integrations"). With no
 * `action` set the submit button is disabled and says why. A form that looks like it
 * works and silently drops every enquiry is the worst of the three states this could
 * be in, and in this niche the dropped message is somebody's medical history.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$eyebrow        = (string) ($attributes['eyebrow'] ?? '');
$heading        = (string) ($attributes['heading'] ?? '');
$lead           = (string) ($attributes['lead'] ?? '');
$action         = (string) ($attributes['action'] ?? '');
$interest_label = (string) ($attributes['interestLabel'] ?? '');
$message_label  = (string) ($attributes['messageLabel'] ?? '');
$submit_label   = (string) ($attributes['submitLabel'] ?? '');
$image_id       = (int) ($attributes['imageId'] ?? 0);
$image_alt      = (string) ($attributes['imageAlt'] ?? '');
$note           = (string) ($attributes['imageNote'] ?? '');

$interests = array_values(array_filter(
    array_map('strval', (array) ($attributes['interests'] ?? [])),
    static fn (string $i): bool => trim($i) !== ''
));

$wired = $action !== '';

// Ids must be unique on the page; wp_unique_id() is what core uses for exactly this.
$uid = wp_unique_id('fm-halo-contact-');

$fields = [
    'first' => ['label' => __('First name', 'foundations'), 'type' => 'text',  'autocomplete' => 'given-name'],
    'email' => ['label' => __('Email', 'foundations'),      'type' => 'email', 'autocomplete' => 'email'],
];
?>
<section <?php echo fm_wrapper(['fm-halo-contact', 'fm-halo-band']); ?>>
  <div class="fm-halo-contact__grid">
    <div class="fm-halo-contact__copy">
      <?php if ($eyebrow !== '') : ?>
        <span class="fm-halo-eyebrow"><?php echo esc_html($eyebrow); ?></span>
      <?php endif; ?>

      <?php if ($heading !== '') : ?>
        <h2 class="fm-halo-display fm-halo-contact__heading"><?php echo esc_html($heading); ?></h2>
      <?php endif; ?>

      <?php if ($lead !== '') : ?>
        <p class="fm-halo-lead fm-halo-contact__lead"><?php echo esc_html($lead); ?></p>
      <?php endif; ?>

      <form
        class="fm-halo-contact__form"
        method="post"
        <?php if ($wired) : ?>action="<?php echo fm_url($action); ?>"<?php endif; ?>
      >
        <?php foreach ($fields as $name => $field) : ?>
          <?php $id = $uid . '-' . $name; ?>
          <p class="fm-halo-contact__field">
            <label class="fm-halo-contact__label" for="<?php echo esc_attr($id); ?>">
              <?php echo esc_html($field['label']); ?>
            </label>
            <input
              class="fm-halo-contact__input"
              id="<?php echo esc_attr($id); ?>"
              name="<?php echo esc_attr($name); ?>"
              type="<?php echo esc_attr($field['type']); ?>"
              autocomplete="<?php echo esc_attr($field['autocomplete']); ?>"
              placeholder="<?php echo esc_attr($field['label']); ?>"
            >
          </p>
        <?php endforeach; ?>

        <?php if ($interests !== []) : ?>
          <p class="fm-halo-contact__field fm-halo-contact__field--wide">
            <label class="fm-halo-contact__label" for="<?php echo esc_attr($uid); ?>-interest">
              <?php echo esc_html($interest_label !== '' ? $interest_label : __('What are you interested in?', 'foundations')); ?>
            </label>
            <select class="fm-halo-contact__input" id="<?php echo esc_attr($uid); ?>-interest" name="interest">
              <option value="" disabled selected>
                <?php echo esc_html($interest_label !== '' ? $interest_label : __('What are you interested in?', 'foundations')); ?>
              </option>
              <?php foreach ($interests as $interest) : ?>
                <option value="<?php echo esc_attr($interest); ?>"><?php echo esc_html($interest); ?></option>
              <?php endforeach; ?>
            </select>
          </p>
        <?php endif; ?>

        <p class="fm-halo-contact__field fm-halo-contact__field--wide">
          <label class="fm-halo-contact__label" for="<?php echo esc_attr($uid); ?>-message">
            <?php echo esc_html($message_label !== '' ? $message_label : __('Anything I should know?', 'foundations')); ?>
          </label>
          <textarea
            class="fm-halo-contact__input fm-halo-contact__textarea"
            id="<?php echo esc_attr($uid); ?>-message"
            name="message"
            rows="5"
            placeholder="<?php echo esc_attr($message_label !== '' ? $message_label : __('Anything I should know?', 'foundations')); ?>"
          ></textarea>
        </p>

        <p class="fm-halo-contact__submit">
          <button
            class="fm-halo-button fm-halo-contact__button"
            type="submit"
            <?php echo $wired ? '' : ' disabled aria-describedby="' . esc_attr($uid) . '-unwired"'; ?>
          >
            <?php echo esc_html($submit_label !== '' ? $submit_label : __('Request an appointment', 'foundations')); ?>
          </button>

          <?php if (!$wired) : ?>
            <span class="fm-halo-contact__unwired" id="<?php echo esc_attr($uid); ?>-unwired">
              <?php esc_html_e('This form has no destination yet. Set one in the block settings before the site goes live.', 'foundations'); ?>
            </span>
          <?php endif; ?>
        </p>
      </form>
    </div>

    <div class="fm-halo-contact__media">
      <div class="fm-halo-plate fm-halo-contact__plate">
        <?php if ($image_id > 0 && empty($attributes['placeholder'])) : ?>
          <?php echo fm_image($image_id, 'large', ['alt' => esc_attr($image_alt)]); ?>
        <?php elseif ($note !== '') : ?>
          <span class="fm-halo-plate__label"><?php echo esc_html($note); ?></span>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
