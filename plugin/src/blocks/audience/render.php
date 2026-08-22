<?php
/**
 * Audience — who the service is for, as pill tags beside a portrait.
 *
 * The tags are a <ul>. They look like standalone pills, but they are a list of the
 * professions served, and a screen reader announcing "list, 11 items" is far more
 * useful than eleven unrelated fragments of text.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$raw = (string) ($attributes['tags'] ?? '');

// One tag per line in the editor.
$tags = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $raw) ?: [])));

$heading = (string) ($attributes['heading'] ?? '');

if ($tags === [] && $heading === '') {
    return;
}

$number    = (string) ($attributes['number'] ?? '');
$label     = (string) ($attributes['label'] ?? '');
$aside     = (string) ($attributes['aside'] ?? '');
$accent    = (string) ($attributes['headingAccent'] ?? '');
$media_id  = (int) ($attributes['mediaId'] ?? 0);
$media_alt = (string) ($attributes['mediaAlt'] ?? '');
$caption   = (string) ($attributes['caption'] ?? '');
?>
<section <?php echo fm_wrapper(['fm-audience']); ?>>
    <?php if ($number !== '' || $label !== '') : ?>
        <?php echo fm_section_rule($number, $label, $aside); ?>
    <?php endif; ?>

    <div class="fm-audience__grid">
        <div>
            <?php if ($heading !== '' || $accent !== '') : ?>
                <h2 class="fm-audience__heading">
                    <?php echo esc_html($heading); ?>
                    <?php if ($accent !== '') : ?>
                        <span class="fm-audience__heading-accent"><?php echo esc_html($accent); ?></span>
                    <?php endif; ?>
                </h2>
            <?php endif; ?>

            <?php if ($tags !== []) : ?>
                <ul class="fm-audience__tags">
                    <?php foreach ($tags as $tag) : ?>
                        <li class="fm-audience__tag"><?php echo esc_html($tag); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="fm-audience__portrait">
            <?php if ($media_id > 0) : ?>
                <?php echo fm_image($media_id, 'large', ['alt' => $media_alt, 'class' => 'fm-audience__img']); ?>
            <?php else : ?>
                <span class="fm-audience__placeholder">
                    <span class="fm-audience__tag-label">[ photo &mdash; portrait ]</span>
                    <?php if ($caption !== '') : ?>
                        <span class="fm-audience__hint"><?php echo esc_html($caption); ?></span>
                    <?php endif; ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
</section>
