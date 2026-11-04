<?php
/** Selective rollback; new records become drafts, never deleted.
 * wp eval-file scripts/rollback-content.php /private/snapshot.json [apply]
 */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) { exit; }
$file = $args[0] ?? '';
if ( ! is_readable( $file ) ) { WP_CLI::error( 'Readable release snapshot required.' ); }
$snapshot = json_decode( file_get_contents( $file ), true, 512, JSON_THROW_ON_ERROR );
if ( ( $snapshot['release'] ?? '' ) !== 'studio-completion-20261105' || $snapshot['home'] !== get_option( 'home' ) || '0' !== (string) get_option( 'blog_public' ) ) { WP_CLI::error( 'Wrong site, snapshot or indexing state.' ); }
$apply = 'apply' === ( $args[1] ?? '' ); $plan = [];
foreach ( $snapshot['entries'] as $entry ) {
    $id = (int) $entry['id'];
    if ( ! $id ) {
        $found = get_posts( [ 'post_type'=>'page', 'post_status'=>'any', 'numberposts'=>2, 'suppress_filters'=>true, 'meta_key'=>'_studio_seed_key', 'meta_value'=>$entry['key'] ] );
        if ( count( $found ) > 1 ) { WP_CLI::error( 'Duplicate seed: ' . $entry['key'] ); }
        if ( ! $found ) { continue; } $id = (int) $found[0]->ID;
    }
    $post = get_post( $id ); if ( ! $post ) { WP_CLI::error( 'Missing record: ' . $id ); }
    $same = static function ( array $fields ) use ( $post ): bool {
        foreach ( $fields as $field=>$value ) { if ( (string) $post->$field !== (string) $value ) { return false; } }
        return true;
    };
    if ( $entry['before'] && $same( $entry['before']['fields'] ) && empty( $entry['applied'] ) ) { continue; }
    if ( ! $same( $entry['target'] ) ) { WP_CLI::error( 'Post-release edits detected; reconcile manually: ' . $entry['key'] ); }
    $entry['id'] = $id; $plan[] = $entry;
}
// Validate the whole rollback before the first write.
foreach ( $plan as $entry ) {
    $id = $entry['id'];
    if ( $apply ) {
        $fields = $entry['before'] ? $entry['before']['fields'] : [ 'post_status'=>'draft' ];
        $fields['ID'] = $id;
        $result = wp_update_post( wp_slash( $fields ), true );
        if ( is_wp_error( $result ) ) { WP_CLI::error( $result->get_error_message() ); }
        if ( $entry['before'] ) {
            foreach ( $entry['before']['meta'] as $key=>$original ) {
                if ( $original['exists'] ) { update_post_meta( $id, $key, wp_slash( $original['value'] ) ); }
                else { delete_post_meta( $id, $key ); }
            }
        }
    }
    WP_CLI::log( ( $apply ? 'RESTORED ' : 'ROLLBACK PLAN ' ) . $entry['key'] . ' id=' . $id . ( $entry['before'] ? ' restore fields/meta' : ' preserve as draft' ) );
}
if ( $apply ) {
    foreach ( $plan as $entry ) { if ( ! empty( $entry['before']['translations'] ) ) { pll_save_post_translations( $entry['before']['translations'] ); } }
}
WP_CLI::success( count( $plan ) . ' selective rollback records ' . ( $apply ? 'applied.' : 'validated; no changes made.' ) );
