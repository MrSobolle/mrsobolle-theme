<?php
/**
 * Plugin Name: Sobolle Studio Content
 * Description: Data model for localized product pages, journal relationships and versioned changelogs.
 * Version: 0.1.0
 * Requires PHP: 8.1
 * Author: Sobolle Studio
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', static function (): void {
    register_post_type( 'sobolle_release', [
        'labels' => [
            'name'          => 'Product releases',
            'singular_name' => 'Product release',
            'add_new_item'  => 'Add product release',
            'edit_item'     => 'Edit product release',
        ],
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => true,
        'supports'           => [ 'title', 'editor', 'revisions' ],
        'menu_icon'          => 'dashicons-update',
        'exclude_from_search'=> true,
        'has_archive'        => false,
        'rewrite'            => false,
    ] );
} );

// Polylang Free: maintain a separate release note for every supported language.
add_filter( 'pll_get_post_types', static function ( array $types ): array {
    $types['sobolle_release'] = 'sobolle_release';
    return $types;
} );

add_action( 'add_meta_boxes', static function (): void {
    add_meta_box( 'studio-product', 'Sobolle product identity', 'sobolle_studio_product_box', 'page', 'side', 'default' );
    add_meta_box( 'studio-related', 'Related products', 'sobolle_studio_related_box', 'post', 'side', 'default' );
    add_meta_box( 'studio-release', 'Release details', 'sobolle_studio_release_box', 'sobolle_release', 'normal', 'high' );
} );

function sobolle_studio_product_box( \WP_Post $post ): void {
    wp_nonce_field( 'studio_content_edit', 'studio_content_nonce' );
    $key = (string) get_post_meta( $post->ID, '_studio_product_key', true );
    $status = (string) get_post_meta( $post->ID, '_studio_product_status', true );
    echo '<p><label for="studio-product-key">Stable product key</label></p>';
    echo '<input id="studio-product-key" name="studio_product_key" type="text" class="widefat" value="' . esc_attr( $key ) . '" placeholder="widecrop">';
    echo '<p><label for="studio-product-status">Lifecycle stage</label></p>';
    echo '<select id="studio-product-status" name="studio_product_status" class="widefat">';
    foreach ( [ '' => 'None', 'pre-alpha' => 'Pre-alpha', 'in-development' => 'In development', 'pre-release' => 'Preparing release', 'released' => 'Released' ] as $value => $label ) {
        echo '<option value="' . esc_attr( $value ) . '"' . selected( $status, $value, false ) . '>' . esc_html( $label ) . '</option>';
    }
    echo '</select>';
}
function sobolle_studio_related_box( \WP_Post $post ): void {
    wp_nonce_field( 'studio_content_edit', 'studio_content_nonce' );
    $keys = (array) get_post_meta( $post->ID, '_studio_related_products', true );
    echo '<p><label for="studio-related-products">Product keys (comma separated)</label></p>';
    echo '<input id="studio-related-products" name="studio_related_products" type="text" class="widefat" value="' . esc_attr( implode( ', ', $keys ) ) . '" placeholder="widecrop, local-powershell">';
}
function sobolle_studio_release_box( \WP_Post $post ): void {
    wp_nonce_field( 'studio_content_edit', 'studio_content_nonce' );
    $fields = [
        'studio_release_product' => [ 'Product key', '_studio_release_product', 'widecrop' ],
        'studio_release_version' => [ 'Version', '_studio_release_version', '0.1.0' ],
        'studio_release_date'    => [ 'Release date (YYYY-MM-DD)', '_studio_release_date', '2026-11-04' ],
    ];
    foreach ( $fields as $name => $field ) {
        echo '<p><label for="' . esc_attr( $name ) . '">' . esc_html( $field[0] ) . '</label><br>';
        echo '<input class="widefat" type="text" id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) get_post_meta( $post->ID, $field[1], true ) ) . '" placeholder="' . esc_attr( $field[2] ) . '"></p>';
    }
    echo '<p>Use the editor for release notes in the selected language. Publish only verified releases.</p>';
}
add_action( 'save_post', static function ( int $post_id, \WP_Post $post ): void {
    if ( empty( $_POST['studio_content_nonce'] ) ||
        ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['studio_content_nonce'] ) ), 'studio_content_edit' ) ||
        ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
        wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    if ( 'page' === $post->post_type ) {
        $key = isset( $_POST['studio_product_key'] ) ? sanitize_key( wp_unslash( $_POST['studio_product_key'] ) ) : '';
        $status = isset( $_POST['studio_product_status'] ) ? sanitize_key( wp_unslash( $_POST['studio_product_status'] ) ) : '';
        update_post_meta( $post_id, '_studio_product_key', $key );
        update_post_meta( $post_id, '_studio_product_status', in_array( $status, [ '', 'pre-alpha', 'in-development', 'pre-release', 'released' ], true ) ? $status : '' );
    }
    if ( 'post' === $post->post_type ) {
        $raw = isset( $_POST['studio_related_products'] ) ? sanitize_text_field( wp_unslash( $_POST['studio_related_products'] ) ) : '';
        $keys = array_values( array_unique( array_filter( array_map( 'sanitize_key', explode( ',', $raw ) ) ) ) );
        update_post_meta( $post_id, '_studio_related_products', $keys );
    }
    if ( 'sobolle_release' === $post->post_type ) {
        $key = isset( $_POST['studio_release_product'] ) ? sanitize_key( wp_unslash( $_POST['studio_release_product'] ) ) : '';
        $version = isset( $_POST['studio_release_version'] ) ? sanitize_text_field( wp_unslash( $_POST['studio_release_version'] ) ) : '';
        $date = isset( $_POST['studio_release_date'] ) ? sanitize_text_field( wp_unslash( $_POST['studio_release_date'] ) ) : '';
        if ( ! preg_match( '/^[A-Za-z0-9][A-Za-z0-9.+-]{0,39}$/', $version ) ) { $version = ''; }
        if ( ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $date, $match ) || ! checkdate( (int) $match[2], (int) $match[3], (int) $match[1] ) ) { $date = ''; }
        update_post_meta( $post_id, '_studio_release_product', $key );
        update_post_meta( $post_id, '_studio_release_version', $version );
        update_post_meta( $post_id, '_studio_release_date', $date );
    }
}, 10, 2 );

/** @return \WP_Post[] */
function sobolle_studio_related_articles( string $product_key, int $limit = 4 ): array {
    $key = sanitize_key( $product_key );
    if ( '' === $key ) { return []; }
    $query = new \WP_Query( [
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => max( 1, min( 12, $limit ) ),
        'ignore_sticky_posts' => true,
        'no_found_rows' => true,
        'meta_query' => [ [ 'key' => '_studio_related_products', 'value' => '"' . $key . '"', 'compare' => 'LIKE' ] ],
    ] );
    return $query->posts;
}
/** @return \WP_Post[] */
function sobolle_studio_releases( string $product_key, int $limit = 100 ): array {
    $key = sanitize_key( $product_key );
    if ( '' === $key ) { return []; }
    $query = new \WP_Query( [
        'post_type' => 'sobolle_release',
        'post_status' => 'publish',
        'posts_per_page' => max( 1, min( 250, $limit ) ),
        'no_found_rows' => true,
        'meta_key' => '_studio_release_date',
        'orderby' => 'meta_value',
        'order' => 'DESC',
        'meta_query' => [ [ 'key' => '_studio_release_product', 'value' => $key, 'compare' => '=' ] ],
    ] );
    return $query->posts;
}