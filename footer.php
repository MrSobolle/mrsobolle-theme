<?php
/**
 * Site footer.
 *
 * @package MrSobolle
 */

use MrSobolle\Data\Site_Data;

defined( 'ABSPATH' ) || exit;

$site_name    = Site_Data::site_name();
$social_links = Site_Data::social_links();
?>

<footer id="colophon" class="site-footer">
    <div class="site-footer__inner">

        <div class="site-footer__content">
            <div class="site-footer__copyright">
                <?php
                printf(
                    esc_html__( '© %1$s %2$s. All rights reserved.', 'mrsobolle' ),
                    esc_html( gmdate( 'Y' ) ),
                    esc_html( $site_name )
                );
                ?>
            </div>

            <?php if ( has_nav_menu( 'footer' ) ) : ?>
                <nav class="footer-navigation" aria-label="<?php esc_attr_e( 'Footer menu', 'mrsobolle' ); ?>">
                    <?php
                    wp_nav_menu(
                        [
                            'theme_location' => 'footer',
                            'menu_id'        => 'footer-menu',
                            'menu_class'     => 'footer-navigation__list',
                            'container'      => false,
                            'fallback_cb'    => false,
                            'depth'          => 1,
                        ]
                    );
                    ?>
                </nav>
            <?php endif; ?>
        </div>

        <?php if ( ! empty( $social_links ) ) : ?>
            <ul class="site-footer__social-links" aria-label="<?php esc_attr_e( 'Social links', 'mrsobolle' ); ?>">
                <?php foreach ( $social_links as $link ) : ?>
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

                    <li class="site-footer__social-item">
                        <a class="site-footer__social-link" href="<?php echo esc_url( $link_url ); ?>">
                            <?php echo esc_html( $link_label ); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

    </div>
</footer>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>