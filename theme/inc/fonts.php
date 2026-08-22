<?php
/**
 * Web fonts.
 *
 * Self-hosting is meaningfully faster than the Google Fonts CDN: it removes a DNS
 * lookup, a TLS handshake and a redirect from the critical path, and lets the font
 * be preloaded from the same origin. So: if the woff2 files are present in
 * theme/assets/fonts/, we serve them ourselves. If they are not, we fall back to
 * the CDN so the site never renders unstyled.
 *
 * To self-host, drop these into theme/assets/fonts/ (see doc/FONTS.md):
 *   archivo-latin.woff2            (variable, 400..800)
 *   archivo-latin-ext.woff2        (variable, 400..800)
 *   instrument-serif-latin.woff2         (400)
 *   instrument-serif-latin-italic.woff2  (400 italic)
 *
 * Only the `latin` subsets are required — the copy is English. latin-ext is optional
 * and used for the occasional accented name.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/** The one font that must be there for self-hosting to be worth switching on. */
function fm_fonts_are_self_hosted(): bool
{
    return file_exists(FM_THEME_DIR . '/assets/fonts/archivo-latin.woff2');
}

/**
 * @return array<int, string> Font files to preload, highest priority first.
 */
function fm_preload_fonts(): array
{
    if (!fm_fonts_are_self_hosted()) {
        return [];
    }

    // Only the latin subsets go in the critical path — everything else loads on demand.
    return array_values(array_filter([
        'archivo-latin.woff2',
        'instrument-serif-latin.woff2',
    ], fn (string $f): bool => file_exists(FM_THEME_DIR . '/assets/fonts/' . $f)));
}

/**
 * Preload the display font. Without this the browser only discovers the font after
 * it has parsed the CSS, which pushes back the largest-contentful-paint text.
 */
function fm_font_preloads(): void
{
    foreach (fm_preload_fonts() as $file) {
        printf(
            '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
            esc_url(FM_THEME_URI . '/assets/fonts/' . $file)
        );
    }
}
add_action('wp_head', 'fm_font_preloads', 1);

/**
 * The @font-face rules for the self-hosted files, inlined in <head> so they are
 * available before the main stylesheet arrives. `font-display: swap` means text is
 * readable immediately in the fallback face rather than invisible.
 */
function fm_font_face_css(): string
{
    $uri = FM_THEME_URI . '/assets/fonts/';

    $latin = 'U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, '
        . 'U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, '
        . 'U+2212, U+2215, U+FEFF, U+FFFD';
    $latin_ext = 'U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, '
        . 'U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, '
        . 'U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF';

    $faces = [
        // Archivo ships as a variable font: one file covers 400 through 800, which is
        // far smaller than five static weights.
        ['Archivo', 'normal', '400 800', 'archivo-latin.woff2', $latin],
        ['Archivo', 'normal', '400 800', 'archivo-latin-ext.woff2', $latin_ext],
        ['Instrument Serif', 'normal', '400', 'instrument-serif-latin.woff2', $latin],
        ['Instrument Serif', 'italic', '400', 'instrument-serif-latin-italic.woff2', $latin],
    ];

    $css = '';

    foreach ($faces as [$family, $style, $weight, $file, $range]) {
        if (!file_exists(FM_THEME_DIR . '/assets/fonts/' . $file)) {
            continue;
        }

        $css .= sprintf(
            "@font-face{font-family:'%s';font-style:%s;font-weight:%s;font-display:swap;"
            . "src:url(%s) format('woff2');unicode-range:%s}",
            $family,
            $style,
            $weight,
            $uri . $file,
            $range
        );
    }

    return $css;
}
