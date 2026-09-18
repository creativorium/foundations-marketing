<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
require_once __DIR__ . '/helpers.php';
$defaults = fm_pk_defaults();
$variant = (string) ($attributes['variant'] ?? 'hero');
if (!isset($defaults[$variant])) { $variant = 'hero'; }
echo '<div ' . fm_wrapper(['fm-pulse-kinetic']) . ' data-pk-root>';
fm_pk_part($variant, $defaults, is_array($attributes['settings'] ?? null) ? $attributes['settings'] : [], __DIR__);
echo '</div>';
