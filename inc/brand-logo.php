<?php
/** Use the approved transparent crest in legacy saved header/footer blocks. */
defined( 'ABSPATH' ) || exit;

add_filter( 'render_block', function ( $html ) {
	if ( false === strpos( $html, '<img' ) ) {
		return $html;
	}
	$tags = new WP_HTML_Tag_Processor( $html );
	while ( $tags->next_tag( 'IMG' ) ) {
		$source = basename( (string) wp_parse_url( (string) $tags->get_attribute( 'src' ), PHP_URL_PATH ) );
		$is_brand = $tags->has_class( 'ymc-ref-header-logo' ) || '235' === (string) $tags->get_attribute( 'data-dc-tpl' );
		if ( $is_brand && in_array( $source, array( 'ymc-soft-purple-cup-transition.png', 'ymc-footer-event-logo.png', 'd3ac31484267fd2f.png' ), true ) ) {
			$tags->set_attribute( 'src', get_theme_file_uri( 'assets/images/ymc-event-logo-transparent.png' ) );
			$tags->remove_attribute( 'srcset' );
			$tags->remove_attribute( 'sizes' );
		}
	}
	return $tags->get_updated_html();
}, 20 );
