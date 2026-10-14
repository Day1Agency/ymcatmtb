<?php
/** Run only through WP-CLI eval-file on LOCAL; HTTP and email are mocked. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) { exit; }
if ( 'localhost' !== wp_parse_url( home_url(), PHP_URL_HOST ) ) { WP_CLI::error( 'Localhost only.' ); }

$original = get_option( \YMC\CaptainInvitations\CONFIG, null );
$ticket = array( 'id' => 'ymc-test-captain', 'transaction_id' => 'ymc-test-payment', 'title' => 'Adult Tournament — Blue (Captain Only)', 'email' => 'captain@example.invalid' );
$transaction = array( 'id' => 'ymc-test-payment', 'campaign_code' => 'RMSQHT', 'campaign_id' => 9999999, 'status' => 'succeeded' );
$team = array( 'id' => 'ymc-test-team', 'name' => 'Test Team', 'url' => 'https://givebutter.com/ymc-adult-player-registration/test-team', 'members' => 1 );
$member = array( 'id' => 'ymc-test-member', 'email' => 'captain@example.invalid', 'url' => $team['url'] . '/captain' );
$job_key = \YMC\CaptainInvitations\job_key( $ticket['id'] );
$invite_key = 'ymc_captain_team_' . hash( 'sha256', $team['id'] );
$sent = array(); $checks = 0;
$assert = function ( $condition, $label ) use ( &$checks ) {
	if ( ! $condition ) { throw new RuntimeException( $label ); }
	$checks++; WP_CLI::log( 'PASS ' . $label );
};
$rejects = function ( $fn ) { try { $fn(); return false; } catch ( RuntimeException $e ) { return true; } };
$http = function ( $pre, $args, $url ) use ( &$ticket, &$transaction, &$member, &$team ) {
	$data = null;
	if ( str_contains( $url, '/tickets/' ) ) { $data = $ticket; }
	elseif ( str_contains( $url, '/transactions/' ) ) { $data = $transaction; }
	elseif ( str_contains( $url, '/members?' ) ) { $data = array( $member ); }
	elseif ( str_contains( $url, '/teams?' ) ) { $data = array( $team ); }
	else { return new WP_Error( 'test_blocked', 'All other HTTP requests blocked by test.' ); }
	$body = isset( $data['id'] ) ? $data : array( 'data' => $data, 'links' => array( 'next' => null ) );
	return array( 'response' => array( 'code' => 200 ), 'body' => wp_json_encode( $body ) );
};
$mail = function ( $pre, $atts ) use ( &$sent ) { $sent[] = $atts; return true; };
add_filter( 'pre_http_request', $http, 10, 3 );
add_filter( 'pre_wp_mail', $mail, 10, 2 );
try {
	update_option( \YMC\CaptainInvitations\CONFIG, array( 'api_key' => 'test-only', 'signing_secret' => 'test-secret', 'send_email' => false ), false );
	$assert( \YMC\CaptainInvitations\captain_ticket( $ticket['title'] ), 'Recognizes configured captain tickets' );
	$assert( \YMC\CaptainInvitations\captain_ticket( 'Adult Tournament Test Ticket (Captain Only)' ), 'Recognizes the real test captain ticket label' );
	$assert( ! \YMC\CaptainInvitations\captain_ticket( 'Adult Tournament (Captain Only) Guest' ), 'Requires the captain label at the end of the title' );
	$assert( ! \YMC\CaptainInvitations\captain_ticket( 'Adult Tournament — Player' ), 'Does not invite ordinary player tickets' );
	$match = \YMC\CaptainInvitations\resolve_captain( $ticket, $transaction, array( $member ), array( $team ) );
	$assert( 'captain@example.invalid' === $match['email'], 'Uses the registered captain email' );
	$bad_member = $member; $bad_member['email'] = 'payer@example.invalid';
	$assert( $rejects( fn() => \YMC\CaptainInvitations\resolve_captain( $ticket, $transaction, array( $bad_member ), array( $team ) ) ), 'Does not guess a captain from payer or first member' );
	$assert( $rejects( fn() => \YMC\CaptainInvitations\resolve_captain( $ticket, $transaction, array( $member, $member ), array( $team ) ) ), 'Ambiguous membership fails closed' );
	$prefix_team = $team; $prefix_team['url'] .= '-another';
	$assert( $rejects( fn() => \YMC\CaptainInvitations\resolve_captain( $ticket, $transaction, array( $member ), array( $prefix_team ) ) ), 'Team path prefixes cannot misassign invitations' );
	$bad_transaction = $transaction; $bad_transaction['campaign_code'] = 'OTHER';
	$assert( $rejects( fn() => \YMC\CaptainInvitations\resolve_captain( $ticket, $bad_transaction, array( $member ), array( $team ) ) ), 'Rejects other campaigns' );
	$bad_transaction = $transaction; $bad_transaction['status'] = 'failed';
	$assert( $rejects( fn() => \YMC\CaptainInvitations\resolve_captain( $ticket, $bad_transaction, array( $member ), array( $team ) ) ), 'Rejects unsuccessful payments' );
	$bad_transaction = $transaction; $bad_transaction['transactions'] = array( array( 'refunded' => true ) );
	$assert( $rejects( fn() => \YMC\CaptainInvitations\resolve_captain( $ticket, $bad_transaction, array( $member ), array( $team ) ) ), 'Rejects refunded purchases' );
	$request = new WP_REST_Request( 'POST', '/ymc/v1/captain-invitations' );
	$request->set_header( 'Content-Type', 'application/json' );
	$request->set_body( wp_json_encode( array( 'event' => 'ticket.created', 'data' => $ticket ) ) );
	$response = rest_do_request( $request );
	$assert( 403 === $response->get_status(), 'Rejects unsigned webhooks' );
	$request->set_header( 'Signature', 'test-secret' );
	$response = rest_do_request( $request );
	$assert( 202 === $response->get_status(), 'Accepts authenticated captain ticket event' );
	\YMC\CaptainInvitations\process( $ticket['id'] );
	$invite = get_option( $invite_key );
	$assert( 'ready' === get_option( $job_key )['status'] && 0 === count( $sent ), 'Preview prepares link without sending email' );
	$assert( ! isset( $invite['code'] ), 'Simplified flow does not generate a team code' );
	$settings = \YMC\CaptainInvitations\config(); $settings['send_email'] = true;
	update_option( \YMC\CaptainInvitations\CONFIG, $settings, false );
	\YMC\CaptainInvitations\process( $ticket['id'] );
	$assert( 1 === count( $sent ) && 'captain@example.invalid' === $sent[0]['to'], 'Enabled delivery sends to captain registered email' );
	$assert( str_contains( $sent[0]['message'], \YMC\CaptainInvitations\REGISTRATION ) && ! str_contains( $sent[0]['message'], 'ymc_join' ) && str_contains( $sent[0]['message'], 'does not automatically select' ), 'Email links directly to Givebutter and explains manual team selection' );
	$assert( str_contains( $sent[0]['message'], 'Test Team' ) && str_contains( $sent[0]['message'], 'Forward this email' ), 'Invitation includes the team name and forwarding instructions' );
	$assert( str_contains( \YMC\CaptainInvitations\invitation_email( '<script>alert(1)</script>' ), '&lt;script&gt;' ), 'Team names are escaped in HTML email' );
	rest_do_request( $request ); \YMC\CaptainInvitations\process( $ticket['id'] );
	$assert( 1 === count( $sent ), 'Webhook redelivery does not duplicate the invitation email' );
	WP_CLI::success( "$checks checks passed; all email and API traffic mocked." );
} finally {
	$invite = get_option( $invite_key );
	if ( ! empty( $invite['code'] ) ) { delete_option( 'ymc_captain_code_' . hash( 'sha256', $invite['code'] ) ); }
	delete_option( $invite_key . '_lock' );
	delete_option( $invite_key ); delete_option( $job_key ); delete_option( $job_key . '_lock' );
	wp_clear_scheduled_hook( \YMC\CaptainInvitations\HOOK, array( $ticket['id'] ) );
	if ( null === $original ) { delete_option( \YMC\CaptainInvitations\CONFIG ); } else { update_option( \YMC\CaptainInvitations\CONFIG, $original, false ); }
	remove_filter( 'pre_http_request', $http, 10 ); remove_filter( 'pre_wp_mail', $mail, 10 );
}
