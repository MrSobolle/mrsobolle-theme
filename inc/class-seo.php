<?php
/** Central public metadata; Polylang owns hreflang. */
namespace MrSobolle;
use MrSobolle\Data\Site_Data;
use MrSobolle\Data\Product_Data;
defined( 'ABSPATH' ) || exit;
final class Seo {
    public function register(): void {
        add_filter( 'smartcrawl_onpage_skip_processing', '__return_true' );
        add_filter( 'wds_process_robots', '__return_false' );
        add_action( 'wp', [ $this, 'prepare' ], 99 );
        add_action( 'wp_head', [ $this, 'head' ], 5 );
        add_filter( 'pre_get_document_title', [ $this, 'title' ], 999 );
        add_filter( 'wp_robots', [ $this, 'robots' ], 999 );
        add_action( 'send_headers', [ $this, 'headers' ] );
        add_filter( 'wp_sitemaps_enabled', static fn( $enabled ) => ! self::locked() && $enabled );
    }
    /** Indexing needs explicit approval in config AND WordPress. */
    public static function locked(): bool {
        return ! defined( 'STUDIO_ALLOW_INDEXING' ) || true !== STUDIO_ALLOW_INDEXING || '1' !== (string) get_option( 'blog_public', '0' );
    }
    public function prepare(): void {
        remove_action( 'wp_head', 'rel_canonical' );
        if ( class_exists( '\SmartCrawl\Schema\Printer' ) ) {
            $printer = \SmartCrawl\Schema\Printer::get();
            remove_action( 'wp_head', [ $printer, 'dispatch_schema_injection' ], 50 );
            remove_action( 'smartcrawl_head_after_output', [ $printer, 'dispatch_schema_injection' ] );
        }
    }
    public function headers(): void {
        if ( self::locked() && ! headers_sent() ) { header( 'X-Robots-Tag: noindex, nofollow, noarchive', true ); }
    }
    public function robots( array $robots ): array {
        if ( self::locked() || is_search() || is_404() || is_archive() || self::filtered() ) {
            unset( $robots['index'], $robots['follow'] );
            $robots['noindex'] = true;
            if ( self::locked() ) { $robots['nofollow'] = true; }
        }
        return $robots;
    }
    public static function filtered(): bool {
        return is_page_template( 'page-templates/journal-catalog.php' ) && ( isset( $_GET['topic'] ) || isset( $_GET['journal_page'] ) );
    }
    public static function title(): string {
        if ( is_front_page() ) { return 'Sobolle Studio — ' . __( 'Independent software for practical problems.', 'mrsobolle' ); }
        if ( is_404() ) { $title = __( 'Page not found', 'mrsobolle' ); }
        elseif ( is_search() ) { $title = __( 'Search results', 'mrsobolle' ) . ': ' . get_search_query( false ); }
        elseif ( is_singular() ) {
            $title = get_the_title( get_queried_object_id() );
            if ( is_page_template( 'page-templates/product-changelog.php' ) ) {
                $parent = wp_get_post_parent_id( get_queried_object_id() );
                if ( $parent ) { $title = get_the_title( $parent ) . ' — ' . $title; }
            }
        }
        else { $title = wp_strip_all_tags( get_the_archive_title() ); }
        $page = max( 1, (int) get_query_var( 'paged' ) );
        return wp_strip_all_tags( $title ) . ( $page > 1 ? ' — ' . $page : '' ) . ' | Sobolle Studio';
    }
    public static function canonical(): string {
        if ( is_404() || is_search() ) { return ''; }
        if ( is_front_page() ) { return Site_Data::home_url(); }
        if ( is_singular() ) { return function_exists( 'wp_get_canonical_url' ) ? (string) wp_get_canonical_url( get_queried_object_id() ) : (string) get_permalink( get_queried_object_id() ); }
        if ( is_category() || is_tag() || is_tax() ) {
            $link = get_term_link( get_queried_object() );
            return is_wp_error( $link ) ? '' : (string) $link;
        }
        return '';
    }
    public static function description(): string {
        if ( is_front_page() ) { return __( 'Software products, web solutions and engineering.', 'mrsobolle' ); }
        $post = get_queried_object();
        if ( $post instanceof \WP_Post && ! post_password_required( $post ) ) {
            $text = $post->post_excerpt ?: $post->post_content;
            if ( in_array( $post->post_name, [ 'docs', 'changelog' ], true ) ) {
                $parent = wp_get_post_parent_id( $post );
                $text = ( $parent ? get_the_title( $parent ) . ': ' : '' ) . $text;
            }
            $text = preg_replace( '~</(?:p|div|h[1-6]|li|section)>~i', ' ', $text );
            $text = html_entity_decode( wp_strip_all_tags( strip_shortcodes( $text ) ), ENT_QUOTES, 'UTF-8' );
            $text = trim( preg_replace( '/\s+/u', ' ', $text ) );
            if ( '' !== $text ) { return mb_strimwidth( $text, 0, 165, '…', 'UTF-8' ); }
        }
        return __( 'Independent software for practical problems.', 'mrsobolle' );
    }
    /** Used by both visible navigation and structured data. */
    public static function breadcrumbs(): array {
        $items = [ [ 'name' => __( 'Home', 'mrsobolle' ), 'url' => Site_Data::home_url() ] ];
        $post = get_queried_object();
        if ( $post instanceof \WP_Post && is_singular() ) {
            if ( 'post' === $post->post_type ) {
                $journal = Site_Data::content_page_url( 'journal' );
                if ( $journal ) { $items[] = [ 'name' => __( 'Journal', 'mrsobolle' ), 'url' => $journal ]; }
            }
            foreach ( array_reverse( get_post_ancestors( $post ) ) as $id ) {
                if ( 'publish' === get_post_status( $id ) ) { $items[] = [ 'name' => get_the_title( $id ), 'url' => get_permalink( $id ) ]; }
            }
            $items[] = [ 'name' => get_the_title( $post ), 'url' => get_permalink( $post ) ];
        }
        return $items;
    }
    public static function graph(): array {
        $url = self::canonical();
        if ( ! $url || ! is_singular() || post_password_required() ) { return []; }
        $root = trailingslashit( (string) get_option( 'home' ) );
        $lang = str_replace( '_', '-', get_locale() );
        $person = $root . '#person'; $studio = $root . '#studio'; $website = $root . '#website';
        $post = get_queried_object();
        $template = get_page_template_slug( $post->ID );
        $type = in_array( $template, [ 'page-templates/apps-catalog.php', 'page-templates/work-catalog.php', 'page-templates/journal-catalog.php' ], true ) ? 'CollectionPage' : 'WebPage';
        $role = get_post_meta( $post->ID, '_studio_page_role', true );
        if ( 'about' === $role ) { $type = 'ProfilePage'; }
        if ( 'contact' === $role || 'page-templates/contact.php' === $template ) { $type = 'ContactPage'; }
        $page = [ '@type' => $type, '@id' => $url . '#webpage', 'url' => $url, 'name' => is_front_page() ? 'Sobolle Studio' : get_the_title( $post ), 'description' => self::description(), 'inLanguage' => $lang, 'isPartOf' => [ '@id' => $website ] ];
        if ( 'ProfilePage' === $type ) { $page['mainEntity'] = [ '@id' => $person ]; }
        $graph = [
            [ '@type' => 'Person', '@id' => $person, 'name' => Site_Data::owner()['name'], 'url' => Site_Data::content_page_url( 'about' ) ?: $root, 'sameAs' => [ 'https://github.com/MrSobolle' ] ],
            [ '@type' => 'Organization', '@id' => $studio, 'name' => 'Sobolle Studio', 'url' => $root, 'founder' => [ '@id' => $person ] ],
            [ '@type' => 'WebSite', '@id' => $website, 'name' => 'Sobolle Studio', 'url' => $root, 'publisher' => [ '@id' => $studio ], 'inLanguage' => [ 'en', 'ru', 'de', 'fr', 'es' ] ],
        ];
        if ( ! is_front_page() ) {
            $crumbs = [];
            foreach ( self::breadcrumbs() as $i => $crumb ) { $crumbs[] = [ '@type' => 'ListItem', 'position' => $i + 1, 'name' => $crumb['name'], 'item' => $crumb['url'] ]; }
            $graph[] = [ '@type' => 'BreadcrumbList', '@id' => $url . '#breadcrumbs', 'itemListElement' => $crumbs ];
            $page['breadcrumb'] = [ '@id' => $url . '#breadcrumbs' ];
        }
        $key = Product_Data::key( (int) $post->ID );
        if ( $key ) {
            $app = [ '@type' => 'SoftwareApplication', '@id' => $url . '#software', 'name' => get_the_title( $post ), 'url' => $url, 'description' => self::description(), 'inLanguage' => $lang, 'author' => [ '@id' => $person ], 'applicationCategory' => 'UtilitiesApplication', 'creativeWorkStatus' => Product_Data::stage_label( Product_Data::stage( (int) $post->ID ) ) ];
            if ( 'widecrop' === $key ) { $app['softwareRequirements'] = 'Google Chrome'; $app['applicationCategory'] = 'BrowserApplication'; }
            elseif ( 'local-powershell' === $key ) { $app['operatingSystem'] = 'Windows'; }
            // A concept is not a working application. Do not invent capabilities.
            if ( 'ai-pc-assistant' === $key ) {
                $app['@type'] = 'CreativeWork';
                $app['@id'] = $url . '#concept';
                unset( $app['applicationCategory'] );
            }
            $graph[] = $app;
            $page['mainEntity'] = [ '@id' => $app['@id'] ];
        }
        if ( is_singular( 'post' ) ) {
            $article = [ '@type' => 'BlogPosting', '@id' => $url . '#article', 'headline' => get_the_title( $post ), 'description' => self::description(), 'mainEntityOfPage' => [ '@id' => $url . '#webpage' ], 'datePublished' => get_the_date( DATE_W3C, $post ), 'dateModified' => get_the_modified_date( DATE_W3C, $post ), 'inLanguage' => $lang, 'author' => [ '@id' => $person ], 'publisher' => [ '@id' => $studio ] ];
            $image = get_the_post_thumbnail_url( $post, 'full' );
            if ( $image ) { $article['image'] = $image; }
            $graph[] = $article;
            $page['mainEntity'] = [ '@id' => $url . '#article' ];
        }
        if ( in_array( $template, [ 'page-templates/apps-catalog.php', 'page-templates/work-catalog.php' ], true ) ) {
            $items = [];
            foreach ( \MrSobolle\Data\Directory_Data::children( $post->ID ) as $i => $child ) { $items[] = [ '@type' => 'ListItem', 'position' => $i + 1, 'url' => get_permalink( $child ), 'name' => get_the_title( $child ) ]; }
            if ( $items ) { $graph[] = [ '@type' => 'ItemList', '@id' => $url . '#list', 'itemListElement' => $items ]; $page['mainEntity'] = [ '@id' => $url . '#list' ]; }
        }
        $graph[] = $page;
        return $graph;
    }
    public function head(): void {
        $url = self::canonical(); $description = self::description();
        echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
        if ( $url ) { echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n"; }
        if ( ! is_404() && ! is_search() ) {
            $tags = [ 'og:type' => is_singular( 'post' ) ? 'article' : 'website', 'og:site_name' => 'Sobolle Studio', 'og:title' => self::title(), 'og:description' => $description, 'og:locale' => get_locale(), 'twitter:card' => 'summary_large_image', 'twitter:title' => self::title(), 'twitter:description' => $description ];
            if ( $url ) { $tags['og:url'] = $url; }
            $image = is_singular() ? get_the_post_thumbnail_url( get_queried_object_id(), 'full' ) : false;
            $tags['og:image'] = $image ?: get_template_directory_uri() . '/assets/images/social-card.png';
            $tags['og:image:alt'] = 'Sobolle Studio';
            $tags['twitter:image'] = $tags['og:image'];
            foreach ( $tags as $name => $value ) { echo '<meta ' . ( str_starts_with( $name, 'og:' ) ? 'property' : 'name' ) . '="' . esc_attr( $name ) . '" content="' . esc_attr( $value ) . '">' . "\n"; }
        }
        if ( ! has_site_icon() ) { echo '<link rel="icon" href="' . esc_url( get_template_directory_uri() . '/assets/images/favicon.svg' ) . '" type="image/svg+xml">' . "\n"; }
        $graph = self::graph();
        if ( $graph ) { echo '<script type="application/ld+json">' . wp_json_encode( [ '@context' => 'https://schema.org', '@graph' => $graph ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "</script>\n"; }
    }
}
