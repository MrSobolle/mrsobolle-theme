<?php
/**
 * Shared, editable catalog template for work and software products.
 *
 * @package MrSobolle
 */

use MrSobolle\Data\Directory_Data;
use MrSobolle\Data\Site_Data;

defined( 'ABSPATH' ) || exit;

$kind   = isset( $args['kind'] ) && 'work' === $args['kind'] ? 'work' : 'apps';
$label  = 'work' === $kind ? __( 'Selected work', 'mrsobolle' ) : __( 'Software & tools', 'mrsobolle' );
$empty  = 'work' === $kind
    ? __( 'Case studies will appear here as they become available.', 'mrsobolle' )
    : __( 'Product pages will appear here as they become available.', 'mrsobolle' );
?>

<main id="primary" class="site-main catalog-page catalog-page--<?php echo esc_attr( $kind ); ?>">
    <?php if ( have_posts() ) : ?>
        <?php while ( have_posts() ) : ?>
            <?php
            the_post();
            $children = Directory_Data::children( get_the_ID() );
            ?>

            <div class="container">
                <header class="catalog-page__header">
                    <a class="page-back-link" href="<?php echo esc_url( Site_Data::home_url() ); ?>">
                        <?php esc_html_e( 'Home', 'mrsobolle' ); ?>
                    </a>
                    <p class="section-kicker"><?php echo esc_html( $label ); ?></p>
                    <h1 class="catalog-page__title"><?php echo esc_html( get_the_title() ); ?></h1>
                    <?php if ( '' !== trim( get_the_content() ) ) : ?>
                        <div class="catalog-page__intro entry-content">
                            <?php the_content(); ?>
                        </div>
                    <?php endif; ?>
                </header>

                <?php if ( $children ) : ?>
                    <div class="catalog-grid" aria-label="<?php echo esc_attr( $label ); ?>">
                        <?php foreach ( $children as $child ) : ?>
                            <article class="catalog-card">
                                <?php if ( has_post_thumbnail( $child ) ) : ?>
                                    <a class="catalog-card__media" href="<?php echo esc_url( get_permalink( $child ) ); ?>" tabindex="-1" aria-hidden="true">
                                        <?php echo get_the_post_thumbnail( $child, 'mrsobolle-card', [ 'class' => 'catalog-card__image', 'loading' => 'lazy' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                    </a>
                                <?php endif; ?>
                                <div class="catalog-card__body">
                                    <h2 class="catalog-card__title">
                                        <a href="<?php echo esc_url( get_permalink( $child ) ); ?>">
                                            <?php echo esc_html( get_the_title( $child ) ); ?>
                                        </a>
                                    </h2>
                                    <?php $summary = trim( (string) get_the_excerpt( $child ) ); ?>
                                    <?php if ( '' !== $summary ) : ?>
                                        <p class="catalog-card__summary"><?php echo esc_html( wp_trim_words( $summary, 36 ) ); ?></p>
                                    <?php endif; ?>
                                    <a class="catalog-card__more" href="<?php echo esc_url( get_permalink( $child ) ); ?>">
                                        <?php esc_html_e( 'Explore', 'mrsobolle' ); ?>
                                        <span aria-hidden="true">&rarr;</span>
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p class="catalog-page__empty"><?php echo esc_html( $empty ); ?></p>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</main>