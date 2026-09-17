<?php
/** Image rendering shared by editorial photo blocks; pack paths remain portable. */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }

function fm_editorial_image(array $attributes, bool $eager = false): string
{
    $id = (int) ($attributes['mediaId'] ?? 0);
    $alt = (string) ($attributes['mediaAlt'] ?? '');
    if ($id > 0) {
        $image = fm_image($id, 'full', ['alt' => $alt], $eager);
        if ($image !== '') { return $image; }
    }

    $path = (string) ($attributes['fallbackImage'] ?? '');
    // Only a named template's local image directory. Never a URL or traversal path.
    if (preg_match('~\A[a-z0-9-]+/assets/[a-z0-9-]+\.(?:webp|jpg|png)\z~D', $path)) {
        $relative = 'src/templates/' . $path;
        $file = FM_BLOCKS_DIR . $relative;
        $dimensions = is_file($file) ? wp_getimagesize($file) : false;
        if ($dimensions) {
            return sprintf(
                '<img src="%s" alt="%s" width="%d" height="%d" loading="%s" decoding="async"%s>',
                esc_url(FM_BLOCKS_URL . $relative), esc_attr($alt),
                $dimensions[0], $dimensions[1], $eager ? 'eager' : 'lazy',
                $eager ? ' fetchpriority="high"' : ''
            );
        }
    }

    // Decorative empty state; no broken URLs, external dependencies or false alt text.
    return '<span class="fm-editorial-photo-empty" aria-hidden="true"></span>';
}
