<?php
/**
 * Front page content.
 *
 * @package MrSobolle
 */

use MrSobolle\Data\Page_Data;
use MrSobolle\View;

defined( 'ABSPATH' ) || exit;

$data = Page_Data::front_page();

$hero_data     = isset( $data['hero'] ) && is_array( $data['hero'] ) ? $data['hero'] : [];
$about_data    = isset( $data['about'] ) && is_array( $data['about'] ) ? $data['about'] : [];
$projects_data = isset( $data['projects'] ) && is_array( $data['projects'] ) ? $data['projects'] : [];
$contact_data  = isset( $data['contact'] ) && is_array( $data['contact'] ) ? $data['contact'] : [];
?>

<main id="primary" class="site-main front-page">

    <?php
    View::render( 'front/hero', $hero_data );
    View::render( 'front/about', $about_data );
    View::render( 'front/projects', $projects_data );
    View::render( 'front/contact', $contact_data );
    ?>

</main>