<?php
/**
 * Single-screen Studio entrance. Content-rich Work/Apps and About/Contact
 * live on real linked WordPress pages, not behind an inaccessible fold.
 *
 * @package MrSobolle
 */

use MrSobolle\Data\Page_Data;
use MrSobolle\View;

defined( 'ABSPATH' ) || exit;

$data = Page_Data::front_page();
$hero_data = isset( $data['hero'] ) && is_array( $data['hero'] ) ? $data['hero'] : [];
$directions_data = isset( $data['directions'] ) && is_array( $data['directions'] ) ? $data['directions'] : [];
?>
<main id="primary" tabindex="-1" class="site-main front-page studio-one-screen" data-studio-screen>
    <?php
    // The experimental perspective room is deliberately disabled in the clean Home concept.
    View::render( 'front/hero', $hero_data );
    View::render( 'front/directions', $directions_data );
    ?>
</main>
