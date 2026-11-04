<?php
defined( 'ABSPATH' ) || exit;
get_header(); ?>
<main id="primary" tabindex="-1" class="site-main content-page"><div class="container error-page">
<p class="section-kicker">404</p>
<h1 class="content-page__title"><?php esc_html_e( 'Page not found', 'mrsobolle' ); ?></h1>
<p class="section-text"><?php esc_html_e( 'This address may have changed. Explore the site or try a search.', 'mrsobolle' ); ?></p>
<p><a class="button button--secondary" href="<?php echo esc_url( \MrSobolle\Data\Site_Data::home_url() ); ?>"><?php esc_html_e( 'Home', 'mrsobolle' ); ?></a></p>
<?php get_search_form(); ?>
</div></main>
<?php get_footer(); ?>
