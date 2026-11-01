<?php
/**
 * Default template for WordPress Pages, including product pages and case studies.
 *
 * All copy, images, documentation, and translated content are edited in WordPress.
 * Checkout, accounts and license verification are intentionally not coupled
 * to this public-facing template.
 *
 * @package MrSobolle
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="primary" class="site-main content-page">
    <div class="container">
        <?php while ( have_posts() ) : ?>
            <?php
            the_post();
            $parent_id = wp_get_post_parent_id( get_the_ID() );
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class( 'content-page__article' ); ?>>
                <header class="content-page__header">
                    <?php if ( $parent_id && 'publish' === get_post_status( $parent_id ) ) : ?>
                        <a class="page-back-link" href="<?php echo esc_url( get_permalink( $parent_id ) ); ?>">
                            <?php echo esc_html( get_the_title( $parent_id ) ); ?>
                        </a>
                    <?php endif; ?>
                    <h1 class="content-page__title"><?php echo esc_html( get_the_title() ); ?></h1>
                </header>
                <div class="content-page__body entry-content">
                    <?php the_content(); ?>
                    <?php wp_link_pages(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
</main>

<?php get_footer(); ?>