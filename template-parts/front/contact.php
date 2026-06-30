<?php
/**
 * Front page contact section.
 *
 * @package MrSobolle
 */

defined( 'ABSPATH' ) || exit;

$kicker = isset( $kicker ) ? (string) $kicker : '';
$title  = isset( $title ) ? (string) $title : '';
$text   = isset( $text ) ? (string) $text : '';
$links  = isset( $links ) && is_array( $links ) ? $links : [];
?>

<section id="contact" class="content-section">
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

        <?php if ( ! empty( $links ) ) : ?>
            <ul class="contact-links" aria-label="<?php esc_attr_e( 'Contact links', 'mrsobolle' ); ?>">
                <?php foreach ( $links as $link ) : ?>
                    <?php
                    if ( ! is_array( $link ) ) {
                        continue;
                    }

                    $link_label = isset( $link['label'] ) ? (string) $link['label'] : '';
                    $link_url   = isset( $link['url'] ) ? (string) $link['url'] : '';

                    if ( '' === $link_label || '' === $link_url || '#' === $link_url ) {
                        continue;
                    }
                    ?>

                    <li class="contact-links__item">
                        <a class="contact-links__link" href="<?php echo esc_url( $link_url ); ?>">
                            <?php echo esc_html( $link_label ); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>