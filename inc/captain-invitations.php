<?php
/** Captain invitation delivery. Disabled until configured under Settings. */
namespace YMC\CaptainInvitations;

defined( 'ABSPATH' ) || exit;
const CONFIG = 'ymc_captain_invitations';
const JOB_PREFIX = 'ymc_captain_job_';
const HOOK = 'ymc_process_captain_invitation';
const CAMPAIGN_CODE = 'RMSQHT';
const REGISTRATION = 'https://givebutter.com/ymc-adult-player-registration/register';

function config() { return (array) get_option( CONFIG, array() ); }
function job_key( $id ) { return JOB_PREFIX . hash( 'sha256', (string) $id ); }
function captain_ticket( $title ) {
	// Campaign is independently verified from the authenticated transaction.
	return 1 === preg_match( '/\(Captain Only\)$/iu', trim( (string) $title ) );
}

function invitation_email( $team_name ) {
	$name = esc_html( $team_name );
	$url = esc_url( REGISTRATION );
	return '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head><body style="margin:0;background:#f3f0f7;font-family:Arial,sans-serif;color:#302344">'
		. '<div style="max-width:600px;margin:24px auto;background:#fff;border-radius:12px;overflow:hidden"><div style="padding:28px;background:#281744;color:#fff;border-bottom:4px solid #d8b654"><strong>YMC AT M&amp;T</strong><p style="margin-bottom:0;font-size:13px">ADULT TOURNAMENT · MARCH 28, 2027</p></div>'
		. '<div style="padding:30px"><h1 style="font-size:26px;margin-top:0">Your team is ready. Invite your players.</h1><p>Thank you for registering as captain of <strong>' . $name . '</strong>.</p><p>Forward this email to your players so they can register for your team.</p>'
		. '<div style="padding:22px;background:#f5f2f8;border:1px solid #e3daeb;border-radius:8px"><h2 style="margin-top:0;font-size:21px">Join ' . $name . '</h2><ol style="padding-left:22px;line-height:1.8"><li>Open the registration link below.</li><li>Sign in or create your Givebutter account.</li><li>Choose <strong>Join a team</strong> and select <strong>' . $name . '</strong>.</li><li>Choose <strong>Adult Tournament — Player</strong> and complete registration.</li></ol>'
		. '<p style="margin:24px 0"><a href="' . $url . '" style="display:inline-block;padding:15px 22px;background:#38245f;color:#fff;text-decoration:none;border-radius:6px;font-weight:bold">Register for the tournament →</a></p><p style="font-size:12px;line-height:1.6;overflow-wrap:anywhere">Or open:<br><a href="' . $url . '">' . $url . '</a></p></div>'
		. '<p style="font-size:13px;line-height:1.7;color:#746780">Please select the team name shown above during registration. The link opens registration; it does not automatically select or reserve a team.</p><p style="font-size:12px;color:#746780">Yeshivas Mekor Chaim · YMC at M&amp;T</p></div></div></body></html>';
}

/** Only call the documented API host; do not follow pagination URLs or redirects. */
function api( $path ) {
	$key = config()['api_key'] ?? '';
	if ( ! $key ) { throw new \RuntimeException( 'Givebutter API key is not configured.' ); }
	$response = wp_remote_get( 'https://api.givebutter.com/v1/' . $path, array(
		'timeout' => 20, 'redirection' => 0,
		'headers' => array( 'Authorization' => 'Bearer ' . $key, 'Accept' => 'application/json' ),
	) );
	if ( is_wp_error( $response ) ) { throw new \RuntimeException( 'Givebutter could not be reached. The invitation will be retried.' ); }
	if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
		throw new \RuntimeException( 'Givebutter API returned HTTP ' . wp_remote_retrieve_response_code( $response ) . '.' );
	}
	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	// Givebutter returns single resources unwrapped, unlike paginated collections.
	if ( is_array( $data ) && isset( $data['id'] ) && ! isset( $data['data'] ) ) {
		$data = array( 'data' => $data );
	}
	if ( ! is_array( $data ) || ! isset( $data['data'] ) || ! is_array( $data['data'] ) ) {
		throw new \RuntimeException( 'Givebutter returned an unexpected response.' );
	}
	return $data;
}

function collection( $path ) {
	$items = array();
	for ( $page = 1; $page <= 20; $page++ ) {
		$response = api( $path . '?page=' . $page );
		$items = array_merge( $items, $response['data'] );
		if ( empty( $response['links']['next'] ) ) { return $items; }
	}
	throw new \RuntimeException( 'Givebutter pagination exceeded the safe limit; no invitation was sent.' );
}

