<?php
/**
 * Template Name: Product Changelog
 * Template Post Type: page
 * Structured history of published releases for a product.
 * @package MrSobolle
 */
use MrSobolle\Data\Product_Data;
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="primary" class="site-main content-page changelog-page">
    <div class="container">
        <?php while ( have_posts() ) : the_post(); ?>
            <?php
            $parent_id = wp_get_post_parent_id( get_the_ID() );
            $key = Product_Data::key( $parent_id );
            $releases = $key && function_exists( 'sobolle_studio_releases' ) ? sobolle_studio_releases( $key ) : [];
            ?>
            <header class="content-page__header">
                <?php if ( $parent_id ) : ?><a class="page-back-link" href="<?php echo esc_url( get_permalink( $parent_id ) ); ?>"><?php echo esc_html( get_the_title( $parent_id ) ); ?></a><?php endif; ?>
                <h1 class="content-page__title"><?php echo esc_html( get_the_title() ); ?></h1>
                <div class="entry-content"><?php the_content(); ?></div>
            </header>
            <?php if ( $releases ) : ?>
                <div class="changelog-list">
                    <?php foreach ( $releases as $release ) : ?>
                        <?php
                        $version = (string) get_post_meta( $release->ID, '_studio_release_version', true );
                        $date = (string) get_post_meta( $release->ID, '_studio_release_date', true );
                        if ( '' === $version || '' === $date ) { continue; }
                        ?>
                        <article class="changelog-entry">
                            <header><h2><?php echo esc_html( $version ); ?></h2><time datetime="<?php echo esc_attr( $date ); ?>"><?php echo esc_html( $date ); ?></time></header>
                            <div class="entry-content"><?php echo apply_filters( 'the_content', $release->post_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p class="catalog-page__empty"><?php esc_html_e( 'Version history will appear after the first release.', 'mrsobolle' ); ?></p>
            <?php endif; ?>
        <?php endwhile; ?>
    </div>
</main>
<?php get_footer(); ?>