<?php
use MrSobolle\Data\Site_Data;
defined( 'ABSPATH' ) || exit;
?>
<footer id="colophon" class="site-footer"><div class="site-footer__inner">
<div class="site-footer__content">
<div class="site-footer__copyright">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> Sobolle Studio</div>
<nav class="footer-navigation" aria-label="<?php esc_attr_e( 'Footer menu', 'mrsobolle' ); ?>"><ul class="footer-navigation__list">
<?php foreach ( [ 'about' => __( 'About', 'mrsobolle' ), 'contact' => __( 'Contact', 'mrsobolle' ), 'privacy' => __( 'Privacy', 'mrsobolle' ), 'terms' => __( 'Terms', 'mrsobolle' ) ] as $slug => $label ) :
$url = Site_Data::content_page_url( $slug ); if ( ! $url ) { continue; } ?>
<li><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a></li>
<?php endforeach; ?></ul></nav>
</div>
<ul class="site-footer__social-links" aria-label="<?php esc_attr_e( 'Social links', 'mrsobolle' ); ?>">
<?php foreach ( Site_Data::social_links() as $link ) : ?><li><a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li><?php endforeach; ?>
</ul>
</div></footer>
</div>
<?php wp_footer(); ?>
</body></html>
