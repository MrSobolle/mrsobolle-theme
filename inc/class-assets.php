<?php
/**
 * Theme assets.
 *
 * @package MrSobolle
 */

namespace MrSobolle;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and enqueues theme styles and scripts.
 */
final class Assets {

    /**
     * Theme asset handle prefix.
     */
    private const HANDLE_PREFIX = 'mrsobolle';

    /**
     * Registers WordPress hooks.
     */
    public function register(): void {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ] );
    }

    /**
     * Enqueues frontend CSS and JavaScript.
     */
    public function enqueue_frontend_assets(): void {
        $this->enqueue_main_styles();
        if ( is_front_page() ) {
            $this->enqueue_studio_scripts();
        }
        $this->enqueue_main_scripts();
    }

    /**
     * Enqueues main frontend stylesheet.
     */
    private function enqueue_main_styles(): void {
        $relative_path = '/assets/css/main.css';
        $file_path     = get_template_directory() . $relative_path;
        $file_uri      = get_template_directory_uri() . $relative_path;

        if ( ! file_exists( $file_path ) ) {
            return;
        }

        wp_enqueue_style(
            self::HANDLE_PREFIX . '-main',
            $file_uri,
            [],
            (string) filemtime( $file_path )
        );
    }

    /**
     * Enqueues main frontend script.
     */
    private function enqueue_studio_scripts(): void {
        $gsap = '/assets/vendor/gsap.min.js';
        $hero = '/assets/js/studio-hero.js';
        if ( ! file_exists( get_template_directory() . $gsap ) || ! file_exists( get_template_directory() . $hero ) ) {
            return;
        }
        wp_enqueue_script( 'mrsobolle-gsap', get_template_directory_uri() . $gsap, [], '3.15.0', true );
        wp_enqueue_script( 'mrsobolle-studio-hero', get_template_directory_uri() . $hero, [ 'mrsobolle-gsap' ], (string) filemtime( get_template_directory() . $hero ), true );
    }

    private function enqueue_main_scripts(): void {
        $relative_path = '/assets/js/main.js';
        $file_path     = get_template_directory() . $relative_path;
        $file_uri      = get_template_directory_uri() . $relative_path;

        if ( ! file_exists( $file_path ) ) {
            return;
        }

        wp_enqueue_script(
            self::HANDLE_PREFIX . '-main',
            $file_uri,
            [ 'jquery' ],
            (string) filemtime( $file_path ),
            true
        );
    }
}
