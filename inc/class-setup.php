<?php
/**
 * Theme setup.
 *
 * @package MrSobolle
 */

namespace MrSobolle;

defined( 'ABSPATH' ) || exit;

/**
 * Registers basic WordPress theme features.
 */
final class Setup {

    /**
     * Registers theme setup features.
     */
    public function register(): void {
        $this->load_textdomain();
        $this->add_theme_supports();
        $this->register_menus();
        $this->register_image_sizes();
    }

    /**
     * Loads theme translations.
     */
    private function load_textdomain(): void {
        load_theme_textdomain(
            'mrsobolle',
            get_template_directory() . '/languages'
        );
    }

    /**
     * Adds WordPress theme supports.
     */
    private function add_theme_supports(): void {
        add_theme_support( 'title-tag' );
        add_theme_support( 'post-thumbnails' );
        add_theme_support( 'custom-logo' );
        add_theme_support( 'automatic-feed-links' );
        add_theme_support( 'html5', $this->get_html5_features() );
        add_theme_support( 'align-wide' );
        add_theme_support( 'responsive-embeds' );
        add_theme_support( 'editor-styles' );

        add_editor_style( 'assets/css/editor.css' );
    }

    /**
     * Registers navigation menu locations.
     */
    private function register_menus(): void {
        register_nav_menus(
            [
                'primary' => esc_html__( 'Primary Menu', 'mrsobolle' ),
                'footer'  => esc_html__( 'Footer Menu', 'mrsobolle' ),
                'social'  => esc_html__( 'Social Links Menu', 'mrsobolle' ),
            ]
        );
    }

    /**
     * Registers custom image sizes.
     */
    private function register_image_sizes(): void {
        add_image_size( 'mrsobolle-card', 640, 420, true );
        add_image_size( 'mrsobolle-hero', 1600, 900, true );
    }

    /**
     * Returns supported HTML5 features.
     *
     * @return array<int, string>
     */
    private function get_html5_features(): array {
        return [
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
            'navigation-widgets',
        ];
    }
}