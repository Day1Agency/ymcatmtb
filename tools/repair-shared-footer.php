<?php
/** Run with: wp eval-file wp-content/themes/ymcatmtb/tools/repair-shared-footer.php */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) { return; }
$theme = get_stylesheet();
$part = get_block_template( $theme . '//footer', 'wp_template_part' );
if ( ! $part ) { WP_CLI::error( 'Footer template part not found.' ); }
$reference = '<!-- wp:pattern {"slug":"ymcatmtb/footer"} /-->';
if ( trim( $part->content ) === $reference ) {
    WP_CLI::success( 'Footer already references the shared theme pattern.' );
    return;
}
if ( empty( $part->wp_id ) ) { WP_CLI::error( 'No database override to repair.' ); }
$post = get_post( $part->wp_id );
if ( ! str_contains( $post->post_content, 'Footer and floating badge' ) ) {
    WP_CLI::error( 'Unrecognized footer customization; review it before changing anything.' );
}
// Keep the original editable content and metadata for recovery; never replace this backup.
$backup_key = 'ymc_footer_before_shared_reference_' . $post->ID;
add_option( $backup_key, array( 'content' => $post->post_content, 'modified' => $post->post_modified_gmt, 'saved_at' => gmdate( 'c' ) ), '', false );
$result = wp_update_post( array( 'ID' => $post->ID, 'post_content' => $reference ), true );
if ( is_wp_error( $result ) ) { WP_CLI::error( $result->get_error_message() ); }
wp_get_theme()->delete_pattern_cache();
WP_CLI::success( 'Shared footer repaired. Original saved in option ' . $backup_key . '. No page content changed.' );
