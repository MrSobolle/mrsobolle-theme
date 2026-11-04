<?php
/**
 * Verify the shared header stylesheet and interactions are enqueued on
 * all templates, rather than on Home only. Dependency-free WP function mocks.
 */
declare(strict_types=1);

define( 'ABSPATH', __DIR__ );
$GLOBALS['assertions'] = 0;
$GLOBALS['mock_front_page'] = false;
$GLOBALS['mock_styles'] = [];
$GLOBALS['mock_scripts'] = [];

function check( bool $condition, string $message ): void {
    $GLOBALS['assertions']++;
    if ( ! $condition ) {
        throw new RuntimeException( $message );
    }
}
function add_action( string $name, callable $callback ): void {}
function is_front_page(): bool { return $GLOBALS['mock_front_page']; }
function get_template_directory(): string { return dirname( __DIR__, 2 ); }
function get_template_directory_uri(): string { return 'https://example.test/wp-content/themes/mrsobolle'; }
function wp_enqueue_style( string $handle, string $src, array $deps = [], ?string $ver = null ): void {
    $GLOBALS['mock_styles'][ $handle ] = [ 'src' => $src, 'deps' => $deps, 'ver' => $ver ];
}
function wp_enqueue_script( string $handle, string $src, array $deps = [], ?string $ver = null, bool $footer = false ): void {
    $GLOBALS['mock_scripts'][ $handle ] = [ 'src' => $src, 'deps' => $deps, 'ver' => $ver, 'footer' => $footer ];
}
function wp_style_is( string $handle, string $status ): bool {
    return 'enqueued' === $status && isset( $GLOBALS['mock_styles'][ $handle ] );
}

require_once dirname( __DIR__, 2 ) . '/inc/class-assets.php';
$assets = new MrSobolle\Assets();
foreach ( [ false, true ] as $home ) {
    $GLOBALS['mock_front_page'] = $home;
    $GLOBALS['mock_styles'] = [];
    $GLOBALS['mock_scripts'] = [];
    $assets->enqueue_frontend_assets();

    $styles = $GLOBALS['mock_styles'];
    $scripts = $GLOBALS['mock_scripts'];
    check( isset( $styles['mrsobolle-main'] ), 'Base CSS missing' );
    check( isset( $styles['mrsobolle-studio-fonts'] ), 'Shared header fonts missing' );
    check( isset( $styles['mrsobolle-studio-header'] ), 'Header CSS missing' );
    check( isset( $scripts['mrsobolle-studio-glass'] ), 'Glass pointer script missing' );
    check( $scripts['mrsobolle-studio-glass']['footer'], 'Glass pointer script should load in footer' );
    check( str_ends_with( $styles['mrsobolle-studio-header']['src'], '/assets/css/studio-header.css' ), 'Wrong global stylesheet' );
    check( $home === isset( $styles['mrsobolle-studio-cinematic'] ), 'Home cinematic CSS must remain Home-only' );
    check( $home === isset( $styles['mrsobolle-studio-one-screen'] ), 'One-screen CSS must remain Home-only' );
    $expected = $home ? 'mrsobolle-studio-one-screen' : 'mrsobolle-studio-fonts';
    check( $styles['mrsobolle-studio-header']['deps'] === [ $expected ], 'Incorrect shared header CSS loading order' );
    check( ! isset( $scripts['mrsobolle-studio-hero'] ), 'Retired 3D scene should stay disabled' );
}

$css = (string) file_get_contents( dirname( __DIR__, 2 ) . '/assets/css/studio-header.css' );
check( str_contains( $css, 'body .site-header[data-studio-glass]' ), 'Global glass component selector absent' );
check( str_contains( $css, 'prefers-reduced-motion' ), 'Reduced-motion support missing' );
check( str_contains( $css, 'focus-visible' ), 'Keyboard focus styling missing' );
echo 'Global glass header assertions: ' . $GLOBALS['assertions'] . " passed\n";