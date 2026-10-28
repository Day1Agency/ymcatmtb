<?php
add_action( 'wp_enqueue_scripts', function () {
	if ( is_404() ) {
		wp_enqueue_style( 'ymc-notfound', get_theme_file_uri( 'assets/css/notfound.css' ), array(), (string) filemtime( get_theme_file_path( 'assets/css/notfound.css' ) ) );
	}
}, 40 );
add_action( 'wp_enqueue_scripts', function () {
	if ( is_page( array( 'need-a-team', 'register-a-team' ) ) || has_shortcode( get_post()->post_content ?? '', 'ymc_team_request' ) || has_shortcode( get_post()->post_content ?? '', 'ymc_captain_request' ) ) {
		wp_enqueue_style( 'ymc-team-request', get_theme_file_uri( 'assets/css/team-request.css' ), array(), (string) filemtime( get_theme_file_path( 'assets/css/team-request.css' ) ) );
		wp_enqueue_script( 'ymc-captain-steps', get_theme_file_uri( 'assets/js/captain-steps.js' ), array(), (string) filemtime( get_theme_file_path( 'assets/js/captain-steps.js' ) ), true );
	}
}, 40 );
/**
 * Names the site as the sender of every email it sends.
 *
 * WordPress defaults to "WordPress" <wordpress@domain>, which reads as a misdirected system
 * notice. The address stays on ymcatmtb.com because that is the domain this server is
 * authorised to send for; a yeshivasmekorchaim.org From would fail SPF and land in spam.
 * No mailbox is required to send, and each message sets its own Reply-To.
 *
 * Both filters are skipped when a message supplies its own From header.
 */
add_filter( 'wp_mail_from', function ( $from ) {
	return 0 === strpos( $from, 'wordpress@' ) ? 'event@ymcatmtb.com' : $from;
} );
add_filter( 'wp_mail_from_name', function ( $name ) {
	return 'WordPress' === $name ? 'YMC at M&T' : $name;
} );

// Shared footer icons are needed on regular pages as well as event sections.
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'ymc-footer-icons', get_theme_file_uri( 'assets/css/footer-icons.css' ), array(), (string) filemtime( get_theme_file_path( 'assets/css/footer-icons.css' ) ) );
}, 40 );
add_action( 'after_setup_theme', function () { add_editor_style( 'assets/css/footer-icons.css' ); } );
// Player Forms styling is shared by the page and the block editor.
add_action( 'wp_enqueue_scripts', function () {
	if ( is_page( 'player-forms' ) ) {
		wp_enqueue_style( 'ymc-player-forms', get_theme_file_uri( 'assets/css/player-forms.css' ), array(), (string) filemtime( get_theme_file_path( 'assets/css/player-forms.css' ) ) );
		wp_enqueue_script( 'ymc-player-forms', get_theme_file_uri( 'assets/js/player-forms.js' ), array(), (string) filemtime( get_theme_file_path( 'assets/js/player-forms.js' ) ), true );
	}
}, 40 );
add_action( 'after_setup_theme', function () { add_editor_style( 'assets/css/player-forms.css' ); } );
require_once __DIR__ . '/inc/captain-invitations.php';
/** Use the current event crest when no Site Icon has been configured. */
function ymcatmtb_default_site_icon( $url ) {
	if ( ! get_option( 'site_icon' ) ) {
		return get_theme_file_uri( 'assets/images/event-site-icon.png' );
	}
	return $url;
}
add_filter( 'get_site_icon_url', 'ymcatmtb_default_site_icon' );

/** Local and host rewrites can treat favicon.ico as a normal page. */
function ymcatmtb_favicon_request() {
	$path = wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH );
	$icon_path = wp_parse_url( home_url( '/favicon.ico' ), PHP_URL_PATH );
	if ( $path === $icon_path ) {
		wp_safe_redirect( get_site_icon_url( 32 ), 302 );
		exit;
	}
}
add_action( 'init', 'ymcatmtb_favicon_request', 1 );

