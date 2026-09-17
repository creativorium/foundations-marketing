<?php
/**
 * The site footer. Same contract as the header: customer edits values, we own layout.
 *
 * Every group below renders only when it has content. A customer who fills in nothing but
 * a phone number gets a footer with a phone number, not a footer with four empty columns.
 *
 * @var array    $attributes
 * @var string   $content
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$variant      = (string) ($attributes['variant'] ?? 'columns');
$show_contact = (bool) ($attributes['showContact'] ?? true);

$nav     = fm_nav('nav_footer');
$social  = fm_setting('social', []);
$social  = is_array($social) ? $social : [];

$name = (string) fm_setting('site_name');

if ($name === '') {
    $name = (string) get_bloginfo('name');
}

$classes = 'fm-site-footer fm-site-footer--' . sanitize_html_class($variant);
?>
<div <?php echo wp_kses_data(get_block_wrapper_attributes(['class' => $classes])); ?>>
  <div class="fm-site-footer__inner">

    <?php if ($show_contact && (fm_has_setting('phone') || fm_has_setting('email') || fm_has_setting('address'))) : ?>
      <div class="fm-site-footer__group fm-site-footer__contact">
        <h2 class="fm-site-footer__heading"><?php esc_html_e('Contact', 'foundations-customer'); ?></h2>
        <ul class="fm-site-footer__list">
          <?php if (fm_has_setting('phone')) : ?>
            <?php $phone = (string) fm_setting('phone'); ?>
            <li>
              <?php // tel: needs the number with no spaces; the label keeps them for reading. ?>
              <a href="tel:<?php echo esc_attr(preg_replace('/[^\d+]/', '', $phone) ?? ''); ?>">
                <?php echo esc_html($phone); ?>
              </a>
            </li>
          <?php endif; ?>

          <?php if (fm_has_setting('email')) : ?>
            <li>
              <a href="mailto:<?php echo esc_attr((string) fm_setting('email')); ?>">
                <?php echo esc_html((string) fm_setting('email')); ?>
              </a>
            </li>
          <?php endif; ?>

          <?php if (fm_has_setting('address')) : ?>
            <li>
              <address class="fm-site-footer__address">
                <?php echo nl2br(esc_html((string) fm_setting('address'))); ?>
              </address>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($nav !== []) : ?>
      <div class="fm-site-footer__group">
        <h2 class="fm-site-footer__heading"><?php esc_html_e('Links', 'foundations-customer'); ?></h2>
        <nav aria-label="<?php esc_attr_e('Footer', 'foundations-customer'); ?>">
          <ul class="fm-site-footer__list">
            <?php foreach ($nav as $item) : ?>
              <li>
                <a
                  href="<?php echo esc_url($item['url']); ?>"
                  <?php echo $item['current'] ? ' aria-current="page"' : ''; ?>
                ><?php echo esc_html($item['label']); ?></a>
              </li>
            <?php endforeach; ?>
          </ul>
        </nav>
      </div>
    <?php endif; ?>

    <?php if ($social !== []) : ?>
      <div class="fm-site-footer__group">
        <h2 class="fm-site-footer__heading"><?php esc_html_e('Follow', 'foundations-customer'); ?></h2>
        <ul class="fm-site-footer__list fm-site-footer__social">
          <?php foreach ($social as $link) : ?>
            <?php
            if (!is_array($link)) {
                continue;
            }

            $url     = (string) ($link['url'] ?? '');
            $network = (string) ($link['network'] ?? '');

            if ($url === '' || $network === '') {
                continue;
            }
            ?>
            <li>
              <?php
              // No icon font (§9), and no bare "opens in a new tab" surprise — the label
              // is the network name, which a screen reader can announce usefully.
              ?>
              <a href="<?php echo esc_url($url); ?>" rel="noopener noreferrer" target="_blank">
                <?php echo esc_html($network); ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="fm-site-footer__legal">
      <?php if (fm_has_setting('footer_text')) : ?>
        <p class="fm-site-footer__text">
          <?php echo nl2br(esc_html((string) fm_setting('footer_text'))); ?>
        </p>
      <?php endif; ?>

      <p class="fm-site-footer__copyright">
        <?php
        printf(
            /* translators: 1: year, 2: business name. */
            esc_html__('© %1$s %2$s', 'foundations-customer'),
            esc_html((string) gmdate('Y')),
            esc_html($name)
        );
        ?>
      </p>
    </div>

  </div>
</div>
