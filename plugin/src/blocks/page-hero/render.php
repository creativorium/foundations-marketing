<?php
/**
 * Page hero — the interior-page opener.
 *
 * Distinct from foundations/hero, which is the homepage's device-mockup version. This
 * one pairs the headline with an at-a-glance spec card. Like the homepage hero it is
 * marked multiple:false because it renders the page's single H1.
 *
 * The spec rows are a <dl>: each label genuinely describes its value, and that pairing
 * is worth carrying into the markup rather than flattening it into rows of spans.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$heading = (string) ($attributes['heading'] ?? '');
$accent  = (string) ($attributes['headingAccent'] ?? '');

if ($heading === '' && $accent === '') {
    return;
}

$badge      = (string) ($attributes['badge'] ?? '');
$note       = (string) ($attributes['note'] ?? '');
$lede       = (string) ($attributes['lede'] ?? '');
$card_title = (string) ($attributes['cardTitle'] ?? '');
$cta_text   = (string) ($attributes['ctaText'] ?? '');
$cta_url    = (string) ($attributes['ctaUrl'] ?? '');
$rows       = is_array($attributes['rows'] ?? null) ? $attributes['rows'] : [];
?>
<section <?php echo fm_wrapper(['fm-page-hero']); ?>>
    <?php if ($badge !== '' || $note !== '') : ?>
        <p class="fm-page-hero__flags">
            <?php if ($badge !== '') : ?>
                <span class="fm-page-hero__badge"><?php echo esc_html($badge); ?></span>
            <?php endif; ?>
            <?php if ($note !== '') : ?>
                <span class="fm-page-hero__note"><?php echo esc_html($note); ?></span>
            <?php endif; ?>
        </p>
    <?php endif; ?>

    <div class="fm-page-hero__grid">
        <div class="fm-page-hero__copy">
            <h1 class="fm-page-hero__title">
                <?php echo esc_html($heading); ?>
                <?php if ($accent !== '') : ?>
                    <span class="fm-page-hero__title-accent"><?php echo esc_html($accent); ?></span>
                <?php endif; ?>
            </h1>

            <?php if ($lede !== '') : ?>
                <p class="fm-page-hero__lede"><?php echo esc_html($lede); ?></p>
            <?php endif; ?>
        </div>

        <?php if ($rows !== [] || $cta_text !== '') : ?>
            <div class="fm-page-hero__card">
                <?php if ($card_title !== '') : ?>
                    <h2 class="fm-page-hero__card-title"><?php echo esc_html($card_title); ?></h2>
                <?php endif; ?>

                <?php if ($rows !== []) : ?>
                    <dl class="fm-page-hero__spec">
                        <?php foreach ($rows as $row) : ?>
                            <?php
                            $key   = (string) ($row['k'] ?? '');
                            $value = (string) ($row['v'] ?? '');
                            ?>
                            <?php if ($key === '') { continue; } ?>
                            <div class="fm-page-hero__spec-row">
                                <dt><?php echo esc_html($key); ?></dt>
                                <dd><?php echo esc_html($value); ?></dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>
                <?php endif; ?>

                <?php if ($cta_text !== '') : ?>
                    <a class="fm-page-hero__cta" href="<?php echo fm_url($cta_url); ?>">
                        <?php echo esc_html($cta_text); ?>
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
