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
        if ( function_exists( 'pll_home_url' ) ) {
            $localized = pll_home_url();

            if ( is_string( $localized ) && '' !== $localized ) {
                return $localized;
            }
        }

        return home_url( '/' );
    }

    /**
     * Resolve a dedicated About or Contact page in the active Polylang locale.
     * Do not emit links to nonexistent pages or anchor targets on the
     * single-screen Home.
     *
     * @param string $slug English canonical page slug.
     * @return string Localized public URL, or empty when unavailable.
     */
    public static function content_page_url( string $slug ): string {
        if ( ! in_array( $slug, [ 'about', 'contact', 'journal', 'privacy', 'terms' ], true ) ) {
            return '';
        }

        $page = get_page_by_path( $slug, OBJECT, 'page' );
        if ( ! $page instanceof \WP_Post || 'publish' !== $page->post_status ) {
            return '';
        }

        $id = (int) $page->ID;
        if ( function_exists( 'pll_get_post' ) && function_exists( 'pll_current_language' ) ) {
            $current = pll_current_language( 'slug' );
            $translated = is_string( $current ) ? pll_get_post( $id, $current ) : false;
            if ( ! is_int( $translated ) && ! ctype_digit( (string) $translated ) ) {
                return '';
            }
            $id = (int) $translated;
        }

        return 'publish' === get_post_status( $id ) ? (string) get_permalink( $id ) : '';
    }

    /**
     * Returns owner profile data.
     *
     * @return array<string, string>
     */
    public static function owner(): array {
        return [
            'name'     => 'Dmitriy Fedotov',
            'role'     => __( 'Software Engineer / PHP & WordPress', 'mrsobolle' ),
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
                'url'   => 'mailto:mrsobolle@gmail.com',
                'type'  => 'email',
            ],
            [
                'label' => __( 'GitHub', 'mrsobolle' ),
                'url'   => 'https://github.com/MrSobolle',
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