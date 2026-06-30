<?php
/**
 * Site data provider.
 *
 * @package MrSobolle
 */

namespace MrSobolle\Data;

defined( 'ABSPATH' ) || exit;

/**
 * Provides shared site-level data.
 */
final class Site_Data {

    /**
     * Returns the public site name.
     */
    public static function site_name(): string {
        return get_bloginfo( 'name' );
    }

    /**
     * Returns the public site description.
     */
    public static function site_description(): string {
        return get_bloginfo( 'description', 'display' );
    }

    /**
     * Returns the home URL.
     */
    public static function home_url(): string {
        return home_url( '/' );
    }

    /**
     * Returns owner profile data.
     *
     * @return array<string, string>
     */
    public static function owner(): array {
        return [
            'name'     => 'Dmitriy Fedotov',
            'role'     => 'WordPress / PHP Developer',
            'location' => 'Europe / Remote',
        ];
    }

    /**
     * Returns contact links.
     *
     * Replace placeholder URLs when real public profiles are ready.
     *
     * @return array<int, array<string, string>>
     */
    public static function contact_links(): array {
        return [
            [
                'label' => __( 'Email', 'mrsobolle' ),
                'url'   => 'mailto:contact@mrsobolle.com',
                'type'  => 'email',
            ],
            [
                'label' => __( 'LinkedIn', 'mrsobolle' ),
                'url'   => 'https://www.linkedin.com/in/mrsobolle/',
                'type'  => 'social',
            ],
            [
                'label' => __( 'GitHub', 'mrsobolle' ),
                'url'   => '#',
                'type'  => 'social',
            ],
        ];
    }

    /**
     * Returns social links intended for header/footer usage.
     *
     * @return array<int, array<string, string>>
     */
    public static function social_links(): array {
        return array_values(
            array_filter(
                self::contact_links(),
                static function ( array $link ): bool {
                    return isset( $link['type'] ) && 'social' === $link['type'];
                }
            )
        );
    }
}