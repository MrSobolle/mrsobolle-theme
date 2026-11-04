<?php
defined( 'ABSPATH' ) || exit;
$children = \MrSobolle\Data\Directory_Data::children( get_the_ID() );
if ( ! $children ) { return; }
?>
<nav class="product-resources" aria-label="<?php esc_attr_e( 'Product resources', 'mrsobolle' ); ?>">
<h2><?php esc_html_e( 'Product resources', 'mrsobolle' ); ?></h2>
<ul><?php foreach ( $children as $child ) : ?><li><a href="<?php echo esc_url( get_permalink( $child ) ); ?>"><?php echo esc_html( get_the_title( $child ) ); ?></a></li><?php endforeach; ?></ul>
</nav>
