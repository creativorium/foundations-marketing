<?php
/**
 * Offline renderer smoke test. Uses the installed WordPress block parser and real
 * block PHP, with small adapters for WP services. Does not load WordPress or its DB.
 * php tests/render-smoke.php /path/to/wordpress [--preview]
 * --preview writes an isolated HTML document to stdout for browser checks.
 * This does not replace checking the active WordPress theme and Gutenberg editor.
 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { exit; }
error_reporting(E_ALL);
set_error_handler(static function (int $severity, string $message, string $file, int $line): never {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

$wp = rtrim($argv[1] ?? '', '/\\');
if (!is_file($wp . '/wp-includes/class-wp-block-parser.php')) {
    fwrite(STDERR, "Pass the path to an installed WordPress directory.\n");
    exit(1);
}
$repo = dirname(__DIR__, 5);
define('ABSPATH', $wp . '/');
define('FM_BLOCKS_DIR', $repo . '/plugin/');
define('FM_BLOCKS_URL', 'file:///' . str_replace(['\\', ' '], ['/', '%20'], FM_BLOCKS_DIR));
require $wp . '/wp-includes/class-wp-block-parser.php';

// Narrow adapters: no database, network, hooks, uploads or writes.
function add_action(...$args): void {}
function esc_html($value): string { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function esc_attr($value): string { return esc_html($value); }
function esc_url($value): string {
    $value = (string) $value;
    return preg_match('/^(?:javascript|data):/i', trim($value)) ? '' : esc_attr($value);
}
function sanitize_hex_color($value): ?string { return preg_match('/^#[a-f0-9]{3}(?:[a-f0-9]{3})?$/i', $value) ? $value : null; }
function sanitize_title($value): string { return preg_replace('/[^a-z0-9_-]/', '', strtolower($value)); }
function __($value, $domain = ''): string { return $value; }
function esc_html_e($value, $domain = ''): void { echo esc_html($value); }
function checked($left, $right): void { if ($left === $right) { echo 'checked="checked"'; } }
function wp_unique_id($prefix = ''): string { static $id = 0; return $prefix . ++$id; }
function wp_getimagesize($path): array|false { return getimagesize($path); }
function wp_get_attachment_image(...$args): string { return ''; }
function get_block_wrapper_attributes(array $attributes = []): string {
    return implode(' ', array_map(static fn ($key): string => $key . '="' . esc_attr($attributes[$key]) . '"', array_keys($attributes)));
}
require FM_BLOCKS_DIR . 'inc/helpers.php';

function check(bool $condition, string $message): void {
    if (!$condition) { throw new RuntimeException($message); }
}

function render_fixture(array $parsed): string {
    $content = '';
    $i = 0;
    foreach ($parsed['innerContent'] as $part) {
        $content .= $part === null ? render_fixture($parsed['innerBlocks'][$i++]) : $part;
    }
    $name = $parsed['blockName'] ?? '';
    if (!str_starts_with($name, 'foundations/')) { return $content; }
    $folder = FM_BLOCKS_DIR . 'src/blocks/' . substr($name, strlen('foundations/'));
    check(is_file($folder . '/block.json'), 'Unknown block: ' . $name);
    $metadata = json_decode(file_get_contents($folder . '/block.json'), true, 512, JSON_THROW_ON_ERROR);
    $defaults = array_map(static fn ($schema) => $schema['default'] ?? null, $metadata['attributes']);
    $attributes = array_merge($defaults, $parsed['attrs']);
    // Verify the supplied composition uses the actual declared attribute types.
    foreach ($parsed['attrs'] as $key => $value) {
        if ($key === 'anchor' || $key === 'className') { continue; }
        check(isset($metadata['attributes'][$key]), 'Unknown attribute: ' . $name . '/' . $key);
        $type = $metadata['attributes'][$key]['type'];
        $valid = match ($type) {
            'array' => is_array($value), 'string' => is_string($value),
            'number' => is_numeric($value), 'boolean' => is_bool($value), default => true,
        };
        check($valid, 'Invalid attribute type: ' . $key);
    }
    ob_start();
    require $folder . '/render.php';
    return ob_get_clean();
}

$markup = file_get_contents(dirname(__DIR__) . '/content.html');
$parsed = (new WP_Block_Parser())->parse($markup);
$html = implode('', array_map('render_fixture', $parsed));
check(!preg_match('/__bundler|DCLogic|sc-camel|<script|<sc-/i', $html), 'Reference runtime leaked into rendering.');
check(!str_contains($html, '{{'), 'Unresolved reference expression.');
check(substr_count($html, 'data-pa-part=') === 16, 'Expected twelve home sections and four shell parts.');
check(substr_count($html, 'data-pa-faq ') === 5, 'Expected five FAQ buttons.');
check(substr_count($html, 'data-pa-quote=') === 3, 'Expected three testimonial buttons.');
check(substr_count($html, '<footer ') === 1, 'Missing reference footer.');
check(substr_count($html, 'background-image:') === 6, 'Expected six background images.');
check(!str_contains($html, 'mailto:'), 'Invented mailto interaction.');
check(str_contains($html, 'type="email"'), 'Newsletter email input missing.');
check(!str_contains($html, '<form'), 'Reference has no submitting form.');
preg_match_all('/\bid="([^"]+)"/', $html, $ids);
check(count($ids[1]) === count(array_unique($ids[1])), 'Duplicate IDs.');
$hostile = ['blockName' => 'foundations/pulse-atelier-section', 'attrs' => ['variant' => '../../wp-config', 'settings' => ['fields' => ['strength_that' => '<img src=x onerror=alert(1)>', 'href_book' => 'javascript:alert(1)'], 'images' => ['hero' => "https://test.invalid/x');color:red;"]]], 'innerContent' => [], 'innerBlocks' => []];
$safe = render_fixture($hostile);
check(!str_contains($safe, '<img src=x'), 'Text must be escaped.');
check(!str_contains($safe, 'javascript:'), 'Unsafe link protocol.');
check(!str_contains($safe, 'color:red'), 'Unsafe background URL.');
if (in_array('--preview', $argv, true)) {
    $file = static fn ($path): string => 'file:///' . str_replace(['\\', ' '], ['/', '%20'], $repo . '/' . $path);
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Pulse Atelier</title>';
    echo '<link rel="stylesheet" href="' . $file('theme/build/main.css') . '"><link rel="stylesheet" href="' . $file('plugin/build/frontend.css') . '">';
    echo '</head><body class="page-template-pulse-atelier"><main id="fm-content">' . $html . '</main><script src="' . $file('plugin/build/frontend.js') . '"></script></body></html>';
} else {
    echo "PASS: WordPress parser, schemas, real PHP rendering, twelve sections, header/footer, interaction markup, unique IDs, escaped text, safe links/media and variant allowlist.\n";
}
