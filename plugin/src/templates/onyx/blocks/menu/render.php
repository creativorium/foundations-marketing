<?php
/**
 * Onyx — the treatment menu.
 *
 * A price list is tabular data — name, what it is, how long, what it costs — so it is a
 * <table> with real <th> row headers rather than a CSS grid of <div>s. That is what lets
 * a screen reader announce "Anti-wrinkle, one area, price £180" instead of reading four
 * disconnected fragments, and it is why the column labels exist even though the design
 * does not draw a header row: they are visually hidden, not absent.
 *
 * Each group is its own table under its own H2, because the groups do not share a
 * meaning down the column — "Injectables" and "Laser" are separate lists, not sections
 * of one.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$foot_note = (string) ($attributes['footNote'] ?? '');
$cta_label = (string) ($attributes['ctaLabel'] ?? '');
$cta_url   = (string) ($attributes['ctaUrl'] ?? '');

$groups = array_values(array_filter(
    (array) ($attributes['groups'] ?? []),
    static fn ($row): bool => is_array($row) && (string) ($row['group'] ?? '') !== ''
));

if ($groups === []) {
    return;
}
?>
<section <?php echo fm_wrapper(['fm-onyx-menu']); ?>>
  <?php foreach ($groups as $group) : ?>
    <?php
    $name  = (string) ($group['group'] ?? '');
    $num   = (string) ($group['num'] ?? '');
    $items = array_values(array_filter(
        (array) ($group['items'] ?? []),
        static fn ($row): bool => is_array($row) && (string) ($row['name'] ?? '') !== ''
    ));

    if ($items === []) {
        continue;
    }
    ?>
    <div class="fm-onyx-menu__group">
      <div class="fm-onyx-menu__group-head">
        <?php if ($num !== '') : ?>
          <span class="fm-onyx-menu__num" aria-hidden="true"><?php echo esc_html($num); ?></span>
        <?php endif; ?>

        <h2 class="fm-onyx-menu__group-name"><?php echo esc_html($name); ?></h2>
      </div>

      <div class="fm-onyx-menu__scroller">
        <table class="fm-onyx-menu__table">
          <caption class="fm-onyx-menu__caption">
            <?php
            printf(
                /* translators: %s: the treatment group's name, e.g. "Injectables". */
                esc_html__('%s — treatments, duration and price', 'foundations'),
                esc_html($name)
            );
            ?>
          </caption>
          <thead>
            <tr>
              <th scope="col"><?php esc_html_e('Treatment', 'foundations'); ?></th>
              <th scope="col"><?php esc_html_e('What it is', 'foundations'); ?></th>
              <th scope="col"><?php esc_html_e('Duration', 'foundations'); ?></th>
              <th scope="col"><?php esc_html_e('Price', 'foundations'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $item) : ?>
              <tr class="fm-onyx-menu__row">
                <th scope="row" class="fm-onyx-menu__name">
                  <?php echo esc_html((string) ($item['name'] ?? '')); ?>
                </th>
                <td class="fm-onyx-menu__desc">
                  <?php echo esc_html((string) ($item['desc'] ?? '')); ?>
                </td>
                <td class="fm-onyx-menu__time">
                  <?php echo esc_html((string) ($item['time'] ?? '')); ?>
                </td>
                <td class="fm-onyx-menu__price">
                  <?php echo esc_html((string) ($item['price'] ?? '')); ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endforeach; ?>

  <?php if ($foot_note !== '' || ($cta_label !== '' && $cta_url !== '')) : ?>
    <div class="fm-onyx-menu__foot">
      <?php if ($foot_note !== '') : ?>
        <p class="fm-onyx-menu__foot-note"><?php echo esc_html($foot_note); ?></p>
      <?php endif; ?>

      <?php if ($cta_label !== '' && $cta_url !== '') : ?>
        <a class="fm-onyx-button" href="<?php echo fm_url($cta_url); ?>">
          <?php echo esc_html($cta_label); ?>
        </a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</section>