// Announce the icon before the rest of the head's styles and scripts.
remove_action( 'wp_head', 'wp_site_icon', 99 );
add_action( 'wp_head', 'wp_site_icon', 1 );

/**
 * Twenty Twenty-Five functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_Five
 * @since Twenty Twenty-Five 1.0
 */

if ( ! function_exists( 'twentytwentyfive_post_format_setup' ) ) :
	/**
	 * Adds theme support for post formats.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_post_format_setup() {
		add_theme_support( 'post-formats', array( 'aside', 'audio', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video' ) );
	}
endif;
add_action( 'after_setup_theme', 'twentytwentyfive_post_format_setup' );

if ( ! function_exists( 'twentytwentyfive_editor_style' ) ) :
	/**
	 * Enqueues editor-style.css in the editors.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_editor_style() {
		add_editor_style( 'assets/css/editor-style.css' );
	}
endif;
add_action( 'after_setup_theme', 'twentytwentyfive_editor_style' );

if ( ! function_exists( 'twentytwentyfive_enqueue_styles' ) ) :
	/**
	 * Enqueues the theme stylesheet on the front.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_enqueue_styles() {
		$src = 'style.css';

		wp_enqueue_style(
			'twentytwentyfive-style',
			get_parent_theme_file_uri( $src ),
			array(),
			wp_get_theme()->get( 'Version' )
		);
		wp_style_add_data(
			'twentytwentyfive-style',
			'path',
			get_parent_theme_file_path( $src )
		);
	}
endif;
add_action( 'wp_enqueue_scripts', 'twentytwentyfive_enqueue_styles' );

if ( ! function_exists( 'twentytwentyfive_block_styles' ) ) :
	/**
	 * Registers custom block styles.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_block_styles() {
		register_block_style(
			'core/list',
			array(
				'name'         => 'checkmark-list',
				'label'        => __( 'Checkmark', 'twentytwentyfive' ),
				'inline_style' => '
				ul.is-style-checkmark-list {
					list-style-type: "\2713";
				}

				ul.is-style-checkmark-list li {
					padding-inline-start: 1ch;
				}',
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_block_styles' );

if ( ! function_exists( 'twentytwentyfive_pattern_categories' ) ) :
	/**
	 * Registers pattern categories.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_pattern_categories() {

		register_block_pattern_category(
			'twentytwentyfive_page',
			array(
				'label'       => __( 'Pages', 'twentytwentyfive' ),
				'description' => __( 'A collection of full page layouts.', 'twentytwentyfive' ),
			)
		);

		register_block_pattern_category(
			'twentytwentyfive_post-format',
			array(
				'label'       => __( 'Post formats', 'twentytwentyfive' ),
				'description' => __( 'A collection of post format patterns.', 'twentytwentyfive' ),
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_pattern_categories' );

if ( ! function_exists( 'twentytwentyfive_register_block_bindings' ) ) :
	/**
	 * Registers the post format block binding source.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_register_block_bindings() {
		register_block_bindings_source(
			'twentytwentyfive/format',
			array(
				'label'              => _x( 'Post format name', 'Label for the block binding placeholder in the editor', 'twentytwentyfive' ),
				'get_value_callback' => 'twentytwentyfive_format_binding',
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_register_block_bindings' );

if ( ! function_exists( 'twentytwentyfive_format_binding' ) ) :
	/**
	 * Callback function for the post format name block binding source.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return string|void Post format name, or nothing if the format is 'standard'.
	 */
	function twentytwentyfive_format_binding() {
		$post_format_slug = get_post_format();

		if ( $post_format_slug && 'standard' !== $post_format_slug ) {
			return get_post_format_string( $post_format_slug );
		}
	}
endif;

/*
 * ---------------------------------------------------------------------------
 * YMC at M&T
 * ---------------------------------------------------------------------------
 */

/**
 * The theme's own stylesheet, on the front and in the editor.
 */
