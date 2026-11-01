<?php
/**
 * Template Name: Apps catalog
 * Template Post Type: page
 *
 * A native WordPress Page directory; product landing pages are child pages.
 * A translation of this parent page uses the same template.
 *
 * @package MrSobolle
 */

defined( 'ABSPATH' ) || exit;

get_header();

get_template_part(
    'template-parts/pages/catalog',
    null,
    [
        'kind' => 'apps',
    ]
);

get_footer();