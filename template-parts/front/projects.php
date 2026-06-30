<?php
/**
 * Front page projects section.
 *
 * @package MrSobolle
 */

defined( 'ABSPATH' ) || exit;

$kicker = isset( $kicker ) ? (string) $kicker : '';
$title  = isset( $title ) ? (string) $title : '';
$items  = isset( $items ) && is_array( $items ) ? $items : [];
?>

<section id="projects" class="content-section content-section--muted">
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

        <?php if ( ! empty( $items ) ) : ?>
            <div class="cards-grid">
                <?php foreach ( $items as $item ) : ?>
                    <?php
                    if ( ! is_array( $item ) ) {
                        continue;
                    }

                    $item_title = isset( $item['title'] ) ? (string) $item['title'] : '';
                    $item_text  = isset( $item['text'] ) ? (string) $item['text'] : '';

                    if ( '' === $item_title && '' === $item_text ) {
                        continue;
                    }
                    ?>

                    <article class="project-card">
                        <?php if ( '' !== $item_title ) : ?>
                            <h3 class="project-card__title">
                                <?php echo esc_html( $item_title ); ?>
                            </h3>
                        <?php endif; ?>

                        <?php if ( '' !== $item_text ) : ?>
                            <p class="project-card__text">
                                <?php echo esc_html( $item_text ); ?>
                            </p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
