<?php
/**
 * Read-only data access for hierarchical WordPress Page directories.
 *
 * Using native Pages keeps product copy and case studies editable in WordPress.
 * If a multilingual plugin is installed later, its language filters may apply
 * because we explicitly do not suppress WordPress query filters.
 *
 * @package MrSobolle
 */

namespace MrSobolle\Data;

defined( 'ABSPATH' ) || exit;

final class Directory_Data {

    /**
     * Return published immediate child pages, in editor-controlled order.
     *
     * @param int $parent_id Parent page identifier.
     * @return \WP_Post[]
     */
    public static function children( int $parent_id ): array {
        if ( $parent_id <= 0 ) {
            return [];
        }

        $pages = get_posts(
            [
                'post_type'              => 'page',
                'post_status'            => 'publish',
                'post_parent'            => $parent_id,
                'posts_per_page'         => -1,
                'orderby'                => [
                    'menu_order' => 'ASC',
                    'title'      => 'ASC',
                ],
                'suppress_filters'       => false,
                'ignore_sticky_posts'    => true,
                'no_found_rows'          => true,
                'update_post_meta_cache' => false,
            ]
        );

        return is_array( $pages ) ? $pages : [];
    }

    /**
     * Locate the current-language directory page by its assigned page template.
     *
     * Template identity is independent of the translated page slug. A future
     * language plugin can filter this query for the active language.
     *
     * @param string $template Template path in this theme.
     * @return \WP_Post|null
     */
    public static function directory_page( string $template ): ?\WP_Post {
        $allowed = [ 'page-templates/apps-catalog.php', 'page-templates/work-catalog.php' ];

        if ( ! in_array( $template, $allowed, true ) ) {
            return null;
        }

        $pages = get_posts(
            [
                'post_type'              => 'page',
                'post_status'            => 'publish',
                'posts_per_page'         => 1,
                'meta_key'               => '_wp_page_template',
                'meta_value'             => $template,
                'suppress_filters'       => false,
                'ignore_sticky_posts'    => true,
                'no_found_rows'          => true,
                'update_post_meta_cache' => false,
            ]
        );

        if ( ! is_array( $pages ) || empty( $pages[0] ) || ! $pages[0] instanceof \WP_Post ) {
            return null;
        }

        return $pages[0];
    }

    /**
     * Link to a configured directory, if it exists in the current language.
     *
     * @param string $kind Supported directory kind: apps or work.
     * @return string URL or empty string.
     */
    public static function directory_url( string $kind ): string {
        $templates = [
            'apps' => 'page-templates/apps-catalog.php',
            'work' => 'page-templates/work-catalog.php',
        ];

        if ( ! isset( $templates[ $kind ] ) ) {
            return '';
        }

        $page = self::directory_page( $templates[ $kind ] );

        return $page ? (string) get_permalink( $page ) : '';
    }
}