/** Match a captain ticket holder to exactly one fundraiser and its team URL. */
function resolve_captain( $ticket, $transaction, $members, $teams ) {
	if ( ! captain_ticket( $ticket['title'] ?? '' ) ) { throw new \RuntimeException( 'This is not a configured captain ticket.' ); }
	if ( CAMPAIGN_CODE !== ( $transaction['campaign_code'] ?? '' ) || 'succeeded' !== ( $transaction['status'] ?? '' ) ) {
		throw new \RuntimeException( 'The adult registration payment is not confirmed.' );
	}
	if ( (string) ( $ticket['transaction_id'] ?? '' ) !== (string) ( $transaction['id'] ?? '' ) ) {
		throw new \RuntimeException( 'The ticket and transaction do not match.' );
	}
	foreach ( $transaction['transactions'] ?? array() as $payment ) {
		if ( ! empty( $payment['refunded'] ) ) { throw new \RuntimeException( 'This transaction has a refund; no invitation was sent.' ); }
	}
	$email = strtolower( trim( $ticket['email'] ?? '' ) );
	if ( ! is_email( $email ) ) { throw new \RuntimeException( 'The captain ticket does not contain a registered email address.' ); }
	$matches = array();
	foreach ( $members as $member ) {
		if ( strtolower( trim( $member['email'] ?? '' ) ) !== $email ) { continue; }
		$url = $member['url'] ?? '';
		foreach ( $teams as $team ) {
			$team_url = rtrim( $team['url'] ?? '', '/' );
			if ( ! preg_match( '~^https://givebutter\.com/ymc-adult-player-registration/[^/?#]+$~', $team_url ) ) { continue; }
			// A team URL must be a complete path segment, never just a name prefix.
			if ( str_starts_with( $url, $team_url . '/' ) && 1 === substr_count( substr( $url, strlen( $team_url ) ), '/' ) ) {
				$matches[] = array( 'email' => $email, 'member_id' => (string) $member['id'], 'team' => $team );
			}
		}
	}
	if ( 1 !== count( $matches ) ) {
		throw new \RuntimeException( 'Waiting for one unambiguous captain fundraiser and team match. No email was sent.' );
	}
	return $matches[0];
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'ymc/v1', '/captain-invitations', array(
		'methods' => 'POST', 'callback' => __NAMESPACE__ . '\\webhook',
		'permission_callback' => function ( $request ) {
			$secret = config()['signing_secret'] ?? '';
			return $secret && hash_equals( $secret, (string) $request->get_header( 'signature' ) )
				? true : new \WP_Error( 'invalid_signature', 'Invalid webhook signature.', array( 'status' => 403 ) );
		},
	) );
} );

function webhook( $request ) {
	$payload = $request->get_json_params();
	if ( ! is_array( $payload ) ) { return new \WP_Error( 'invalid_payload', 'Expected a JSON object.', array( 'status' => 400 ) ); }
	if ( 'ticket.created' !== ( $payload['event'] ?? '' ) || ! captain_ticket( $payload['data']['title'] ?? '' ) ) {
		return new \WP_REST_Response( array( 'status' => 'ignored' ), 200 );
	}
	$id = (string) ( $payload['data']['id'] ?? '' );
	if ( ! preg_match( '/^[a-zA-Z0-9_-]{1,100}$/', $id ) ) {
		return new \WP_Error( 'invalid_ticket', 'Missing or invalid ticket ID.', array( 'status' => 400 ) );
	}
	$key = job_key( $id );
	$job = get_option( $key );
	if ( ! $job ) {
		add_option( $key, array( 'ticket_id' => $id, 'status' => 'pending', 'attempts' => 0, 'updated' => time() ), '', false );
	}
	if ( ! $job || 'pending' === ( $job['status'] ?? '' ) ) {
		if ( ! wp_next_scheduled( HOOK, array( $id ) ) ) { wp_schedule_single_event( time() + 60, HOOK, array( $id ) ); }
	}
	return new \WP_REST_Response( array( 'status' => 'accepted' ), 202 );
}

