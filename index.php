<?php
/**
 * Main fallback template.
 *
 * This file is required by WordPress and is used as the final fallback
 * when no more specific template is available.
 *
 * @package MrSobolle
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

    <main id="primary" class="site-main">
        <?php if ( have_posts() ) : ?>

            <?php
            while ( have_posts() ) :
                the_post();
                ?>

                <article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
                    <header class="entry-header">
                        <h1 class="entry-title">
                            <?php the_title(); ?>
                        </h1>
                    </header>

                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>
                </article>

            <?php endwhile; ?>

        <?php else : ?>

            <section class="no-results">
                <h1><?php esc_html_e( 'Nothing found', 'mrsobolle' ); ?></h1>
                <p><?php esc_html_e( 'No content is available yet.', 'mrsobolle' ); ?></p>
            </section>

        <?php endif; ?>
    </main>

<?php
get_footer();