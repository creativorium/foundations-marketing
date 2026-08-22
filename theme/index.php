<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();
    the_content();
endwhile;

get_footer();
