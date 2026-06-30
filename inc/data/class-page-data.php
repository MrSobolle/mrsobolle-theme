<?php
/**
 * Page data provider.
 *
 * @package MrSobolle
 */

namespace MrSobolle\Data;

defined( 'ABSPATH' ) || exit;

/**
 * Provides prepared data for theme pages.
 */
final class Page_Data {

    /**
     * Returns front page data.
     *
     * @return array<string, mixed>
     */
    public static function front_page(): array {
        return [
            'hero'     => self::get_hero_data(),
            'about'    => self::get_about_data(),
            'projects' => self::get_projects_data(),
            'contact'  => self::get_contact_data(),
        ];
    }

    /**
     * Returns hero section data.
     *
     * @return array<string, mixed>
     */
    private static function get_hero_data(): array {
        $owner = Site_Data::owner();

        return [
            'eyebrow' => $owner['role'],
            'title'   => $owner['name'],
            'lead'    => __( 'I build clean, fast and maintainable WordPress solutions with custom architecture, data processing, SEO logic and practical business focus.', 'mrsobolle' ),
            'actions' => [
                [
                    'label' => __( 'View projects', 'mrsobolle' ),
                    'url'   => '#projects',
                    'style' => 'primary',
                ],
                [
                    'label' => __( 'Contact me', 'mrsobolle' ),
                    'url'   => '#contact',
                    'style' => 'secondary',
                ],
            ],
            'profile' => [
                'label' => __( 'Focus', 'mrsobolle' ),
                'items' => [
                    __( 'Custom WordPress themes', 'mrsobolle' ),
                    __( 'PHP architecture', 'mrsobolle' ),
                    __( 'Data import and automation', 'mrsobolle' ),
                    __( 'SEO-oriented structures', 'mrsobolle' ),
                ],
            ],
        ];
    }

    /**
     * Returns about section data.
     *
     * @return array<string, string>
     */
    private static function get_about_data(): array {
        return [
            'kicker' => __( 'About', 'mrsobolle' ),
            'title'  => __( 'A practical developer with a strong architectural mindset.', 'mrsobolle' ),
            'text'   => __( 'This website is a personal portfolio, family site and technical showcase of a modern WordPress theme built step by step with clean structure and maintainable code.', 'mrsobolle' ),
        ];
    }

    /**
     * Returns projects section data.
     *
     * @return array<string, mixed>
     */
    private static function get_projects_data(): array {
        return [
            'kicker' => __( 'Projects', 'mrsobolle' ),
            'title'  => __( 'Selected directions', 'mrsobolle' ),
            'items'  => [
                [
                    'title' => __( 'Portfolio', 'mrsobolle' ),
                    'text'  => __( 'A structured presentation of professional experience, cases and technical background.', 'mrsobolle' ),
                ],
                [
                    'title' => __( 'WordPress Theme', 'mrsobolle' ),
                    'text'  => __( 'A clean classic theme with object-oriented backend modules and simple frontend technologies.', 'mrsobolle' ),
                ],
                [
                    'title' => __( 'Family Website', 'mrsobolle' ),
                    'text'  => __( 'A personal space for future family history, photos, notes and memories.', 'mrsobolle' ),
                ],
            ],
        ];
    }

    /**
     * Returns contact section data.
     *
     * @return array<string, mixed>
     */
    private static function get_contact_data(): array {
        return [
            'kicker' => __( 'Contact', 'mrsobolle' ),
            'title'  => __( 'Let’s build something clear, useful and reliable.', 'mrsobolle' ),
            'text'   => __( 'Contact details and social links will be expanded in the next iterations.', 'mrsobolle' ),
            'links'  => Site_Data::contact_links(),
        ];
    }
}