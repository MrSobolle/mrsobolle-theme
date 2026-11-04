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
            'hero'       => self::get_hero_data(),
            'directions' => self::get_directions_data(),
            'projects'   => self::get_projects_data(),
            'about'      => self::get_about_data(),
            'contact'    => self::get_contact_data(),
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
            'lead'    => __( 'Software products, web solutions and engineering.', 'mrsobolle' ),
            'actions' => [
                [
                    'label' => __( 'Explore apps', 'mrsobolle' ),
                    'url'   => Directory_Data::directory_url( 'apps' ) ?: '#projects',
                    'style' => 'primary',
                ],
                [
                    'label' => __( 'Explore work', 'mrsobolle' ),
                    'url'   => Directory_Data::directory_url( 'work' ) ?: '#projects',
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
     * Public areas of work. Unpublished developer tools link to the Apps
     * catalogue, while WideCrop links to its actual translated product page.
     *
     * @return array<string, mixed>
     */
    private static function get_directions_data(): array {
        $products_url    = Directory_Data::directory_url( 'apps' );
        $engineering_url = Directory_Data::directory_url( 'work' );

        return [
            'kicker' => __( 'What I build', 'mrsobolle' ),
            'title'  => __( 'Independent software for practical problems.', 'mrsobolle' ),
            'items'  => [
                [
                    'number'     => '01',
                    'title'      => __( 'Products', 'mrsobolle' ),
                    'text'       => __( 'Useful software built to solve practical problems.', 'mrsobolle' ),
                    'url'        => $products_url,
                    'link_label' => __( 'Explore apps', 'mrsobolle' ),
                ],
                [
                    'number'     => '02',
                    'title'      => __( 'Engineering', 'mrsobolle' ),
                    'text'       => __( 'Software engineering, architecture and selected professional work.', 'mrsobolle' ),
                    'url'        => $engineering_url,
                    'link_label' => __( 'Explore work', 'mrsobolle' ),
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
            'title'  => __( 'Software engineering from architecture to delivery.', 'mrsobolle' ),
            'text'   => __( 'I work with PHP, WordPress, integrations, data processing and developer tooling. Here you can explore my professional work and independently built software.', 'mrsobolle' ),
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
            'title'  => __( 'Professional work & software', 'mrsobolle' ),
            'items'  => [
                [
                    'title' => __( 'Professional work', 'mrsobolle' ),
                    'url'   => Directory_Data::directory_url( 'work' ),
                    'link_label' => __( 'Explore work', 'mrsobolle' ),
                    'text'  => __( 'Selected engineering challenges, architectural decisions and technical contributions.', 'mrsobolle' ),
                ],
                [
                    'title' => __( 'Software products', 'mrsobolle' ),
                    'url'   => Directory_Data::directory_url( 'apps' ),
                    'link_label' => __( 'Explore apps', 'mrsobolle' ),
                    'text'  => __( 'Browser extensions, automation tools and software built around practical needs.', 'mrsobolle' ),
                ],
                [
                    'title' => __( 'Open development', 'mrsobolle' ),
                    'url'   => 'https://github.com/MrSobolle/mrsobolle-theme',
                    'link_label' => __( 'View source', 'mrsobolle' ),
                    'text'  => __( 'This website is an open example of maintainable WordPress theme development.', 'mrsobolle' ),
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
            'text'   => __( 'For professional inquiries, please use one of the contact links below.', 'mrsobolle' ),
            'links'  => Site_Data::contact_links(),
        ];
    }
}