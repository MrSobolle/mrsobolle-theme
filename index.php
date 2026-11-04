<?php
/** Accessible archive and search fallback: one H1, linked entries, pagination. */
defined( 'ABSPATH' ) || exit;
get_header(); ?>
<main id="primary" tabindex="-1" class="site-main catalog-page"><div class="container">
<header class="catalog-page__header">
<h1 class="catalog-page__title"><?php echo esc_html( is_search() ? __( 'Search results', 'mrsobolle' ) : wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
<?php if ( is_search() ) { get_search_form(); } else { the_archive_description( '<div class="entry-content">', '</div>' ); } ?>
</header>
<?php if ( have_posts() ) : ?>
<div class="journal-grid">
<?php while ( have_posts() ) : the_post(); ?>
<article <?php post_class( 'journal-card' ); ?>>
<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 32 ) ); ?></p>
<a class="journal-card__more" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read article', 'mrsobolle' ); ?> &rarr;</a>
</article>
<?php endwhile; ?>
</div>
<?php the_posts_pagination(); ?>
<?php else : ?>
<p class="catalog-page__empty"><?php esc_html_e( 'No matching content. Try a different search.', 'mrsobolle' ); ?></p>
<?php endif; ?>
</div></main>
<?php get_footer(); ?>
