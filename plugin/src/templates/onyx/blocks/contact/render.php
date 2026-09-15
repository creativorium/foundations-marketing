<?php
/**
 * Onyx — the enquiry form.
 *
 * ACCESSIBILITY: every field has a real <label>, not a placeholder standing in for one.
 * The design draws placeholder-only inputs; that is a defect, not a style — a placeholder
 * disappears the moment someone types, so a user who looks away loses the field's name,
 * and several screen readers do not announce it at all. The labels are therefore present
 * in the markup and visually hidden, and the placeholders are kept as the visible hint
 * (how-to-work.md §8).
 *
 * THE FORM DOES NOT SEND ANYTHING BY ITSELF. Nothing in this template processes a
 * submission — form adapters are explicitly out of a template's scope and have to be
 * implemented and tested separately (team-template-workflow.md, "Testing external
 * integrations"). So with no `action` set, the submit button is disabled and says so.
 * A form that looks like it works and silently drops every enquiry is the worst of the
 * three states this could be in.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$action        = (string) ($attributes['action'] ?? '');
$area_label    = (string) ($attributes['areaLabel'] ?? '');
$message_label = (string) ($attributes['messageLabel'] ?? '');
$consent_text  = (string) ($attributes['consentText'] ?? '');
$consent_url   = (string) ($attributes['consentUrl'] ?? '');
$consent_link  = (string) ($attributes['consentLink'] ?? '');
$submit_label  = (string) ($attributes['submitLabel'] ?? '');
$map_note      = (string) ($attributes['mapNote'] ?? '');

$areas = array_values(array_filter(
    array_map('strval', (array) ($attributes['areas'] ?? [])),
    static fn (string $a): bool => trim($a) !== ''
));

$cards = array_values(array_filter(
    (array) ($attributes['cards'] ?? []),
    static fn ($row): bool => is_array($row) && (string) ($row['v'] ?? '') !== ''
));

$wired = $action !== '';

// Ids have to be unique on the page; wp_unique_id() is what core uses for exactly this.
$uid = wp_unique_id('fm-onyx-contact-');
?>
<section <?php echo fm_wrapper(['fm-onyx-contact']); ?>>
  <form
    class="fm-onyx-contact__form"
    method="post"
    <?php if ($wired) : ?>action="<?php echo fm_url($action); ?>"<?php endif; ?>
  >
    <?php
    $fields = [
        'first' => ['label' => __('First name', 'foundations'), 'type' => 'text',  'autocomplete' => 'given-name'],
        'last'  => ['label' => __('Last name', 'foundations'),  'type' => 'text',  'autocomplete' => 'family-name'],
        'email' => ['label' => __('Email', 'foundations'),      'type' => 'email', 'autocomplete' => 'email'],
        'phone' => ['label' => __('Phone', 'foundations'),      'type' => 'tel',   'autocomplete' => 'tel'],
    ];

    foreach ($fields as $name => $field) :
        $id = $uid . '-' . $name;
        ?>
      <p class="fm-onyx-contact__field">
        <label class="fm-onyx-contact__label" for="<?php echo esc_attr($id); ?>">
          <?php echo esc_html($field['label']); ?>
        </label>
        <input
          class="fm-onyx-contact__input"
          id="<?php echo esc_attr($id); ?>"
          name="<?php echo esc_attr($name); ?>"
          type="<?php echo esc_attr($field['type']); ?>"
          autocomplete="<?php echo esc_attr($field['autocomplete']); ?>"
          placeholder="<?php echo esc_attr($field['label']); ?>"
        >
      </p>
    <?php endforeach; ?>

    <?php if ($areas !== []) : ?>
      <p class="fm-onyx-contact__field fm-onyx-contact__field--wide">
        <label class="fm-onyx-contact__label" for="<?php echo esc_attr($uid); ?>-area">
          <?php echo esc_html($area_label !== '' ? $area_label : __('Area of interest', 'foundations')); ?>
        </label>
        <select
          class="fm-onyx-contact__input"
          id="<?php echo esc_attr($uid); ?>-area"
          name="area"
        >
          <?php foreach ($areas as $index => $area) : ?>
            <option
              value="<?php echo esc_attr($area); ?>"
              <?php echo $index === 0 ? ' selected' : ''; ?>
            ><?php echo esc_html($area); ?></option>
          <?php endforeach; ?>
        </select>
      </p>
    <?php endif; ?>

    <p class="fm-onyx-contact__field fm-onyx-contact__field--wide">
      <label class="fm-onyx-contact__label" for="<?php echo esc_attr($uid); ?>-message">
        <?php echo esc_html($message_label !== '' ? $message_label : __('Your message', 'foundations')); ?>
      </label>
      <textarea
        class="fm-onyx-contact__input fm-onyx-contact__textarea"
        id="<?php echo esc_attr($uid); ?>-message"
        name="message"
        rows="6"
        placeholder="<?php echo esc_attr($message_label !== '' ? $message_label : __('Your message', 'foundations')); ?>"
      ></textarea>
    </p>

    <?php if ($consent_text !== '') : ?>
      <?php
      /*
       * The policy link sits OUTSIDE the label on purpose. Inside it, every click meant
       * for the link also toggles the checkbox — the reader either ticks a box they did
       * not mean to tick or, having already ticked it, silently unticks it on the way to
       * reading the policy. Keeping it a sibling, still inline, preserves the sentence
       * and separates the two controls.
       *
       * The checkbox's own box is small by design; the target is the whole label, which
       * is associated with it and clears 44px in both directions (how-to-work.md §8).
       */
      ?>
      <p class="fm-onyx-contact__consent">
        <label class="fm-onyx-contact__consent-row" for="<?php echo esc_attr($uid); ?>-consent">
          <input
            class="fm-onyx-contact__check"
            id="<?php echo esc_attr($uid); ?>-consent"
            name="consent"
            type="checkbox"
            value="1"
            required
          >
          <span class="fm-onyx-contact__consent-text"><?php echo esc_html($consent_text); ?></span>
        </label><?php if ($consent_link !== '' && $consent_url !== '') : ?><a
          class="fm-onyx-contact__consent-link"
          href="<?php echo fm_url($consent_url); ?>"
        ><?php echo esc_html($consent_link); ?></a><?php endif; ?>
      </p>
    <?php endif; ?>

    <p class="fm-onyx-contact__submit">
      <button
        class="fm-onyx-button"
        type="submit"
        <?php echo $wired ? '' : ' disabled aria-describedby="' . esc_attr($uid) . '-unwired"'; ?>
      >
        <?php echo esc_html($submit_label !== '' ? $submit_label : __('Send enquiry', 'foundations')); ?>
      </button>

      <?php if (!$wired) : ?>
        <span class="fm-onyx-contact__unwired" id="<?php echo esc_attr($uid); ?>-unwired">
          <?php esc_html_e('This form has no destination yet. Set one in the block settings before the site goes live.', 'foundations'); ?>
        </span>
      <?php endif; ?>
    </p>
  </form>

  <div class="fm-onyx-contact__side">
    <?php if ($cards !== []) : ?>
      <dl class="fm-onyx-contact__cards">
        <?php foreach ($cards as $card) : ?>
          <div class="fm-onyx-contact__card">
            <dt class="fm-onyx-contact__card-key"><?php echo esc_html((string) ($card['k'] ?? '')); ?></dt>
            <dd class="fm-onyx-contact__card-value"><?php echo esc_html((string) ($card['v'] ?? '')); ?></dd>
          </div>
        <?php endforeach; ?>
      </dl>
    <?php endif; ?>

    <?php if ($map_note !== '') : ?>
      <div class="fm-onyx-plate fm-onyx-contact__map">
        <span class="fm-onyx-plate__label"><?php echo esc_html($map_note); ?></span>
      </div>
    <?php endif; ?>
  </div>
</section>