add_action( HOOK, __NAMESPACE__ . '\\process' );
function process( $id ) {
	$key = job_key( $id );
	$job = get_option( $key );
	if ( ! $job || in_array( $job['status'], array( 'sent', 'sending', 'ignored', 'review' ), true ) ) { return; }
	// A persistent lock fails closed after a process crash; never risk duplicate emails.
	$lock = $key . '_lock';
	if ( ! add_option( $lock, time(), '', false ) ) { return; }
	$team_lock = null;
	try {
		$ticket = api( 'tickets/' . rawurlencode( $id ) )['data'];
		if ( ! captain_ticket( $ticket['title'] ?? '' ) ) { $job['status'] = 'ignored'; return; }
		$transaction = api( 'transactions/' . rawurlencode( $ticket['transaction_id'] ?? '' ) )['data'];
		if ( CAMPAIGN_CODE !== ( $transaction['campaign_code'] ?? '' ) ) { $job['status'] = 'ignored'; return; }
		$campaign = (int) ( $transaction['campaign_id'] ?? 0 );
		if ( ! $campaign ) { throw new \RuntimeException( 'The transaction has no campaign ID.' ); }
		$match = resolve_captain( $ticket, $transaction, collection( "campaigns/$campaign/members" ), collection( "campaigns/$campaign/teams" ) );
		$team_id = (string) $match['team']['id'];
		$invite_key = 'ymc_captain_team_' . hash( 'sha256', $team_id );
		$team_lock_key = $invite_key . '_lock';
		if ( ! add_option( $team_lock_key, time(), '', false ) ) { throw new \RuntimeException( 'This team invitation is already being processed. Retrying.' ); }
		$team_lock = $team_lock_key;
		$invite = get_option( $invite_key );
		if ( $invite && $invite['member_id'] !== $match['member_id'] ) {
			$job['status'] = 'review'; $job['message'] = 'This team already has a different captain invitation.'; return;
		}
		if ( ! $invite ) {
			$invite = array(
				'team_id' => $team_id,
				'campaign_id' => $campaign, 'team_name' => sanitize_text_field( $match['team']['name'] ),
				'email' => $match['email'], 'member_id' => $match['member_id'], 'status' => 'ready',
			);
			// Atomic creation also prevents two tickets claiming the same captain slot.
			if ( ! add_option( $invite_key, $invite, '', false ) ) { throw new \RuntimeException( 'Another invitation is being prepared for this team. Retrying.' ); }
		}
		$job['team'] = $invite['team_name'];
		$job['invite_key'] = $invite_key;
		if ( in_array( $invite['status'], array( 'sent', 'sending' ), true ) ) {
			$job['status'] = $invite['status']; return;
		}
		// Refresh unsent details from the verified registration before delivery.
		$invite['email'] = $match['email'];
		$invite['team_name'] = sanitize_text_field( $match['team']['name'] );
		update_option( $invite_key, $invite, false );
		if ( empty( config()['send_email'] ) ) {
			$job['status'] = 'ready'; $job['message'] = 'Invitation prepared. Email delivery is disabled.'; return;
		}
		$invite['status'] = 'sending';
		update_option( $invite_key, $invite, false );
		$job['status'] = 'sending'; update_option( $key, $job, false );
		$message = invitation_email( $invite['team_name'] );
		$sent = wp_mail( $invite['email'], 'Invite your players — ' . $invite['team_name'], $message, array( 'Content-Type: text/html; charset=UTF-8' ) );
		$invite['status'] = $sent ? 'sent' : 'ready';
		update_option( $invite_key, $invite, false );
		if ( ! $sent ) { $job['status'] = 'pending'; throw new \RuntimeException( 'Email transport rejected the message. Retrying.' ); }
		$job['status'] = 'sent'; $job['message'] = 'Invitation accepted by the email transport; inbox delivery is not confirmed.';
	} catch ( \Throwable $error ) {
		$job['attempts'] = ( $job['attempts'] ?? 0 ) + 1;
		$job['status'] = $job['attempts'] >= 24 ? 'review' : 'pending';
		$job['message'] = $error->getMessage();
		if ( 'pending' === $job['status'] ) { wp_schedule_single_event( time() + 300, HOOK, array( $id ) ); }
	} finally {
		if ( $team_lock ) { delete_option( $team_lock ); }
		$job['updated'] = time(); update_option( $key, $job, false ); delete_option( $lock );
	}
}

/** Public invitation: show team context honestly; never pretend the GB team is selected. */
add_action( 'template_redirect', function () {
	if ( ! isset( $_GET['ymc_join'] ) ) { return; }
	if ( ! is_string( $_GET['ymc_join'] ) ) { wp_die( 'Invalid team code.', '', array( 'response' => 400 ) ); }
	$code = strtoupper( sanitize_text_field( wp_unslash( $_GET['ymc_join'] ) ) );
	$invite = false; $error = ''; $full = false;
	if ( '1' !== $code ) {
		$key = preg_match( '/^[A-F0-9]{12}$/', $code ) ? get_option( 'ymc_captain_code_' . hash( 'sha256', $code ) ) : false;
		$invite = $key ? get_option( $key ) : false;
		if ( ! $invite ) { $error = 'That team code was not found. Please check the code your captain sent.'; }
		else {
			try {
				$team = api( 'campaigns/' . (int) $invite['campaign_id'] . '/teams/' . rawurlencode( $invite['team_id'] ) )['data'];
				$invite['team_name'] = sanitize_text_field( $team['name'] );
				$full = ! isset( $team['members'] ) || (int) $team['members'] >= 10;
			} catch ( \Throwable $e ) { $error = 'We could not check this team right now. Please try again shortly.'; }
		}
	}
	$closed = time() >= ( new \DateTimeImmutable( '2027-02-02 00:00:00', new \DateTimeZone( 'America/New_York' ) ) )->getTimestamp();
	nocache_headers(); header( 'X-Robots-Tag: noindex, nofollow', true );
	wp_enqueue_style( 'ymc-team-invitation', get_theme_file_uri( 'assets/css/team-invitation.css' ), array(), filemtime( get_theme_file_path( 'assets/css/team-invitation.css' ) ) );
	require __DIR__ . '/team-invitation-view.php';
	exit;
} );

