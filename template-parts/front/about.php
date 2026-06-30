<?php
/**
 * Front page about section.
 *
 * @package MrSobolle
 */

defined( 'ABSPATH' ) || exit;

$kicker = isset( $kicker ) ? (string) $kicker : '';
$title  = isset( $title ) ? (string) $title : '';
$text   = isset( $text ) ? (string) $text : '';
?>

<section id="about" class="content-section">
    <div class="container">
        <?php if ( '' !== $kicker ) : ?>
            <p class="section-kicker">
                <?php echo esc_html( $kicker ); ?>
            </p>
        <?php endif; ?>

        <?php if ( '' !== $title ) : ?>
            <h2 class="section-title">
                <?php echo esc_html( $title ); ?>
            </h2>
        <?php endif; ?>

        <?php if ( '' !== $text ) : ?>
            <p class="section-text">
                <?php echo esc_html( $text ); ?>
            </p>
        <?php endif; ?>
    </div>
</section>