function ymcatmtb_styles() {
	$file = get_theme_file_path( 'assets/css/main.css' );

	wp_enqueue_style(
		'ymcatmtb',
		get_theme_file_uri( 'assets/css/main.css' ),
		array(),
		file_exists( $file ) ? (string) filemtime( $file ) : '1.0.0'
	);
}
add_action( 'wp_enqueue_scripts', 'ymcatmtb_styles' );

/**
 * The same stylesheet inside the editor.
 */
function ymcatmtb_editor_styles() {
	add_editor_style( array( 'assets/css/reference.css', 'assets/css/reference-editor.css' ) );
}
add_action( 'after_setup_theme', 'ymcatmtb_editor_styles' );

/**
 * Keeps the dark page styling out of the classic editor.
 *
 * add_editor_style() applies to every editor screen. The block editor scopes those rules to
 * its own wrapper, but the classic editor loads them into a bare iframe, so reference.css
 * painted the body black on unrelated admin screens.
 */
function ymcatmtb_classic_editor_css( $css ) {
	$reference = get_theme_file_uri( 'assets/css/reference' );
	$keep      = array_filter(
		array_map( 'trim', explode( ',', $css ) ),
		function ( $url ) use ( $reference ) {
			return '' !== $url && 0 !== strpos( $url, $reference );
		}
	);

	return implode( ',', $keep );
}
add_filter( 'mce_css', 'ymcatmtb_classic_editor_css' );

/**
 * Where each button sends people.
 *
 * Registration, tickets and sponsorship are taken on Givebutter, so the
 * addresses live here and a campaign can move without hunting through
 * patterns. Registration opens the sign-up form rather than the campaign page.
 *
 * @param string $key Which destination.
 * @return string The address, or the home page when the key is unknown.
 */
function ymcatmtb_link( $key ) {
	$links = apply_filters(
		'ymcatmtb_links',
		array(
			'teen'       => 'https://givebutter.com/ymc-teen-player-registration/register',
			'team-start' => 'https://givebutter.com/ymc-adult-player-registration/register',
			'team-join'  => 'https://givebutter.com/ymc-adult-player-registration/register',
			'tickets'    => 'https://givebutter.com/ymc-at-mt-tickets-t96vtp',
			'sponsor'    => 'https://givebutter.com/ymc-sponsorship-opportunities-mxvkzl',
			'donate'     => 'https://givebutter.com/ymc-annual-campaign',
		)
	);

	return isset( $links[ $key ] ) ? $links[ $key ] : home_url( '/' );
}

/**
 * Who answers player questions.
 *
 * @return string
 */
function ymcatmtb_contact_email() {
	return apply_filters( 'ymcatmtb_contact_email', 'ykohen@yeshivasmekorchaim.org' );
}

/**
 * When the first game starts. Drives the countdown in the hero.
 *
 * @return string An ISO 8601 date and time.
 */
function ymcatmtb_kickoff() {
	return apply_filters( 'ymcatmtb_kickoff', '2027-03-28T00:00:00-04:00' );
}

/**
 * The countdown script, on the front only.
 */
function ymcatmtb_scripts() {
	$file = get_theme_file_path( 'assets/js/countdown.js' );

	wp_enqueue_script(
		'ymcatmtb-countdown',
		get_theme_file_uri( 'assets/js/countdown.js' ),
		array(),
		file_exists( $file ) ? (string) filemtime( $file ) : '1.0.0',
		true
	);
}
add_action( 'wp_enqueue_scripts', 'ymcatmtb_scripts' );

