<?php
/**
 * Theme class autoloader.
 *
 * @package MrSobolle
 */

namespace MrSobolle;

defined( 'ABSPATH' ) || exit;

/**
 * Simple theme autoloader.
 */
final class Autoloader {

    /**
     * Registers the autoloader.
     */
    public static function register(): void {
        spl_autoload_register( [ self::class, 'autoload' ] );
    }

    /**
     * Loads a class file by class name.
     *
     * Examples:
     * MrSobolle\Theme          -> inc/class-theme.php
     * MrSobolle\Data\Site_Data -> inc/data/class-site-data.php
     *
     * @param string $class Fully qualified class name.
     */
    private static function autoload( string $class ): void {
        $prefix = __NAMESPACE__ . '\\';

        if ( 0 !== strpos( $class, $prefix ) ) {
            return;
        }

        $relative_class = substr( $class, strlen( $prefix ) );
        $relative_class = ltrim( $relative_class, '\\' );

        $parts      = explode( '\\', $relative_class );
        $class_name = array_pop( $parts );

        $path_parts = array_map( [ self::class, 'normalize_path_part' ], $parts );
        $file_name  = 'class-' . self::normalize_path_part( $class_name ) . '.php';

        $file = __DIR__ . '/';

        if ( ! empty( $path_parts ) ) {
            $file .= implode( '/', $path_parts ) . '/';
        }

        $file .= $file_name;

        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }

    /**
     * Converts namespace or class segment to file/path segment.
     *
     * @param string $part Namespace or class segment.
     * @return string
     */
    private static function normalize_path_part( string $part ): string {
        $part = str_replace( '_', '-', $part );
        return strtolower( $part );
    }
}