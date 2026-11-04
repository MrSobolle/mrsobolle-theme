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

use MrSobolle\Data\Product_Data;
defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="primary" tabindex="-1" class="site-main content-page">
    <div class="container">
        <?php while ( have_posts() ) : ?>
            <?php
            the_post();
            $parent_id = wp_get_post_parent_id( get_the_ID() );
            $product_key = Product_Data::key( get_the_ID() );
            $product_stage = Product_Data::stage_label( Product_Data::stage( get_the_ID() ) );
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class( 'content-page__article' ); ?>>
                <header class="content-page__header">
                    <?php if ( $parent_id && 'publish' === get_post_status( $parent_id ) ) : ?>
                        <a class="page-back-link" href="<?php echo esc_url( get_permalink( $parent_id ) ); ?>">
                            <?php echo esc_html( get_the_title( $parent_id ) ); ?>
                        </a>
                    <?php endif; ?>
                    <h1 class="content-page__title"><?php echo esc_html( get_the_title() ); ?></h1>
                    <?php if ( '' !== $product_stage ) : ?><span class="product-stage"><?php echo esc_html( $product_stage ); ?></span><?php endif; ?>
                </header>
                <div class="content-page__body entry-content">
                    <?php the_content(); ?>
                    <?php wp_link_pages(); ?>
                </div>
                <?php if ( '' !== $product_key ) : ?>
                    <?php get_template_part( 'template-parts/site/product-resources' ); ?>
                    <?php $related = function_exists( 'sobolle_studio_related_articles' ) ? sobolle_studio_related_articles( $product_key ) : []; ?>
                    <?php if ( ! empty( $related ) ) : ?>
                        <section class="related-articles" aria-labelledby="related-articles-title">
                            <h2 id="related-articles-title"><?php esc_html_e( 'Related articles', 'mrsobolle' ); ?></h2>
                            <div class="journal-grid">
                                <?php foreach ( $related as $article ) : ?>
                                    <article class="journal-card">
                                        <h3><a href="<?php echo esc_url( get_permalink( $article ) ); ?>"><?php echo esc_html( get_the_title( $article ) ); ?></a></h3>
                                        <p><?php echo esc_html( wp_trim_words( get_the_excerpt( $article ), 24 ) ); ?></p>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>
                <?php endif; ?>
            </article>
        <?php endwhile; ?>
    </div>
</main>

<?php get_footer(); ?>