/** Load the supplied registration design without the starter theme's overrides. */
function ymcatmtb_reference_assets() {
	if ( ! is_front_page() && ! has_block( 'ymcatmtb/section' ) ) {
		return;
	}
	wp_dequeue_style( 'twentytwentyfive-style' );
	wp_dequeue_style( 'ymcatmtb' );
	wp_dequeue_script( 'ymcatmtb-countdown' );
	foreach ( array( 'css/reference.css', 'js/reference.js' ) as $asset ) {
		$file = get_theme_file_path( 'assets/' . $asset );
		if ( ! file_exists( $file ) ) {
			continue;
		}
		if ( substr( $asset, -4 ) === '.css' ) {
			wp_enqueue_style( 'ymcatmtb-reference', get_theme_file_uri( 'assets/' . $asset ), array(), (string) filemtime( $file ) );
		} else {
			wp_enqueue_script( 'ymcatmtb-reference', get_theme_file_uri( 'assets/' . $asset ), array(), (string) filemtime( $file ), true );
			wp_add_inline_script( 'ymcatmtb-reference', 'window.ymcKickoff = ' . wp_json_encode( ymcatmtb_kickoff() ) . ';', 'before' );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'ymcatmtb_reference_assets', 30 );

/** Stadium intro enhancement; inactive when its saved block is absent. */
function ymcatmtb_stadium_assets() {
	if ( ! is_front_page() ) { return; }
	foreach ( array( 'css', 'js' ) as $type ) {
		$asset = 'assets/' . $type . '/stadium-intro.' . $type;
		$version = (string) filemtime( get_theme_file_path( $asset ) );
		if ( 'css' === $type ) {
			wp_enqueue_style( 'ymcatmtb-stadium', get_theme_file_uri( $asset ), array( 'ymcatmtb-reference' ), $version );
		} else {
			wp_enqueue_script( 'ymcatmtb-stadium', get_theme_file_uri( $asset ), array( 'ymcatmtb-reference' ), $version, true );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'ymcatmtb_stadium_assets', 31 );

/** Independent visual section blocks used by the theme's unsynced patterns. */
function ymcatmtb_register_section_block() {
	wp_register_script( 'ymcatmtb-section-editor', get_theme_file_uri( 'assets/js/section-editor.js' ), array( 'wp-blocks', 'wp-block-editor', 'wp-element', 'wp-components' ), (string) filemtime( get_theme_file_path( 'assets/js/section-editor.js' ) ), true );
	register_block_type( 'ymcatmtb/section', array(
		'api_version' => 3,
		'editor_script' => 'ymcatmtb-section-editor',
		'attributes' => array(
			'title' => array( 'type' => 'string', 'default' => 'YMC section' ),
			'html' => array( 'type' => 'string', 'source' => 'html', 'selector' => '.ymc-section-content', 'default' => '' ),
		),
	) );
}
add_action( 'init', 'ymcatmtb_register_section_block' );

/** Some hosts do not enqueue a block's editor script on their own; make sure of it. */
function ymcatmtb_section_editor_assets() {
	wp_enqueue_script( 'ymcatmtb-section-editor' );
}
add_action( 'enqueue_block_editor_assets', 'ymcatmtb_section_editor_assets' );

/** Serialize a standalone block; it carries no reference to a parent pattern. */
function ymcatmtb_section_markup( $title, $html ) {
	$html = preg_replace( '/<!--\s*\/?wp:html\s*-->/', '', $html );
	$document = new DOMDocument();
	$previous = libxml_use_internal_errors( true );
	$document->loadHTML( '<?xml encoding="UTF-8"><div id="ymc-section-source">' . trim( $html ) . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	libxml_use_internal_errors( $previous );
	$root = $document->getElementById( 'ymc-section-source' );
	$index = 0;
	foreach ( $root->getElementsByTagName( '*' ) as $element ) {
		$element->setAttribute( 'data-ymc-node', (string) $index++ );
	}
	$html = '';
	foreach ( $root->childNodes as $node ) {
		$html .= $document->saveHTML( $node );
	}
	return '<!-- wp:ymcatmtb/section ' . wp_json_encode( array( 'title' => $title ) ) . ' -->' . "\n" . '<div class="wp-block-ymcatmtb-section ymc-reference"><div class="ymc-section-content">' . $html . '</div></div>' . "\n" . '<!-- /wp:ymcatmtb/section -->';
}
