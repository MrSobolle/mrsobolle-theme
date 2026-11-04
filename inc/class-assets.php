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
        $this->enqueue_studio_fonts();
        if ( is_front_page() ) {
            $this->enqueue_home_styles();
        }
        $this->enqueue_shared_header();
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
     * Enqueues locally hosted fonts for the shared Studio header.
     */
    private function enqueue_studio_fonts(): void {
        $font_style = '/assets/css/studio-fonts.css';
        if ( ! file_exists( get_template_directory() . $font_style ) ) {
            return;
        }

        wp_enqueue_style(
            'mrsobolle-studio-fonts',
            get_template_directory_uri() . $font_style,
            [ 'mrsobolle-main' ],
            (string) filemtime( get_template_directory() . $font_style )
        );
    }

    /**
     * Front-page-only styles, including the cinematic Home experiment.
     */
    private function enqueue_home_styles(): void {
        $studio_style = '/assets/css/studio-cinematic.css';
        wp_enqueue_style(
            'mrsobolle-studio-cinematic',
            get_template_directory_uri() . $studio_style,
            [ 'mrsobolle-studio-fonts' ],
            (string) filemtime( get_template_directory() . $studio_style )
        );
        $one_screen_style = '/assets/css/studio-one-screen.css';
        if ( file_exists( get_template_directory() . $one_screen_style ) ) {
            wp_enqueue_style(
                'mrsobolle-studio-one-screen',
                get_template_directory_uri() . $one_screen_style,
                [ 'mrsobolle-studio-cinematic' ],
                (string) filemtime( get_template_directory() . $one_screen_style )
            );
        }
    }

    /**
     * One glass header, typography and pointer response for every page.
     * The former room / Three.js / GSAP experiments remain disabled.
     */
    private function enqueue_shared_header(): void {
        $header_style = '/assets/css/studio-header.css';
        if ( file_exists( get_template_directory() . $header_style ) ) {
            $dependencies = [ 'mrsobolle-studio-fonts' ];
            if ( is_front_page() && wp_style_is( 'mrsobolle-studio-one-screen', 'enqueued' ) ) {
                $dependencies = [ 'mrsobolle-studio-one-screen' ];
            }
            wp_enqueue_style(
                'mrsobolle-studio-header',
                get_template_directory_uri() . $header_style,
                $dependencies,
                (string) filemtime( get_template_directory() . $header_style )
            );
        }

        $glass_script = '/assets/js/studio-glass.js';
        if ( file_exists( get_template_directory() . $glass_script ) ) {
            wp_enqueue_script(
                'mrsobolle-studio-glass',
                get_template_directory_uri() . $glass_script,
                [],
                (string) filemtime( get_template_directory() . $glass_script ),
                true
            );
        }
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
