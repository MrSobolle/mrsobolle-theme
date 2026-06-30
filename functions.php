<?php
/**
 * Theme bootstrap file.
 *
 * @package MrSobolle
 */

defined( 'ABSPATH' ) || exit;

$autoloader = __DIR__ . '/inc/class-autoloader.php';

if ( file_exists( $autoloader ) ) {
    require_once $autoloader;
}

if ( class_exists( '\MrSobolle\Autoloader' ) ) {
    \MrSobolle\Autoloader::register();
}

add_action(
    'after_setup_theme',
    static function (): void {
        if ( ! class_exists( '\MrSobolle\Theme' ) ) {
            return;
        }

        $theme = new \MrSobolle\Theme();
        $theme->boot();
    }
);