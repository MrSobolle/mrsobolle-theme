<?php
defined( 'ABSPATH' ) || exit;
get_header(); ?>
<main id="primary" tabindex="-1" class="site-main content-page"><div class="container">
<?php while ( have_posts() ) : the_post(); ?>
<article <?php post_class( 'content-page__article' ); ?>>
<header class="content-page__header">
<p class="section-kicker"><?php esc_html_e( 'Journal', 'mrsobolle' ); ?></p>
<h1 class="content-page__title"><?php the_title(); ?></h1>
<p class="article-meta"><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time> · <?php echo esc_html( \MrSobolle\Data\Site_Data::owner()['name'] ); ?></p>
</header>
<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large', [ 'class' => 'article-cover' ] ); } ?>
<div class="entry-content"><?php the_content(); wp_link_pages(); ?></div>
<?php $journal = \MrSobolle\Data\Site_Data::content_page_url( 'journal' ); if ( $journal ) : ?>
<p class="article-return"><a class="page-back-link" href="<?php echo esc_url( $journal ); ?>"><?php esc_html_e( 'Journal', 'mrsobolle' ); ?></a></p>
<?php endif; ?>
</article>
<?php endwhile; ?>
</div></main>
<?php get_footer(); ?>
