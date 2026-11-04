<?php
/**
 * View helpers for published, translated native product Pages.
 * Product identity is a stable key, never a localized WordPress page ID.
 * @package MrSobolle
 */
namespace MrSobolle\Data;
defined( 'ABSPATH' ) || exit;
final class Product_Data {
    public static function key( int $page_id ): string {
        if ( $page_id <= 0 || ! function_exists( 'get_post_meta' ) ) { return ''; }
        return sanitize_key( (string) get_post_meta( $page_id, '_studio_product_key', true ) );
    }
    public static function stage( int $page_id ): string {
        if ( $page_id <= 0 || ! function_exists( 'get_post_meta' ) ) { return ''; }
        return sanitize_key( (string) get_post_meta( $page_id, '_studio_product_status', true ) );
    }
    public static function stage_label( string $status ): string {
        switch ( $status ) {
            case 'pre-alpha': return __( 'Pre-alpha', 'mrsobolle' );
            case 'in-development': return __( 'In development', 'mrsobolle' );
            case 'pre-release': return __( 'Preparing release', 'mrsobolle' );
            case 'released': return __( 'Released', 'mrsobolle' );
            default: return '';
        }
    }
    public static function changelog_url( int $product_id ): string {
        if ( $product_id <= 0 || ! function_exists( 'get_page_template_slug' ) ) { return ''; }
        $children = get_posts( [
            'post_type' => 'page', 'post_status' => 'publish',
            'post_parent' => $product_id, 'posts_per_page' => -1,
            'suppress_filters' => false, 'no_found_rows' => true,
        ] );
        foreach ( $children as $child ) {
            if ( 'page-templates/product-changelog.php' === get_page_template_slug( $child->ID ) ) {
                return (string) get_permalink( $child );
            }
        }
        return '';
    }
}