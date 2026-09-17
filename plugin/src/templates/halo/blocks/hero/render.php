<?php
/**
 * Halo — hero.
 *
 * SEO: this block owns the page's ONE H1 (how-to-work.md §10), which is why there is no
 * `level` attribute — there is nothing to choose. `highlight` is the italic phrase inside
 * that same H1, so the headline stays one sentence to a crawler and to a screen reader
 * rather than being three fragments.
 *
 * SPEED: the studio photograph is the LCP element, so it is eager with fetchpriority
 * high (§9). fm_image() sets width and height, which is what stops the figures row
 * jumping when it lands.
 *
 * The booking note floating over the photograph is real text, not part of the image:
 * it is the most time-sensitive line on the page and the customer edits it most often.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$eyebrow     = (string) ($attributes['eyebrow'] ?? '');
$heading     = (string) ($attributes['heading'] ?? '');
$highlight   = (string) ($attributes['highlight'] ?? '');
$tail        = (string) ($attributes['headingTail'] ?? '');
$lead        = (string) ($attributes['lead'] ?? '');
$cta_label   = (string) ($attributes['ctaLabel'] ?? '');
$cta_url     = (string) ($attributes['ctaUrl'] ?? '');
$alt_label   = (string) ($attributes['altLabel'] ?? '');
$alt_url     = (string) ($attributes['altUrl'] ?? '');
$image_id    = (int) ($attributes['imageId'] ?? 0);
$image_alt   = (string) ($attributes['imageAlt'] ?? '');
$note        = (string) ($attributes['imageNote'] ?? '');
$badge_label = (string) ($attributes['badgeLabel'] ?? '');
$badge_text  = (string) ($attributes['badgeText'] ?? '');

$stats = array_values(array_filter(
    (array) ($attributes['stats'] ?? []),
    static fn ($row): bool => is_array($row) && (string) ($row['v'] ?? '') !== ''
));

$credentials = array_values(array_filter(
    array_map('strval', (array) ($attributes['credentials'] ?? [])),
    static fn (string $item): bool => trim($item) !== ''
));

// A hero with no headline is a half-configured block, not a design decision. An empty
// band reads as a broken page; nothing at all reads as "not filled in yet".
if ($heading === '') {
    return;
}
?>
<section <?php echo fm_wrapper(['fm-halo-hero']); ?>>
  <div class="fm-halo-hero__grid">
    <div class="fm-halo-hero__copy">
      <?php if ($eyebrow !== '') : ?>
        <span class="fm-halo-eyebrow"><?php echo esc_html($eyebrow); ?></span>
      <?php endif; ?>

      <h1 class="fm-halo-hero__heading fm-halo-display">
        <?php echo esc_html($heading); ?><?php if ($highlight !== '') : ?> <em><?php echo esc_html($highlight); ?></em><?php endif; ?><?php if ($tail !== '') : ?> <?php echo esc_html($tail); ?><?php endif; ?>
      </h1>

      <?php if ($lead !== '') : ?>
        <p class="fm-halo-lead fm-halo-hero__lead"><?php echo esc_html($lead); ?></p>
      <?php endif; ?>

      <?php if (($cta_label !== '' && $cta_url !== '') || ($alt_label !== '' && $alt_url !== '')) : ?>
        <div class="fm-halo-hero__actions">
          <?php // Both halves or neither: a labelled button with no destination is a dead control. ?>
          <?php if ($cta_label !== '' && $cta_url !== '') : ?>
            <a class="fm-halo-button" href="<?php echo fm_url($cta_url); ?>"><?php echo esc_html($cta_label); ?></a>
          <?php endif; ?>
          <?php if ($alt_label !== '' && $alt_url !== '') : ?>
            <a class="fm-halo-button--ghost" href="<?php echo fm_url($alt_url); ?>"><?php echo esc_html($alt_label); ?></a>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if ($stats !== []) : ?>
        <?php // A description list: each figure is the value of the label beside it, and that is what dl means. ?>
        <dl class="fm-halo-hero__stats">
          <?php foreach ($stats as $stat) : ?>
            <div class="fm-halo-hero__stat">
              <dd class="fm-halo-hero__stat-value"><?php echo esc_html((string) ($stat['v'] ?? '')); ?></dd>
              <dt class="fm-halo-hero__stat-key"><?php echo esc_html((string) ($stat['k'] ?? '')); ?></dt>
            </div>
          <?php endforeach; ?>
        </dl>
      <?php endif; ?>
    </div>

    <div class="fm-halo-hero__media">
      <div class="fm-halo-plate fm-halo-hero__plate">
        <?php if ($image_id > 0 && empty($attributes['placeholder'])) : ?>
          <?php echo fm_image($image_id, 'full', ['alt' => esc_attr($image_alt)], true); ?>
        <?php elseif ($note !== '') : ?>
          <span class="fm-halo-plate__label"><?php echo esc_html($note); ?></span>
        <?php endif; ?>
      </div>

      <?php if ($badge_text !== '') : ?>
        <p class="fm-halo-hero__badge">
          <?php if ($badge_label !== '') : ?>
            <span class="fm-halo-hero__badge-label"><?php echo esc_html($badge_label); ?></span>
          <?php endif; ?>
          <span class="fm-halo-hero__badge-text"><?php echo esc_html($badge_text); ?></span>
        </p>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($credentials !== []) : ?>
    <?php
    /*
     * The credentials strip. A list, not a row of loose spans: it is four separate
     * claims, and a screen reader should be told there are four of them.
     */
    ?>
    <ul class="fm-halo-hero__credentials">
      <?php foreach ($credentials as $item) : ?>
        <li><?php echo esc_html($item); ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</section>
