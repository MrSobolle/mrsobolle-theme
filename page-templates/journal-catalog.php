<?php
/**
 * Template Name: Studio Journal
 * Template Post Type: page
 * WordPress Posts, translated native categories and tags.
 * @package MrSobolle
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="primary" class="site-main journal-page">
    <div class="container">
        <?php while ( have_posts() ) : the_post(); ?>
            <?php
            $journal_id = get_the_ID();
            $language = function_exists( 'pll_current_language' ) ? (string) pll_current_language( 'slug' ) : 'en';
            $topics = [];
            $canonical_topics = get_option( 'studio_journal_topics', [] );
            foreach ( [ 'engineering', 'products', 'creative-lab' ] as $slug ) {
                if ( ! isset( $canonical_topics[ $slug ] ) ) { continue; }
                $term_id = (int) $canonical_topics[ $slug ];
                if ( 'en' !== $language && function_exists( 'pll_get_term' ) ) {
                    $translated_id = pll_get_term( $term_id, $language );
                    if ( ! $translated_id ) { continue; }
                    $term_id = (int) $translated_id;
                }
                $term = get_term( $term_id, 'category' );
                if ( $term instanceof WP_Term ) { $topics[ $slug ] = $term; }
            }
            $selected = isset( $_GET['topic'] ) ? sanitize_key( wp_unslash( $_GET['topic'] ) ) : '';
            if ( ! array_key_exists( $selected, $topics ) ) { $selected = ''; }
            $base_url = get_permalink( $journal_id );
            $category_ids = $selected ? [ (int) $topics[ $selected ]->term_id ] : array_map( static fn( $term ) => (int) $term->term_id, array_values( $topics ) );
            $page = isset( $_GET['journal_page'] ) ? max( 1, absint( $_GET['journal_page'] ) ) : 1;
            ?>
            <header class="journal-page__header">
                <p class="section-kicker">Sobolle Studio</p>
                <h1 class="catalog-page__title"><?php echo esc_html( get_the_title() ); ?></h1>
                <div class="entry-content"><?php the_content(); ?></div>
            </header>
            <?php if ( $topics ) : ?>
                <nav class="journal-topics" aria-label="<?php esc_attr_e( 'Journal topics', 'mrsobolle' ); ?>">
                    <a class="journal-topics__link<?php echo '' === $selected ? ' is-active' : ''; ?>" href="<?php echo esc_url( $base_url ); ?>"<?php echo '' === $selected ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'All topics', 'mrsobolle' ); ?></a>
                    <?php foreach ( $topics as $slug => $term ) : ?>
                        <a class="journal-topics__link<?php echo $slug === $selected ? ' is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'topic', $slug, $base_url ) ); ?>"<?php echo $slug === $selected ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $term->name ); ?></a>
                    <?php endforeach; ?>
                </nav>
                <?php
                $journal_query = new WP_Query( [
                    'post_type' => 'post',
                    'post_status' => 'publish',
                    'posts_per_page' => 9,
                    'paged' => $page,
                    'ignore_sticky_posts' => true,
                    'category__in' => $category_ids,
                    'lang' => $language,
                ] );
                ?>
                <?php if ( $journal_query->have_posts() ) : ?>
                    <div class="journal-grid">
                        <?php while ( $journal_query->have_posts() ) : $journal_query->the_post(); ?>
                            <article <?php post_class( 'journal-card' ); ?>>
                                <p class="journal-card__date"><time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p>
                                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                                <p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
                                <a class="journal-card__more" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read article', 'mrsobolle' ); ?> &rarr;</a>
                            </article>
                        <?php endwhile; ?>
                    </div>
                    <?php
                    $pagination = [ 'base' => add_query_arg( 'journal_page', '%#%', $base_url ), 'format' => '', 'current' => $page, 'total' => $journal_query->max_num_pages ];
                    if ( $selected ) { $pagination['add_args'] = [ 'topic' => $selected ]; }
                    echo '<nav class="journal-pagination" aria-label="' . esc_attr__( 'Journal pages', 'mrsobolle' ) . '">' . wp_kses_post( paginate_links( $pagination ) ) . '</nav>';
                    ?>
                <?php else : ?>
                    <p class="catalog-page__empty"><?php esc_html_e( 'No articles yet.', 'mrsobolle' ); ?></p>
                <?php endif; ?>
                <?php wp_reset_postdata(); ?>
            <?php endif; ?>
        <?php endwhile; ?>
    </div>
</main>
<?php get_footer(); ?>