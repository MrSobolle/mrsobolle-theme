<?php
/**
 * Template Name: Work catalog
 * Template Post Type: page
 *
 * Case studies are child pages, with one translated directory per language.
 *
 * @package MrSobolle
 */

defined( 'ABSPATH' ) || exit;

get_header();

get_template_part(
    'template-parts/pages/catalog',
    null,
    [
        'kind' => 'work',
    ]
);

get_footer();