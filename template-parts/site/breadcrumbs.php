<?php
defined( 'ABSPATH' ) || exit;
if ( is_front_page() || ! is_singular() ) { return; }
$crumbs = \MrSobolle\Seo::breadcrumbs();
?>
<nav class="breadcrumbs container" aria-label="<?php esc_attr_e( 'Breadcrumbs', 'mrsobolle' ); ?>"><ol>
<?php foreach ( $crumbs as $i => $crumb ) : ?>
<li><?php if ( $i === count( $crumbs ) - 1 ) : ?><span aria-current="page"><?php echo esc_html( $crumb['name'] ); ?></span><?php else : ?><a href="<?php echo esc_url( $crumb['url'] ); ?>"><?php echo esc_html( $crumb['name'] ); ?></a><?php endif; ?></li>
<?php endforeach; ?>
</ol></nav>
