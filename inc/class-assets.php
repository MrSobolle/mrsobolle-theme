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
        $font_style = '/assets/css/studio-fonts.css';
        $studio_style = '/assets/css/studio-cinematic.css';
        wp_enqueue_style(
            'mrsobolle-studio-fonts',
            get_template_directory_uri() . $font_style,
            [ 'mrsobolle-main' ],
            (string) filemtime( get_template_directory() . $font_style )
        );
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
        // Keep only the glass interactions in the clean Home experiment.
        // The retired studio-room, Three.js and GSAP scripts stay in Git
        // history/source for a reversible future artistic decision.
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
