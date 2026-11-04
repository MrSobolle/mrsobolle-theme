<?php
/**
 * Dependency-free isolated tests for WordPress Page catalogs.
 *
 * These tests exercise PR code with mocked WP functions. They do not replace
 * a real WordPress staging environment and must not be described as full E2E.
 */

declare(strict_types=1);

define( 'ABSPATH', __DIR__ );

class WP_Post {
    public function __construct(
        public int $ID,
        public string $post_title,
        public string $post_content = '',
        public string $post_excerpt = '',
        public int $post_parent = 0
    ) {}
}

$GLOBALS['last_query'] = null;
$GLOBALS['mock_children'] = [];
$GLOBALS['mock_directory'] = [];
$GLOBALS['mock_loop'] = [];
$GLOBALS['loop_position'] = 0;
$GLOBALS['active_post'] = null;
$GLOBALS['assertions'] = 0;

function check( bool $condition, string $message ): void {
    $GLOBALS['assertions']++;
    if ( ! $condition ) {
        throw new RuntimeException( $message );
    }
}

function get_posts( array $query ): array {
    $GLOBALS['last_query'] = $query;
    if ( isset( $query['post_parent'] ) ) {
        return $GLOBALS['mock_children'];
    }
    return $GLOBALS['mock_directory'];
}
function home_url( string $path = '/' ): string { return 'https://example.test' . $path; }
function __( string $value, string $domain = '' ): string { return $value; }
function esc_html( string $value ): string { return htmlspecialchars( $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' ); }
function esc_attr( string $value ): string { return esc_html( $value ); }
function esc_url( string $value ): string { return esc_html( $value ); }
function esc_html_e( string $value, string $domain = '' ): void { echo esc_html( $value ); }
function get_permalink( WP_Post|int $page ): string { return 'https://example.test/pages/' . ( is_int( $page ) ? $page : $page->ID ) . '/'; }
function have_posts(): bool { return $GLOBALS['loop_position'] < count( $GLOBALS['mock_loop'] ); }
function the_post(): void { $GLOBALS['active_post'] = $GLOBALS['mock_loop'][ $GLOBALS['loop_position']++ ]; }
function get_the_ID(): int { return $GLOBALS['active_post']->ID; }
function get_the_title( WP_Post|int|null $post = null ): string {
    if ( $post instanceof WP_Post ) { return $post->post_title; }
    if ( is_int( $post ) ) { return 'Parent'; }
    return $GLOBALS['active_post']->post_title;
}
function get_the_content(): string { return $GLOBALS['active_post']->post_content; }
function the_content(): void { echo $GLOBALS['active_post']->post_content; }
function get_the_excerpt( WP_Post $post ): string { return $post->post_excerpt; }
function wp_trim_words( string $text, int $num_words = 55 ): string {
    $words = preg_split( '/\s+/', trim( $text ) ) ?: [];
    return implode( ' ', array_slice( $words, 0, $num_words ) );
}
function has_post_thumbnail( WP_Post $post ): bool { return false; }

require_once dirname( __DIR__, 2 ) . '/inc/data/class-directory-data.php';
require_once dirname( __DIR__, 2 ) . '/inc/data/class-site-data.php';
require_once dirname( __DIR__, 2 ) . '/inc/data/class-product-data.php';

use MrSobolle\Data\Directory_Data;
use MrSobolle\Data\Site_Data;

$child_a = new WP_Post( 12, 'Video tools', '', 'Extensions for large screens' );
$child_b = new WP_Post( 13, 'Unsafe <script>alert(1)</script>', '', 'Escaping <script> matters' );
$GLOBALS['mock_children'] = [ $child_a, $child_b ];

check( Directory_Data::children( 0 ) === [], 'Invalid parent should return an empty list' );
check( $GLOBALS['last_query'] === null, 'Invalid parent must not query DB' );
$found = Directory_Data::children( 7 );
check( count( $found ) === 2, 'Expected two child pages' );
check( $GLOBALS['last_query']['post_parent'] === 7, 'Must query immediate children only' );
check( $GLOBALS['last_query']['post_type'] === 'page', 'Must query pages' );
check( $GLOBALS['last_query']['post_status'] === 'publish', 'Must exclude drafts' );
check( $GLOBALS['last_query']['suppress_filters'] === false, 'Must allow multilingual filters' );
check( $GLOBALS['last_query']['posts_per_page'] === -1, 'Must include full directory' );

check( Directory_Data::directory_page( '../../secret.php' ) === null, 'Unknown templates must be rejected' );
check( Directory_Data::directory_url( 'unknown' ) === '', 'Unknown catalog type must be rejected' );
$GLOBALS['mock_directory'] = [ new WP_Post( 3, 'Apps', '', '' ) ];
check( Directory_Data::directory_url( 'apps' ) === 'https://example.test/pages/3/', 'Catalog page should resolve by template' );
check( $GLOBALS['last_query']['meta_value'] === 'page-templates/apps-catalog.php', 'Should look up template identity instead of page slug' );
check( $GLOBALS['last_query']['suppress_filters'] === false, 'Directory lookup must respect language context' );
$GLOBALS['mock_directory'] = [];
check( Directory_Data::directory_url( 'work' ) === '', 'Missing catalog page should not produce broken link' );
check( Site_Data::home_url() === 'https://example.test/', 'Fallback home URL should work without Polylang' );

$GLOBALS['mock_loop'] = [ new WP_Post( 7, 'Apps', '<p>Intro from the editor</p>' ) ];
$GLOBALS['loop_position'] = 0;
$GLOBALS['active_post'] = null;
$args = [ 'kind' => 'apps' ];
ob_start();
include dirname( __DIR__, 2 ) . '/template-parts/pages/catalog.php';
$html = ob_get_clean();
check( str_contains( $html, 'Intro from the editor' ), 'Catalog introduction must render editable WP content' );
check( str_contains( $html, 'Video tools' ), 'Catalog must render published children' );
check( str_contains( $html, 'Unsafe &lt;script&gt;' ), 'Child titles must be escaped' );
check( ! str_contains( $html, '<script>alert(1)</script>' ), 'Catalog must not render raw script markup from titles' );
check( str_contains( $html, 'https://example.test/pages/12/' ), 'Catalog must have navigable details links' );
check( str_contains( $html, 'Escaping &lt;script&gt;' ), 'Excerpts must be escaped' );

$GLOBALS['mock_children'] = [];
$GLOBALS['mock_loop'] = [ new WP_Post( 9, 'Work', '' ) ];
$GLOBALS['loop_position'] = 0;
$GLOBALS['active_post'] = null;
$args = [ 'kind' => 'work' ];
ob_start();
include dirname( __DIR__, 2 ) . '/template-parts/pages/catalog.php';
$empty_html = ob_get_clean();
check( str_contains( $empty_html, 'Case studies will appear here' ), 'Empty work catalog needs a clear empty state' );
check( substr_count( $empty_html, '<h1' ) === 1, 'Catalog should have exactly one H1' );

function get_header(): void {}
function get_footer(): void {}
function wp_get_post_parent_id( int $id ): int { return $GLOBALS['active_post']->post_parent; }
function get_post_status( int $id ): string { return 'publish'; }
function the_ID(): void { echo get_the_ID(); }
function post_class( string $classes = '' ): void { echo 'class="' . esc_attr( $classes ) . '"'; }
function wp_link_pages(): void {}

$GLOBALS['mock_loop'] = [ new WP_Post( 15, 'Product <b>Test</b>', '<p>Editable product description</p>', '', 7 ) ];
$GLOBALS['loop_position'] = 0;
$GLOBALS['active_post'] = null;
ob_start();
include dirname( __DIR__, 2 ) . '/page.php';
$detail_html = ob_get_clean();
check( str_contains( $detail_html, 'Product &lt;b&gt;Test&lt;/b&gt;' ), 'Detail title must be escaped' );
check( str_contains( $detail_html, 'Editable product description' ), 'Detail content must come from WP editor' );
check( str_contains( $detail_html, '/pages/7/' ), 'Detail page must link back to its parent catalog' );
check( substr_count( $detail_html, '<h1' ) === 1, 'Detail page must have exactly one H1' );
echo 'Catalog and template assertions: ' . $GLOBALS['assertions'] . " passed\n";