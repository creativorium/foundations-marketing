<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
require_once __DIR__ . '/helpers.php';
$defaults = fm_pa_defaults('pulse-atelier-section');
$variant = (string) ($attributes['variant'] ?? 'hero');
if (!isset($defaults[$variant])) { $variant = 'hero'; }
echo '<div ' . fm_wrapper(['fm-pulse-atelier']) . ' data-pa-root>';
fm_pa_part($variant, $defaults, is_array($attributes['settings'] ?? null) ? $attributes['settings'] : [], __DIR__);
echo '</div>';


