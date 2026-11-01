<?php
/**
 * Verify GNU MO gettext catalogs for the five-language site.
 * No WordPress runtime required.
 */
declare(strict_types=1);

$theme = dirname( __DIR__, 2 );
$expected = [ 'ru_RU', 'de_DE', 'fr_FR', 'es_ES' ];

$decode = static function ( string $bytes ): array {
    if ( strlen( $bytes ) < 28 ) {
        throw new RuntimeException( 'MO too small' );
    }

    $header = unpack( 'Vmagic/Vrevision/Vcount/Vorig/Vtrans/Vhashsize/Vhashoffset', substr( $bytes, 0, 28 ) );
    if ( $header['magic'] !== 0x950412de || $header['revision'] !== 0 || $header['count'] !== 50 ) {
        throw new RuntimeException( 'Unexpected MO header or entry count' );
    }

    $messages = [];
    for ( $i = 0; $i < $header['count']; $i++ ) {
        $origRecord = unpack( 'Vlength/Voffset', substr( $bytes, $header['orig'] + $i * 8, 8 ) );
        $transRecord = unpack( 'Vlength/Voffset', substr( $bytes, $header['trans'] + $i * 8, 8 ) );
        $source = substr( $bytes, $origRecord['offset'], $origRecord['length'] );
        $target = substr( $bytes, $transRecord['offset'], $transRecord['length'] );
        if ( strlen( $source ) !== $origRecord['length'] || strlen( $target ) !== $transRecord['length'] ) {
            throw new RuntimeException( 'Out-of-bounds catalog string' );
        }
        $messages[ $source ] = $target;
    }

    return $messages;
};

foreach ( $expected as $locale ) {
    $moPath = $theme . '/languages/' . $locale . '.mo';
    $poPath = $theme . '/languages/' . $locale . '.po';
    if ( ! is_file( $moPath ) || ! is_file( $poPath ) ) {
        throw new RuntimeException( "Missing locale catalog: $locale" );
    }

    $messages = $decode( (string) file_get_contents( $moPath ) );
    if ( ! str_contains( $messages[''] ?? '', "Language: $locale" ) ) {
        throw new RuntimeException( "Incorrect language header: $locale" );
    }

    foreach ( [ 'About', 'Work', 'Apps', 'Language', 'View projects',
        'Software Engineer / PHP & WordPress', 'Software engineering from architecture to delivery.' ] as $source ) {
        if ( ! isset( $messages[ $source ] ) || $messages[ $source ] === '' ) {
            throw new RuntimeException( "Missing translation: $locale $source" );
        }
    }

    foreach ( $messages as $source => $translated ) {
        if ( $source !== '' && ! mb_check_encoding( $translated, 'UTF-8' ) ) {
            throw new RuntimeException( "Invalid UTF-8: $locale" );
        }
        preg_match_all( '/%[0-9]+\$s/', $source, $originalPlaceholders );
        preg_match_all( '/%[0-9]+\$s/', $translated, $translatedPlaceholders );
        if ( $originalPlaceholders[0] !== $translatedPlaceholders[0] ) {
            throw new RuntimeException( "Format placeholder mismatch in $locale" );
        }
    }

    echo "$locale: " . ( count( $messages ) - 1 ) . " validated translations\n";
}

echo "TRANSLATION_CATALOG_TESTS_PASSED\n";