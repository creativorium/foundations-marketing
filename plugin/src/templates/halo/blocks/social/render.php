<?php
/**
 * Halo — the social strip.
 *
 * Images the customer uploads, not a live Instagram feed. An embedded feed would mean a
 * third-party script on every page load, an API token to keep alive, and a section that
 * empties itself the day the token expires — against §9 and against "no new dependency"
 * (how-to-work.md §0.1). Five pictures and a link do the same job and keep working.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$handle     = (string) ($attributes['handle'] ?? '');
$link_label = (string) ($attributes['linkLabel'] ?? '');
$link_url   = (string) ($attributes['linkUrl'] ?? '');

$posts = array_values(array_filter(
    (array) ($attributes['posts'] ?? []),
    static fn ($row): bool => is_array($row)
        && ((int) ($row['id'] ?? 0) > 0 || (string) ($row['note'] ?? '') !== '')
));

if ($handle === '' && $posts === []) {
    return;
}
?>
<section <?php echo fm_wrapper(['fm-halo-social', 'fm-halo-band']); ?>>
  <div class="fm-halo-social__head">
    <?php if ($handle !== '') : ?>
      <h2 class="fm-halo-display fm-halo-social__handle"><?php echo esc_html($handle); ?></h2>
    <?php endif; ?>

    <?php if ($link_label !== '' && $link_url !== '') : ?>
      <a class="fm-halo-social__link" href="<?php echo fm_url($link_url); ?>">
        <?php echo esc_html($link_label); ?>
        <span aria-hidden="true">&rarr;</span>
      </a>
    <?php endif; ?>
  </div>

  <ul class="fm-halo-social__grid">
    <?php foreach ($posts as $post) : ?>
      <?php
      $id   = (int) ($post['id'] ?? 0);
      $alt  = (string) ($post['alt'] ?? '');
      $note = (string) ($post['note'] ?? '');
      ?>
      <li class="fm-halo-plate fm-halo-social__cell">
        <?php if ($id > 0) : ?>
          <?php echo fm_image($id, 'medium', ['alt' => esc_attr($alt)]); ?>
        <?php elseif ($note !== '') : ?>
          <span class="fm-halo-plate__label"><?php echo esc_html($note); ?></span>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>
</section>