add_action( 'admin_menu', function () {
	add_options_page( 'Captain Invitations', 'Captain Invitations', 'manage_options', 'ymc-captain-invitations', __NAMESPACE__ . '\\settings_page' );
} );

function settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$settings = config();
	if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
		check_admin_referer( 'ymc_captain_settings' );
		foreach ( array( 'api_key', 'signing_secret' ) as $field ) {
			$value = trim( sanitize_text_field( wp_unslash( $_POST[$field] ?? '' ) ) );
			if ( $value ) { $settings[$field] = $value; }
		}
		$settings['send_email'] = ! empty( $_POST['send_email'] );
		update_option( CONFIG, $settings, false );
		if ( $settings['send_email'] ) {
			global $wpdb;
			$pending = $wpdb->get_col( $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name LIKE %s AND option_name NOT LIKE %s", $wpdb->esc_like( JOB_PREFIX ) . '%', '%' . $wpdb->esc_like( '_lock' ) ) );
			foreach ( $pending as $value ) {
				$job = maybe_unserialize( $value );
				if ( is_array( $job ) && 'ready' === ( $job['status'] ?? '' ) && ! wp_next_scheduled( HOOK, array( $job['ticket_id'] ) ) ) {
					wp_schedule_single_event( time() + 60, HOOK, array( $job['ticket_id'] ) );
				}
			}
		}
		echo '<div class="notice notice-success"><p>Settings saved.</p></div>';
	}
	echo '<div class="wrap"><h1>Captain Invitations</h1><p>After a successful adult ticket purchase labeled (Captain Only), email the matched registered captain their team name, the Givebutter registration link, and instructions to forward to players. No team code is required. Players select their team in Givebutter.</p><form method="post">';
	wp_nonce_field( 'ymc_captain_settings' );
	foreach ( array( 'api_key' => 'Givebutter API key', 'signing_secret' => 'Webhook signing secret' ) as $field => $label ) {
		echo '<p><label>' . esc_html( $label ) . '<br><input class="regular-text" type="password" name="' . esc_attr( $field ) . '" autocomplete="new-password" value=""></label> ' . ( empty( $settings[$field] ) ? 'Not configured' : 'Configured — leave blank to keep' ) . '</p>';
	}
	echo '<p><label><input type="checkbox" name="send_email" value="1" ' . checked( ! empty( $settings['send_email'] ), true, false ) . '> Automatically send captain invitation emails (leave off during initial verification)</label></p>';
	submit_button(); echo '</form><h2>Givebutter webhook setup</h2><p>Create a webhook for <code>ticket.created</code> at this URL, then save its signing secret above:</p><code>' . esc_html( rest_url( 'ymc/v1/captain-invitations' ) ) . '</code><p>The local URL cannot receive public Givebutter webhooks. A deployed HTTPS site and working scheduled tasks/email delivery are required.</p><h2>Recent captain tickets</h2>';
	global $wpdb;
	$rows = $wpdb->get_col( $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name LIKE %s AND option_name NOT LIKE %s ORDER BY option_id DESC LIMIT 30", $wpdb->esc_like( JOB_PREFIX ) . '%', '%' . $wpdb->esc_like( '_lock' ) ) );
	echo '<table class="widefat"><thead><tr><th>Ticket</th><th>Team</th><th>Status</th><th>Details</th></tr></thead><tbody>';
	foreach ( $rows as $value ) {
		$job = maybe_unserialize( $value );
		if ( ! is_array( $job ) ) { continue; }
		echo '<tr><td>' . esc_html( $job['ticket_id'] ) . '</td><td>' . esc_html( $job['team'] ?? 'Pending match' ) . '</td><td>' . esc_html( $job['status'] ) . '</td><td>' . esc_html( $job['message'] ?? '' ) . '</td></tr>';
	}
	echo '</tbody></table></div>';
}
