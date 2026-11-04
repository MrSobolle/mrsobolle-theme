<?php
/** Selective content migration; default read-only. No full database import.
 * wp eval-file scripts/complete-content.php [apply /private/snapshot.json]
 */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) { exit; }
if ( ! function_exists( 'pll_set_post_language' ) ) { WP_CLI::error( 'Polylang required.' ); }
$host = wp_parse_url( (string) get_option( 'home' ), PHP_URL_HOST );
if ( ! in_array( $host, [ '127.0.0.1', 'mrsobolle.com' ], true ) ) { WP_CLI::error( 'Unexpected website.' ); }
if ( '0' !== (string) get_option( 'blog_public' ) ) { WP_CLI::error( 'Expected blog_public=0.' ); }
$manifest_file = __DIR__ . '/content-20261105.json';
$manifest = json_decode( file_get_contents( $manifest_file ), true, 512, JSON_THROW_ON_ERROR );
$apply = 'apply' === ( $args[0] ?? '' );
$meta_keys = [ '_studio_seed_key', '_studio_editorial_status', '_studio_page_role', '_wp_page_template' ];
$fields = [ 'post_title', 'post_content', 'post_excerpt', 'post_status', 'post_type', 'post_parent', 'post_name', 'comment_status', 'ping_status' ];
$groups = []; $keys = []; $plan = [];
foreach ( $manifest as $row ) {
    foreach ( [ 'key', 'lang', 'parent', 'slug', 'title', 'content', 'excerpt', 'temporary' ] as $required ) {
        if ( ! array_key_exists( $required, $row ) ) { WP_CLI::error( 'Missing field: ' . $required ); }
    }
    if ( isset( $keys[$row['key']] ) || ! in_array( $row['lang'], pll_languages_list(), true ) ) { WP_CLI::error( 'Duplicate key or unknown language.' ); }
    $keys[$row['key']] = true;
    if ( ! empty( $row['group'] ) ) {
        if ( isset( $groups[$row['group']][$row['lang']] ) ) { WP_CLI::error( 'Duplicate translation member.' ); }
        $groups[$row['group']][$row['lang']] = $row['key'];
    }
    $id = (int) ( $row['id'] ?? 0 ); $post = $id ? get_post( $id ) : null;
    if ( $id && ( ! $post || $post->post_name !== $row['slug'] || $post->post_type !== ( $row['type'] ?? 'page' ) || pll_get_post_language( $id ) !== $row['lang'] || (int) $post->post_parent !== (int) $row['parent'] ) ) { WP_CLI::error( 'Identity mismatch: ' . $row['key'] ); }
    if ( ! $id ) {
        $found = get_posts( [ 'post_type'=>'page', 'post_status'=>'any', 'numberposts'=>2, 'suppress_filters'=>true, 'meta_key'=>'_studio_seed_key', 'meta_value'=>$row['key'] ] );
        if ( count( $found ) > 1 ) { WP_CLI::error( 'Duplicate seed: ' . $row['key'] ); }
        if ( $found ) { $post = $found[0]; $id = (int) $post->ID; }
        else {
            $siblings = get_posts( [ 'post_type'=>'page', 'post_status'=>'any', 'post_parent'=>$row['parent'], 'name'=>$row['slug'], 'numberposts'=>1, 'suppress_filters'=>true ] );
            if ( $siblings ) { WP_CLI::error( 'Slug collision: ' . $row['key'] ); }
        }
    }
    if ( $post ) {
        if ( $post->post_name !== $row['slug'] || (int) $post->post_parent !== (int) $row['parent'] || pll_get_post_language( $id ) !== $row['lang'] ) { WP_CLI::error( 'Seed identity mismatch: ' . $row['key'] ); }
        $hashes = [ hash( 'sha256', $row['content'] ), $row['before_hash'] ?? '', $row['previous_content_hash'] ?? '' ];
        if ( '127.0.0.1' === $host ) { $hashes[] = $row['before_local_hash'] ?? ''; }
        if ( ! in_array( hash( 'sha256', $post->post_content ), $hashes, true ) ) { WP_CLI::error( 'Content edited after review: ' . $row['key'] ); }
        if ( ! in_array( $post->post_title, [ $row['title'], $row['before_title'] ?? $row['title'] ], true ) || ! in_array( $post->post_excerpt, [ $row['excerpt'], $row['before_excerpt'] ?? $row['excerpt'], $row['previous_excerpt'] ?? $row['excerpt'] ], true ) || ! in_array( $post->post_status, [ $row['status'] ?? 'publish', $row['before_status'] ?? 'publish' ], true ) ) { WP_CLI::error( 'Title, summary or status changed: ' . $row['key'] ); }
    }
    if ( (int) $row['parent'] > 0 ) {
        $parent = get_post( (int) $row['parent'] );
        if ( ! $parent || 'publish' !== $parent->post_status || pll_get_post_language( $parent->ID ) !== $row['lang'] ) { WP_CLI::error( 'Missing/cross-language parent: ' . $row['key'] ); }
    }
    $target = [ 'post_title'=>$row['title'], 'post_content'=>$row['content'], 'post_excerpt'=>$row['excerpt'], 'post_status'=>$row['status'] ?? 'publish', 'post_type'=>$row['type'] ?? 'page', 'post_parent'=>(int) $row['parent'], 'post_name'=>$row['slug'], 'comment_status'=>'closed', 'ping_status'=>'closed' ];
    if ( 'draft' === $target['post_status'] && $post ) { $target['post_content'] = $post->post_content; }
    $meta = [ '_studio_seed_key'=>$row['key'], '_studio_editorial_status'=>$row['temporary'] ? 'temporary-review-required' : 'verified-existing' ];
    if ( ! empty( $row['template'] ) ) { $meta['_wp_page_template'] = $row['template']; }
    if ( ! empty( $row['role'] ) ) { $meta['_studio_page_role'] = $row['role']; }
    $before = null;
    if ( $post ) {
        $before = [ 'fields'=>[], 'meta'=>[], 'translations'=>pll_get_post_translations( $id ) ];
        foreach ( $fields as $field ) { $before['fields'][$field] = $post->$field; }
        foreach ( $meta_keys as $key ) { $before['meta'][$key] = [ 'exists'=>metadata_exists( 'post', $id, $key ), 'value'=>get_post_meta( $id, $key, true ) ]; }
    }
    $plan[] = [ 'key'=>$row['key'], 'lang'=>$row['lang'], 'group'=>$row['group'] ?? '', 'id'=>$id, 'before'=>$before, 'target'=>$target, 'meta'=>$meta ];
}
foreach ( $groups as $name=>$members ) {
    $languages = array_keys( $members ); sort( $languages );
    if ( $languages !== [ 'de', 'en', 'es', 'fr', 'ru' ] ) { WP_CLI::error( 'Incomplete translation group BEFORE writes: ' . $name ); }
}
$snapshot_file = $args[1] ?? '';
$snapshot = [ 'release'=>'studio-completion-20261105', 'created_utc'=>gmdate( DATE_W3C ), 'home'=>get_option( 'home' ), 'manifest_sha256'=>hash_file( 'sha256', $manifest_file ), 'entries'=>$plan ];
$save = static function () use ( &$snapshot, $snapshot_file ): void {
    $json = wp_json_encode( $snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
    if ( false === file_put_contents( $snapshot_file, $json, LOCK_EX ) ) { WP_CLI::error( 'Cannot persist rollback snapshot.' ); }
    chmod( $snapshot_file, 0600 );
};
if ( $apply ) {
    $directory = realpath( dirname( $snapshot_file ) );
    $wp_root = strtolower( str_replace( '\\', '/', realpath( ABSPATH ) ) );
    $normalized = $directory ? strtolower( str_replace( '\\', '/', $directory ) ) : '';
    if ( ! $directory || ! is_writable( $directory ) || file_exists( $snapshot_file ) || $normalized === $wp_root || str_starts_with( $normalized . '/', $wp_root . '/' ) || '.json' !== substr( $snapshot_file, -5 ) ) { WP_CLI::error( 'Apply requires a NEW writable .json rollback snapshot outside WordPress.' ); }
    $save(); // No database write before a recoverable selective snapshot exists.
}
$translations = [];
foreach ( $plan as $index=>$entry ) {
    $id = $entry['id'];
    if ( $apply ) {
        $target = $entry['target'];
        if ( $id ) { $target['ID'] = $id; }
        else { $target['post_author'] = (int) get_post_field( 'post_author', (int) get_option( 'page_on_front' ) ); }
        $target['meta_input'] = $entry['meta'];
        $result = wp_insert_post( wp_slash( $target ), true );
        if ( is_wp_error( $result ) ) { WP_CLI::error( $result->get_error_message() . '; use saved selective rollback.' ); }
        $id = (int) $result;
        pll_set_post_language( $id, $entry['lang'] );
        $snapshot['entries'][$index]['id'] = $id;
        $snapshot['entries'][$index]['applied'] = true;
        $save();
    }
    if ( $id && $entry['group'] ) { $translations[$entry['group']][$entry['lang']] = $id; }
    WP_CLI::log( ( $apply ? 'APPLIED ' : 'PLAN ' ) . $entry['key'] . ' id=' . $id . ' status=' . $entry['target']['post_status'] );
}
if ( $apply ) {
    foreach ( $translations as $group ) { pll_save_post_translations( $group ); }
    $snapshot['completed_utc'] = gmdate( DATE_W3C ); $save();
}
WP_CLI::success( ( $apply ? 'Applied ' : 'Preflight passed: ' ) . count( $plan ) . ' records; ' . count( $groups ) . ' complete translation groups. blog_public=0 unchanged.' );
