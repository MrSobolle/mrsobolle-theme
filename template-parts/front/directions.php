<?php
/**
 * Two studio disciplines presented as editorial text, not product cards.
 *
 * @package MrSobolle
 */

defined( 'ABSPATH' ) || exit;

$kicker = isset( $kicker ) ? (string) $kicker : '';
$title  = isset( $title ) ? (string) $title : '';
$items  = isset( $items ) && is_array( $items ) ? $items : [];
?>
<section id="directions" class="studio-directions" aria-labelledby="studio-directions-title">
    <div class="container studio-directions__inner">
        <header class="studio-directions__header">
            <?php if ( '' !== $kicker ) : ?>
                <p class="studio-directions__eyebrow"><?php echo esc_html( $kicker ); ?></p>
            <?php endif; ?>
            <h2 class="studio-directions__heading" id="studio-directions-title"><?php echo esc_html( $title ); ?></h2>
        </header>

        <?php if ( ! empty( $items ) ) : ?>
            <div class="studio-directions__grid">
                <?php foreach ( $items as $item ) : ?>
                    <?php
                    if ( ! is_array( $item ) ) {
                        continue;
                    }
                    $number = isset( $item['number'] ) ? (string) $item['number'] : '';
                    $name   = isset( $item['title'] ) ? (string) $item['title'] : '';
                    $text   = isset( $item['text'] ) ? (string) $item['text'] : '';
                    $url    = isset( $item['url'] ) ? (string) $item['url'] : '';
                    $label  = isset( $item['link_label'] ) ? (string) $item['link_label'] : '';
                    if ( '' === $name ) {
                        continue;
                    }
                    ?>
                    <article class="studio-direction">
                        <span class="studio-direction__number" aria-hidden="true"><?php echo esc_html( $number ); ?></span>
                        <div class="studio-direction__body">
                            <h3 class="studio-direction__title"><?php echo esc_html( $name ); ?></h3>
                            <?php if ( '' !== $text ) : ?>
                                <p class="studio-direction__text"><?php echo esc_html( $text ); ?></p>
                            <?php endif; ?>
                            <?php if ( '' !== $url && '' !== $label ) : ?>
                                <a class="studio-direction__link" href="<?php echo esc_url( $url ); ?>">
                                    <span><?php echo esc_html( $label ); ?></span>
                                    <span class="studio-direction__arrow" aria-hidden="true">&#8599;</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>