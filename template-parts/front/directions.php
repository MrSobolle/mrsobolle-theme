<?php
/**
 * Full-bleed unified glass panel containing two whole-area links.
 *
 * @package MrSobolle
 */
defined( 'ABSPATH' ) || exit;
$kicker = isset( $kicker ) ? (string) $kicker : '';
$items = isset( $items ) && is_array( $items ) ? $items : [];
?>
<section id="directions" class="studio-directions" data-studio-glass aria-labelledby="studio-directions-title">
    <h2 class="screen-reader-text" id="studio-directions-title"><?php echo esc_html( $kicker ); ?></h2>
    <div class="studio-directions__grid">
        <?php foreach ( $items as $item ) : ?>
            <?php
            if ( ! is_array( $item ) ) {
                continue;
            }
            $name = isset( $item['title'] ) ? (string) $item['title'] : '';
            $text = isset( $item['text'] ) ? (string) $item['text'] : '';
            $url  = isset( $item['url'] ) ? (string) $item['url'] : '';
            if ( '' === $name || '' === $url ) {
                continue;
            }
            ?>
            <a class="studio-tile" href="<?php echo esc_url( $url ); ?>" data-studio-tile>
                <div class="studio-tile__body">
                    <h3 class="studio-tile__label"><?php echo esc_html( $name ); ?></h3>
                    <?php if ( '' !== $text ) : ?>
                        <p class="studio-tile__description"><?php echo esc_html( $text ); ?></p>
                    <?php endif; ?>
                </div>
                <span class="studio-tile__arrow" aria-hidden="true">&#8599;</span>
            </a>
        <?php endforeach; ?>
    </div>
</section>
