<?php
/**
 * Template Name: Pulse Atelier
 * Template Post Type: page
 * A dedicated shell for the editable Pulse Atelier header and footer blocks.
 */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#fm-content"><?php esc_html_e('Skip to content', 'foundations'); ?></a>
<main id="fm-content"><?php while (have_posts()) { the_post(); the_content(); } ?></main>
<?php wp_footer(); ?>
</body>
</html>
