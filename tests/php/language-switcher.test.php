<?php
/**
 * Lightweight i18n switcher rendering checks, without WordPress or Polylang.
 */

declare(strict_types=1);

define( 'ABSPATH', __DIR__ );

$GLOBALS['mock_languages'] = [];
$GLOBALS['checks'] = 0;

function check( bool $result, string $message ): void {
    $GLOBALS['checks']++;
    if ( ! $result ) {
        throw new RuntimeException( $message );
    }
}
function esc_attr_e( string $value, string $domain = '' ): void { echo htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( string $value ): string { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function esc_html( string $value ): string { return esc_attr( $value ); }
function esc_url( string $value ): string { return esc_attr( $value ); }
function pll_the_languages( array $args ): array {
    check( ! empty( $args['raw'] ), 'Use raw language records from Polylang' );
    return $GLOBALS['mock_languages'];
}
function pll_home_url(): string { return 'https://example.test/en/'; }
function home_url( string $path = '/' ): string { return 'https://example.test' . $path; }

require_once dirname( __DIR__, 2 ) . '/inc/data/class-site-data.php';

check( \MrSobolle\Data\Site_Data::home_url() === 'https://example.test/en/', 'Home link must honor current language' );

$part = dirname( __DIR__, 2 ) . '/template-parts/site/language-switcher.php';

$GLOBALS['mock_languages'] = [
    'en' => [
        'slug' => 'en', 'name' => 'English', 'url' => 'https://example.test/en/',
        'is_current' => true, 'has_translations' => true,
    ],
    'ru' => [
        'slug' => 'ru', 'name' => 'Русский', 'url' => 'https://example.test/ru/',
        'is_current' => false, 'has_translations' => true,
    ],
    'de' => [
        'slug' => 'de', 'name' => 'Deutsch', 'url' => 'https://example.test/de/',
        'is_current' => false, 'has_translations' => false,
    ],
];
ob_start();
include $part;
$html = ob_get_clean();
check( str_contains( $html, 'English' ), 'Current language should be shown' );
check( str_contains( $html, 'Русский' ), 'Available translated page should be shown' );
check( ! str_contains( $html, 'Deutsch' ), 'Missing translation should not link to unrelated page' );
check( str_contains( $html, 'hreflang="ru"' ), 'Language links should carry hreflang' );
check( substr_count( $html, 'aria-current="page"' ) === 1, 'Exactly one language should be current' );
check( substr_count( $html, 'language-switcher__item' ) === 2, 'Only translated languages should render' );

$GLOBALS['mock_languages'] = [
    'en' => [
        'slug' => 'en', 'name' => 'English', 'url' => 'https://example.test/en/',
        'current_lang' => true, 'no_translation' => false,
    ],
    'fr' => [
        'slug' => 'fr', 'name' => 'Français', 'url' => 'https://example.test/fr/',
        'current_lang' => false, 'no_translation' => false,
    ],
];
ob_start();
include $part;
$legacy = ob_get_clean();
check( str_contains( $legacy, 'Français' ), 'Legacy Polylang records should remain supported' );
check( substr_count( $legacy, 'aria-current="page"' ) === 1, 'Legacy Polylang current language should render correctly' );

$GLOBALS['mock_languages'] = [
    'en' => [
        'slug' => 'en', 'name' => 'English', 'url' => 'https://example.test/en/',
        'is_current' => true, 'has_translations' => true,
    ],
];
ob_start();
include $part;
$single = ob_get_clean();
check( '' === trim( $single ), 'Single language should not show an unusable switcher' );

echo 'Language switcher assertions: ' . $GLOBALS['checks'] . " passed\n";