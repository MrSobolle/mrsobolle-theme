<?php
/**
 * Template Name: Studio Contact
 * No server submission or persistence: explicitly labelled browser-only demo.
 */
defined( 'ABSPATH' ) || exit;
get_header(); ?>
<main id="primary" tabindex="-1" class="site-main content-page"><div class="container">
<?php while ( have_posts() ) : the_post(); ?>
<header class="content-page__header"><p class="section-kicker">Sobolle Studio</p><h1 class="content-page__title"><?php the_title(); ?></h1></header>
<div class="contact-layout">
<div class="entry-content"><?php the_content(); ?></div>
<section class="contact-panel" aria-labelledby="contact-demo-heading">
<h2 id="contact-demo-heading"><?php esc_html_e( 'Message preview', 'mrsobolle' ); ?></h2>
<p id="contact-demo-note" class="demo-notice"><?php esc_html_e( 'Demo only. Nothing is sent or saved. Use email for a real inquiry.', 'mrsobolle' ); ?></p>
<form data-contact-demo aria-describedby="contact-demo-note" autocomplete="off" onsubmit="return false">
<label for="contact-name"><?php esc_html_e( 'Name', 'mrsobolle' ); ?></label>
<input id="contact-name" type="text" maxlength="100" required>
<label for="contact-email"><?php esc_html_e( 'Email', 'mrsobolle' ); ?></label>
<input id="contact-email" type="email" maxlength="200" required>
<label for="contact-message"><?php esc_html_e( 'Message', 'mrsobolle' ); ?></label>
<textarea id="contact-message" rows="5" minlength="10" maxlength="2000" required></textarea>
<div class="form-actions"><button type="submit" data-demo-submit disabled class="button button--primary"><?php esc_html_e( 'Check demo message', 'mrsobolle' ); ?></button><button type="reset" class="button"><?php esc_html_e( 'Clear', 'mrsobolle' ); ?></button></div>
<p data-demo-result class="demo-notice" role="status" tabindex="-1" hidden><?php esc_html_e( 'The demo is complete. Your message has NOT been sent. Please use the email link to contact me.', 'mrsobolle' ); ?></p>
<noscript><p><?php esc_html_e( 'This demo requires JavaScript. You can still use the email link.', 'mrsobolle' ); ?></p></noscript>
</form>
</section>
</div>
<?php endwhile; ?>
</div></main>
<?php get_footer(); ?>
