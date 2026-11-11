<?php
/**
 * A staff dashboard for captain team requests, served outside wp-admin.
 *
 * Yossi needs to see who has asked for a team and to chase them, but not to
 * learn WordPress to do it. This renders the same records and the same controls
 * as the admin list at a plain URL, behind its own password, so the people who
 * run the tournament never have to go through wp-admin at all.
 *
 * Deliberately not a WordPress page: the live database is never rebuilt from
 * local, so a page record would have to be created by hand on the server and
 * would be lost by the next content sync. A code-level route ships with a push.
 */
if (!defined('ABSPATH')) exit;

/** The public path, without slashes. Change here and the links follow. */
const YMC_CAPTAIN_DASH_PATH = 'team-dashboard';

/** How long a sign-in lasts before the password is asked for again. */
const YMC_CAPTAIN_DASH_LIFETIME = 12 * HOUR_IN_SECONDS;

/** Wrong passwords allowed per address before the form stops answering. */
const YMC_CAPTAIN_DASH_TRIES = 8;

/**
 * Wrong passwords allowed from everyone together inside a quarter hour.
 *
 * Set well above anything a few staff could reach by mistyping, so it only
 * trips when someone is guessing from more than one address.
 */
const YMC_CAPTAIN_DASH_TRIES_ALL = 25;

/**
 * The key every session cookie and CSRF token is signed with.
 *
 * Generated once and kept in options rather than derived from the WordPress
 * salts, so changing the password or rotating this value logs everyone out
 * without touching wp-config.
 */
function ymc_captain_dash_secret() {
 $secret = (string) get_option('ymc_captain_dash_secret', '');
 if (32 > strlen($secret)) {
  $secret = wp_generate_password(64, false, false);
  update_option('ymc_captain_dash_secret', $secret, false);
 }
 return $secret;
}

function ymc_captain_dash_cookie_name() {
 return 'ymc_captain_dash';
}

function ymc_captain_dash_url($args = []) {
 $url = home_url('/' . YMC_CAPTAIN_DASH_PATH . '/');
 return $args ? add_query_arg($args, $url) : $url;
}

/** True once a password has been set; until then the dashboard stays shut. */
function ymc_captain_dash_has_password() {
 return '' !== (string) get_option('ymc_captain_dash_password', '');
}

/**
 * Issues the signed session cookie.
 *
 * Stateless on purpose: the expiry travels in the cookie and is signed, so
 * there is no server-side session list to grow, expire or get out of step.
 */
function ymc_captain_dash_start_session() {
 $expiry = time() + YMC_CAPTAIN_DASH_LIFETIME;
 $value  = $expiry . '.' . hash_hmac('sha256', (string) $expiry, ymc_captain_dash_secret());
 setcookie(ymc_captain_dash_cookie_name(), $value, [
  'expires'  => $expiry,
  'path'     => '/',
  'secure'   => is_ssl(),
  'httponly' => true,
  'samesite' => 'Lax',
 ]);
 $_COOKIE[ymc_captain_dash_cookie_name()] = $value;
}

function ymc_captain_dash_end_session() {
 setcookie(ymc_captain_dash_cookie_name(), '', [
  'expires'  => time() - 3600,
  'path'     => '/',
  'secure'   => is_ssl(),
  'httponly' => true,
  'samesite' => 'Lax',
 ]);
 unset($_COOKIE[ymc_captain_dash_cookie_name()]);
}

/** The signed expiry from a valid cookie, or 0. */
function ymc_captain_dash_session_expiry() {
 $raw = (string) ($_COOKIE[ymc_captain_dash_cookie_name()] ?? '');
 if (false === strpos($raw, '.')) return 0;
 list($expiry, $sig) = explode('.', $raw, 2);
 if (!ctype_digit($expiry) || (int) $expiry < time()) return 0;
 /* Timing-safe, so a wrong signature cannot be narrowed down by how long the
    comparison takes. */
 if (!hash_equals(hash_hmac('sha256', $expiry, ymc_captain_dash_secret()), $sig)) return 0;
 return (int) $expiry;
}

/**
 * Who may see the dashboard.
 *
 * An administrator already signed into WordPress is let through without a
 * second password: they can read the same records in wp-admin anyway, and
 * making them type a second one only invites it being written down.
 */
function ymc_captain_dash_may_view() {
 if (current_user_can('manage_options')) return true;
 return 0 !== ymc_captain_dash_session_expiry();
}

/**
 * A CSRF token tied to the viewer.
 *
 * WordPress nonces key off the logged-in user, so every password-only viewer
 * would share one token and it would protect nothing. This binds the token to
 * the session cookie instead, and to the action being performed.
 */
function ymc_captain_dash_token($action) {
 $seed = current_user_can('manage_options')
  ? 'wp:' . get_current_user_id()
  : 'session:' . ymc_captain_dash_session_expiry();
 return hash_hmac('sha256', $action . '|' . $seed, ymc_captain_dash_secret());
}

function ymc_captain_dash_check_token($action, $token) {
 return hash_equals(ymc_captain_dash_token($action), (string) $token);
}

/** Attempts are counted per address so one visitor cannot lock out the rest. */
function ymc_captain_dash_throttle_key() {
 $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
 return 'ymc_dash_tries_' . md5($ip);
}

/**
 * The address a failure is recorded against.
 *
 * REMOTE_ADDR only. A forwarded-for header is written by whoever sent the
 * request, so trusting it would let one attacker claim a fresh address on
 * every try and never be counted at all.
 */
function ymc_captain_dash_ip() {
 return (string) ($_SERVER['REMOTE_ADDR'] ?? '');
}

/**
 * True while this address is shut out.
 *
 * Per-address alone is not enough: eight tries then a new address repeats
 * forever, and nothing in the count ever notices. The site-wide tally below is
 * what sees that pattern, so either ceiling closes the form.
 */
function ymc_captain_dash_locked_out() {
 if (YMC_CAPTAIN_DASH_TRIES <= (int) get_transient(ymc_captain_dash_throttle_key())) return true;
 return YMC_CAPTAIN_DASH_TRIES_ALL <= (int) get_transient('ymc_dash_tries_all');
}

/**
 * Records a wrong password against this address and against the site.
 *
 * The two counters have deliberately different windows. An address that fails
 * is held for an hour, because a real person who mistypes eight times will
 * ask someone rather than keep going. The site-wide window is short, so a
 * burst closes the door but normal work resumes on its own.
 */
function ymc_captain_dash_count_failure() {
 $key = ymc_captain_dash_throttle_key();
 set_transient($key, (int) get_transient($key) + 1, HOUR_IN_SECONDS);
 set_transient('ymc_dash_tries_all', (int) get_transient('ymc_dash_tries_all') + 1, 15 * MINUTE_IN_SECONDS);
 ymc_captain_dash_log_failure();
}

/**
 * Keeps the last 50 wrong passwords, newest first.
 *
 * Stored rather than only counted, because a lockout that leaves no trace tells
 * nobody afterwards whether it was one person fumbling or someone working
 * through a list.
 */
function ymc_captain_dash_log_failure() {
 $log = get_option('ymc_captain_dash_failures', []);
 if (!is_array($log)) $log = [];
 array_unshift($log, ['at' => time(), 'ip' => ymc_captain_dash_ip()]);
 update_option('ymc_captain_dash_failures', array_slice($log, 0, 50), false);
}

/** Failures inside the last day, for the line shown on the dashboard. */
function ymc_captain_dash_recent_failures() {
 $log = get_option('ymc_captain_dash_failures', []);
 if (!is_array($log)) return [];
 $since = time() - DAY_IN_SECONDS;
 return array_values(array_filter($log, function ($row) use ($since) {
  return is_array($row) && (int) ($row['at'] ?? 0) >= $since;
 }));
}

/* ------------------------------------------------------------------ actions */

/**
 * Cancels a request and frees its colour.
 *
 * Mirrors the admin screen's Cancel button, including the holder check: a stale
 * record must not release a colour that a later captain has since claimed.
 */
function ymc_captain_dash_cancel($id) {
 if (!$id || 'ymc_captain_request' !== get_post_type($id)) return 'badid';
 $colour = (string) get_post_meta($id, 'ymc_colour', true);
 if ($colour && ymc_captain_colour_holder($colour) === (string) $id) ymc_captain_release_colour($colour);
 update_post_meta($id, 'ymc_status', 'cancelled');
 /* Recorded the same way the admin screen records it, so the Details button
    reads alike wherever the cancelling was done from. Password-only staff have
    no WordPress account to name, so the dashboard says so plainly. */
 update_post_meta($id, 'ymc_outcome_reason', sprintf(
  'Cancelled by %s on %s%s.',
  current_user_can('manage_options') ? (wp_get_current_user()->display_name ?: 'a staff member') : 'staff from the team dashboard',
  date_i18n('j M Y, g:i a'),
  $colour ? ', releasing '.$colour : ''
 ));
 return 'cancelled';
}

/** Sends the team link by hand, for a team not named after its captain. */
function ymc_captain_dash_send($id, $team, $url) {
 if (!$id || 'ymc_captain_request' !== get_post_type($id)) return 'badid';
 if ('' === $team || '' === $url) return 'incomplete';
 return ymc_captain_send_link($id, $team, $url) ? 'sent' : 'failed';
}

/**
 * Runs whichever control was submitted, then redirects.
 *
 * Every action ends in a redirect so a refresh cannot fire it twice - resending
 * an email or releasing a colour on a browser reload is exactly the kind of
 * accident this screen should not allow.
 */
function ymc_captain_dash_handle_action() {
 $action = sanitize_key(wp_unslash($_POST['ymc_dash_action'] ?? ''));
 if ('' === $action) return;
 if (!ymc_captain_dash_may_view()) return;
 if (!ymc_captain_dash_check_token($action, wp_unslash($_POST['ymc_dash_token'] ?? ''))) {
  wp_safe_redirect(ymc_captain_dash_url(['msg' => 'expired']));
  exit;
 }
 $id = absint($_POST['id'] ?? 0);
 if ('resend' === $action)      $result = ymc_captain_resend_link($id);
 elseif ('cancel' === $action)  $result = ymc_captain_dash_cancel($id);
 elseif ('send' === $action)    $result = ymc_captain_dash_send($id,
   sanitize_text_field(wp_unslash($_POST['team'] ?? '')),
   esc_url_raw(wp_unslash($_POST['url'] ?? '')));
 else return;
 wp_safe_redirect(ymc_captain_dash_url(['msg' => $result]));
 exit;
}

/** What each outcome says to the person who triggered it. */
function ymc_captain_dash_notice($key) {
 $notices = [
  'sent'       => ['ok',  'The team link was emailed.'],
  'cancelled'  => ['ok',  'The request was cancelled and its color released.'],
  'failed'     => ['bad', 'That email could not be sent. The mail setup needs looking at.'],
  'noteam'     => ['bad', 'No team has been created for that request yet, so there is no link to send.'],
  'noemail'    => ['bad', 'That request has no valid email address on it.'],
  'incomplete' => ['bad', 'Both a team name and a team link are needed.'],
  'badid'      => ['bad', 'That request could not be found.'],
  'expired'    => ['bad', 'That form had expired. Please try again.'],
  'wrongpass'  => ['bad', 'That password was not right.'],
  'lockedout'  => ['bad', 'Too many wrong passwords. Please wait, then try again.'],
 ];
 return $notices[$key] ?? null;
}

/* ------------------------------------------------------------------ chrome */

/**
 * Opens a standalone HTML document.
 *
 * The theme's header and footer are left out on purpose: this is a staff tool,
 * not a page of the website, and the site navigation around it would only
 * invite people to wander off into the public site mid-task.
 */
function ymc_captain_dash_head($title) {
 nocache_headers();
 header('X-Robots-Tag: noindex, nofollow', true);
 header('Content-Type: text/html; charset=utf-8');
 ?><!doctype html>
<html lang="en"><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?php echo esc_html($title); ?> &middot; YMC</title>
<style>
:root{--ink:#241046;--gold:#d4af37;--line:#e4e0ec;--bg:#f6f4fa;--mute:#6b6480;--bad:#b32d2e;--ok:#1d6f42}
*{box-sizing:border-box}
body{margin:0;padding:0 16px 64px;background:var(--bg);color:var(--ink);
 font:16px/1.55 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif}
.wrap{max-width:1180px;margin:0 auto}
header.bar{display:flex;flex-wrap:wrap;gap:12px;align-items:baseline;justify-content:space-between;
 padding:28px 0 20px;border-bottom:3px solid var(--gold);margin-bottom:28px}
header.bar h1{margin:0;font-size:25px;letter-spacing:-.01em}
header.bar p{margin:0;color:var(--mute);font-size:14px}
a{color:var(--ink)}
.note{padding:12px 16px;border-radius:8px;margin:0 0 22px;font-size:15px}
.note.ok{background:#e7f5ec;border:1px solid #b6ddc4;color:var(--ok)}
.note.bad{background:#fdecec;border:1px solid #f1bdbd;color:var(--bad)}
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin:0 0 30px}
.card{background:#fff;border:1px solid var(--line);border-radius:10px;padding:16px 18px}
.card b{display:block;font-size:30px;line-height:1.1;letter-spacing:-.02em}
.card span{color:var(--mute);font-size:13px;text-transform:uppercase;letter-spacing:.07em}
h2{font-size:18px;margin:34px 0 14px}
h2:first-of-type{margin-top:0}
.panel{background:#fff;border:1px solid var(--line);border-radius:10px;overflow:hidden}
table{width:100%;border-collapse:collapse;font-size:15px}
th{text-align:left;font-size:12px;text-transform:uppercase;letter-spacing:.07em;color:var(--mute);
 padding:12px 16px;background:#faf9fc;border-bottom:1px solid var(--line);white-space:nowrap}
td{padding:14px 16px;border-bottom:1px solid var(--line);vertical-align:top}
tr:last-child td{border-bottom:0}
.sub{display:block;color:var(--mute);font-size:13px}
.muted{color:var(--mute);font-size:13px}
.dot{display:inline-block;width:12px;height:12px;border-radius:50%;border:1px solid rgba(0,0,0,.2);
 margin-right:7px;vertical-align:-1px}
.tag{display:inline-block;padding:3px 9px;border-radius:999px;font-size:11px;font-weight:700;
 text-transform:uppercase;letter-spacing:.07em;white-space:nowrap}
.tag.free{background:#eceaf2;color:var(--mute)}
.tag.reserved{background:var(--gold);color:var(--ink)}
.tag.taken{background:var(--ink);color:#fff}
.tag.off{background:#fff;color:var(--mute);border:1px dashed var(--line)}
form.inline{display:inline}
button{font:inherit;cursor:pointer;border-radius:7px;padding:7px 13px;border:1px solid var(--ink);
 background:var(--ink);color:#fff;font-size:14px}
button.ghost{background:#fff;color:var(--ink)}
button.danger{background:#fff;color:var(--bad);border-color:#e2a9a9}
button:hover{opacity:.88}
details.send{margin-top:10px}
details.send summary{cursor:pointer;font-size:13px;color:var(--mute)}
details.send input{font:inherit;padding:7px 9px;border:1px solid var(--line);border-radius:6px;
 width:100%;max-width:330px;margin:7px 0}
.login{max-width:400px;margin:13vh auto 0;background:#fff;border:1px solid var(--line);
 border-radius:12px;padding:34px}
.login h1{margin:0 0 6px;font-size:22px}
.login p.lede{margin:0 0 22px;color:var(--mute);font-size:14px}
.login input[type=password]{font:inherit;width:100%;padding:11px 13px;border:1px solid var(--line);
 border-radius:7px;margin-bottom:14px}
.login button{width:100%;padding:11px}
.empty{padding:40px 16px;text-align:center;color:var(--mute)}
@media(max-width:760px){
 .panel{overflow-x:auto}
 table{min-width:720px}
}
</style>
<link rel="stylesheet" href="<?php echo esc_url(plugins_url('dashboard.css', __FILE__) . '?v=' . filemtime(__DIR__ . '/dashboard.css')); ?>">
</head><body class="<?php echo ymc_captain_dash_may_view() ? 'staff-dashboard' : 'staff-login'; ?>">
<?php if (ymc_captain_dash_may_view()) : ?>
<a class="skip-link" href="#dashboard-main">Skip to dashboard</a>
<aside class="dash-sidebar"><a class="dash-brand" href="<?php echo esc_url(home_url('/')); ?>"><img src="<?php echo esc_url(get_theme_file_uri('assets/images/ymc-event-logo-transparent.png')); ?>" alt="YMC at M&amp;T"><span>YMC at M&amp;T<small>TOURNAMENT STAFF</small></span></a>
<p class="nav-label">WORKSPACE</p><?php ymc_captain_dash_nav(); ?>
<div class="sidebar-bottom"><span>ADULT TOURNAMENT</span><p>Keep your teams<br>moving forward.</p><a href="<?php echo esc_url(home_url('/')); ?>">View website &nearr;</a></div></aside>
<?php endif; ?><main class="wrap" id="dashboard-main">
<?php }

/** Which of the two screens is being asked for. */
function ymc_captain_dash_view() {
 return 'team-requests' === sanitize_key(wp_unslash($_GET['view'] ?? '')) ? 'team-requests' : 'main';
}

/**
 * The sidebar: two places to be, each with its own parts underneath.
 *
 * The two are different jobs - people who have registered against people still
 * waiting for a team - so they are pages rather than two more sections on one
 * long scroll. Only the open one lists its parts; showing both sets of anchors
 * would offer links that go nowhere from the page you are on.
 */
function ymc_captain_dash_nav() {
 $view  = ymc_captain_dash_view();
 $pages = [
  'main' => [
   'label' => 'Team registrations',
   'url'   => ymc_captain_dash_url(),
   'parts' => [
    '#overview'      => 'Overview',
    '#requests'      => 'Team registrations',
    '#registrations' => 'Player registrations',
    '#team-colors'   => 'Team colors',
   ],
  ],
  'team-requests' => [
   'label' => 'Team requests',
   'url'   => ymc_captain_dash_url(['view' => 'team-requests']),
   'parts' => ['#placements' => 'Who is waiting'],
  ],
 ];
 echo '<nav aria-label="Dashboard">';
 foreach ($pages as $key => $page) {
  printf('<a class="nav-main%s" href="%s"%s>%s</a>',
   $key === $view ? ' active' : '', esc_url($page['url']),
   $key === $view ? ' aria-current="page"' : '', esc_html($page['label']));
  if ($key !== $view) continue;
  echo '<span class="nav-parts">';
  foreach ($page['parts'] as $anchor => $label) {
   printf('<a class="nav-part" href="%s">%s</a>', esc_attr($anchor), esc_html($label));
  }
  echo '</span>';
 }
 echo '</nav>';
}

function ymc_captain_dash_foot() {
 echo '</main><script src="' . esc_url(plugins_url('dashboard.js', __FILE__) . '?v=' . filemtime(__DIR__ . '/dashboard.js')) . '" defer></script></body></html>';
}

/** Prints the notice for ?msg=, if there is one to print. */
function ymc_captain_dash_print_notice() {
 if (empty($_GET['msg'])) return;
 $notice = ymc_captain_dash_notice(sanitize_key(wp_unslash($_GET['msg'])));
 if (!$notice) return;
 printf('<p class="note %s">%s</p>', esc_attr($notice[0]), esc_html($notice[1]));
}

/* ------------------------------------------------------------------- screens */

function ymc_captain_dash_login_screen() {
 ymc_captain_dash_head('Sign in');
 echo '<div class="login"><h1>Team requests</h1>';
 if (!ymc_captain_dash_has_password()) {
  echo '<p class="lede">This dashboard has no password set yet, so it is closed. '
   . 'An administrator can set one under <strong>Captain Requests &rarr; Settings</strong> in WordPress.</p></div>';
  ymc_captain_dash_foot();
  return;
 }
 echo '<p class="lede">Enter the staff password to see who has requested a team.</p>';
 ymc_captain_dash_print_notice();
 echo '<form method="post"><input type="hidden" name="ymc_dash_login" value="1">'
  . '<input type="password" name="password" placeholder="Password" autocomplete="current-password" autofocus required>'
  . '<button type="submit">Sign in</button></form></div>';
 ymc_captain_dash_foot();
}

/**
 * The answer a buyer gave to one of the registration questions, or ''.
 *
 * Matched on a few words of the question rather than the whole of it, so
 * rewording "Are you the team captain?" in Givebutter does not quietly stop
 * this working. Field ids would be stabler, but they are not visible anywhere
 * staff can read them.
 */
function ymc_captain_ticket_answer($ticket, $needle) {
 foreach ((array) ($ticket['custom_fields'] ?? []) as $field) {
  if (!is_array($field)) continue;
  if (false !== stripos((string) ($field['title'] ?? ''), $needle)) return trim((string) ($field['value'] ?? ''));
 }
 return '';
}

/**
 * What one ticket says about its buyer, or null if it is not a registration.
 *
 * The title names the tournament and whether a captain ticket was bought, but
 * it is not the last word on who someone is. Captains keep buying the plain
 * player ticket - Lichtman did - and the registration form asks them outright,
 * so the answer to "Are you the team captain?" overrules the title. Without
 * that, a captain reads as an ordinary player and their colour looks free.
 *
 * Test and sample tickets are staff rehearsing the form, not people who are
 * coming, so they are dropped here rather than counted as registrations.
 */
function ymc_captain_dash_classify_ticket($ticket) {
 $title = (string) (is_array($ticket) ? ($ticket['title'] ?? '') : $ticket);
 if (preg_match('/\b(test|sample)\b/i', $title)) return null;
 /* Adult only. The teen tournament does not run on captain teams and this screen
    is about the team side, so counting teens here would only pad the numbers. */
 if (!preg_match('/^\s*adult\s+tournament\b/i', $title)) return null;
 $where = 'Adult';
 $ticket = is_array($ticket) ? $ticket : [];


 /* What they said they are playing on. On a captain ticket the colour is in the
    title; on a player ticket it is the only place the colour appears at all. */
 $says = ymc_captain_ticket_answer($ticket, 'which team');
 $said = ymc_captain_colour_in_team($says);

 if (false !== stripos($title, '(Captain Only)')) {
  return ['where' => $where, 'kind' => 'captain', 'colour' => ymc_captain_colour_in_team($title), 'mislabelled' => false];
 }
 if (!preg_match('/\bplayer\b/i', $title)) return null;

 /* Yes here and a player ticket in hand is the gap nothing else can see. */
 $isCaptain = 0 === strcasecmp('yes', ymc_captain_ticket_answer($ticket, 'team captain'));
 return [
  'where'       => $where,
  'kind'        => $isCaptain ? 'captain' : 'player',
  'colour'      => $said,
  'mislabelled' => $isCaptain,
 ];
}

/**
 * Everyone registered in Givebutter, with their team counts, or null if it
 * could not be read.
 *
 * Three calls, cached together, because the screen needs all three to say
 * anything useful: the tickets are who paid, the teams are how full each one
 * is, and the member list is the only way to tell whether someone who paid ever
 * ended up on a team at all.
 *
 * Givebutter will not say which member belongs to which team - the member
 * records carry no team, and filtering them by team is ignored - so the roster
 * is a count per team, and people are matched to "on a team or not" by email.
 */
function ymc_captain_dash_registrations() {
 $cached = get_transient('ymc_captain_dash_registrations');
 if (is_array($cached)) return $cached;
 if (!function_exists('YMC\\CaptainInvitations\\collection')) return null;
 try {
  $tickets = call_user_func('YMC\\CaptainInvitations\\collection', 'tickets?campaign_id=' . YMC_CAPTAIN_CAMPAIGN_ID);
  $teams   = call_user_func('YMC\\CaptainInvitations\\collection', 'campaigns/' . YMC_CAPTAIN_CAMPAIGN_ID . '/teams');
  $members = call_user_func('YMC\\CaptainInvitations\\collection', 'campaigns/' . YMC_CAPTAIN_CAMPAIGN_ID . '/members');
  $paidTx  = call_user_func('YMC\\CaptainInvitations\\collection', 'transactions?campaign_id=' . YMC_CAPTAIN_CAMPAIGN_ID);
 } catch (\Throwable $e) {
  return null;
 }

 /* What was actually paid, per transaction. Matching test registrations on the
    name was always a guess, and it missed the ones bought under a real name.
    Money is the fact: a rehearsal costs nothing, a registration costs $180.
    A ticket's own price is the sticker price and stays 180 on a comped entry,
    so the transaction is the only place the truth is. */
 $paidFor = [];
 foreach ($paidTx as $tx) {
  $id = (string) ($tx['id'] ?? '');
  if ('' === $id || 'succeeded' !== (string) ($tx['status'] ?? '')) continue;
  $paidFor[$id] = (float) ($tx['amount'] ?? 0);
 }

 $onTeam = [];
 $raised = [];
 foreach ($members as $m) {
  $email = strtolower(trim((string) ($m['email'] ?? '')));
  if ('' === $email) continue;
  $onTeam[$email] = true;
  /* What they have raised, not what they paid: a captain who brings in
     sponsorship shows more than their ticket, which is the number the team
     total is made of. */
  $raised[$email] = (float) ($m['raised'] ?? 0);
 }

 /* Which colours have a team at all. A player naming a colour nobody captains
    is a different problem from one waiting to be added, and saying the wrong
    one of the two sends staff looking for a team that was never created. */
 $teamForColour = [];
 foreach ($teams as $t) {
  $colour = ymc_captain_colour_in_team($t['name'] ?? '');
  if ('' !== $colour && !isset($teamForColour[$colour])) $teamForColour[$colour] = (string) ($t['name'] ?? '');
 }

 $people = [];
 foreach ($tickets as $t) {
  $what = ymc_captain_dash_classify_ticket($t);
  if (null === $what) continue;
  /* Nothing paid, nobody coming. This is what keeps rehearsals out, and it also
     drops a registration staff have since cancelled, which no amount of reading
     the ticket could have told us. */
  if (0 >= ($paidFor[(string) ($t['transaction_id'] ?? '')] ?? 0)) continue;
  $email = strtolower(trim((string) ($t['email'] ?? '')));
  $name  = trim((string) ($t['first_name'] ?? '') . ' ' . (string) ($t['last_name'] ?? '')) ?: (string) ($t['name'] ?? '');
  $team  = '' !== $what['colour'] ? ($teamForColour[$what['colour']] ?? '') : '';

  /* Being the captain counts as being on the team even without a fundraiser
     page: the team carries their name. Avi Yudkowsky captains "Avi Yudkowsky -
     Gray" while Dany Donaty holds the page, and reporting him as adrift was
     plainly wrong. */
  $placed = ('' !== $email && !empty($onTeam[$email]))
   || ('' !== $team && '' !== $name && ymc_captain_name_in_team($name, $team));

  $people[] = [
   'name'        => $name,
   'email'       => $email,
   'title'       => (string) ($t['title'] ?? ''),
   'where'       => $what['where'],
   'kind'        => $what['kind'],
   'colour'      => $what['colour'],
   'team'        => $team,
   'mislabelled' => $what['mislabelled'],
   /* Their fundraising page if they have one, otherwise what they paid: someone
      not yet added to a team has still handed over their $180. */
   'raised'      => $raised[$email] ?? (float) ($paidFor[(string) ($t['transaction_id'] ?? '')] ?? 0),
   'date'        => (string) ($t['created_at'] ?? ''),
   /* No team for that colour is not the same as not being on one. */
   'noteam'      => '' !== $what['colour'] && '' === $team,
   'adrift'      => '' !== $team && !$placed,
  ];
 }
 usort($people, function ($a, $b) { return strcmp($b['date'], $a['date']); });

 /* Counted from what each person wrote on their own form. Givebutter's member
    list does not say which team anyone is on, and filtering it by team is
    ignored, so the registration answer is the only thing that does. */
 $byColour = [];
 foreach ($people as $p) {
  if ('' === $p['colour']) continue;
  $byColour[$p['colour']] = ($byColour[$p['colour']] ?? 0) + 1;
 }

 $rosters = [];
 foreach ($teams as $t) {
  $colour = ymc_captain_colour_in_team($t['name'] ?? '');
  $rosters[] = [
   'name'      => (string) ($t['name'] ?? ''),
   'url'       => (string) ($t['url'] ?? ''),
   'raised'    => (float) ($t['raised'] ?? 0),
   'colour'    => $colour,
   /* Two counts, because they answer different questions. Givebutter's is who
      has a fundraising page on the team; ours is who said they are playing for
      it. They part company the moment somebody pays without being added. */
   'members'   => (int) ($t['members'] ?? 0),
   'signed_up' => $colour ? (int) ($byColour[$colour] ?? 0) : 0,
  ];
 }
 usort($rosters, function ($a, $b) { return $b['signed_up'] <=> $a['signed_up']; });

 $data = ['people' => $people, 'teams' => $rosters];
 /* Short, because staff refresh this screen while adding people in Givebutter
    in another tab and a stale roster would read as the change not working. */
 set_transient('ymc_captain_dash_registrations', $data, 180);
 return $data;
}

/**
 * The pager that sits under a table.
 *
 * It paginates whatever table shares its parent, so it goes inside the panel or
 * the tab the table lives in. Hidden until the script finds work for it: with
 * no JavaScript, and on a table short enough to fit, every row simply shows.
 */
function ymc_captain_dash_pager($label = 'rows') {
 return sprintf('<div class="pager" data-pager data-pager-label="%s" hidden>'
  . '<button type="button" data-page="prev">&larr; Previous</button>'
  . '<span data-page-status role="status"></span>'
  . '<button type="button" data-page="next">Next &rarr;</button></div>', esc_attr($label));
}

/**
 * The filter pills and search box that sit at the top of a table.
 *
 * One shape for every table on the screen. The request table had this and the
 * others did not, which made them look like different kinds of thing when they
 * are all just lists of rows.
 *
 * Hidden until the script shows it: the controls do nothing without JavaScript,
 * and a dead row of buttons is worse than none.
 */
function ymc_captain_dash_toolbar($group, $filters, $label, $placeholder) {
 $buttons = '';
 $first   = true;
 foreach ($filters as $value => $text) {
  $buttons .= sprintf('<button type="button" data-filter="%s" aria-pressed="%s">%s</button>',
   esc_attr($value), $first ? 'true' : 'false', esc_html($text));
  $first = false;
 }
 return sprintf('<div class="request-toolbar" data-toolbar="%s" hidden>'
  . '<div class="request-filters" role="group" aria-label="%s">%s</div>'
  . '<label class="search-label">%s<input type="search" data-search placeholder="%s"></label></div>',
  esc_attr($group), esc_attr($label), $buttons, esc_html($label), esc_attr($placeholder));
}

/** Everyone who registered in Givebutter, and how full each team is. */
function ymc_captain_dash_registration_board() {
 $data = ymc_captain_dash_registrations();
 echo '<section id="registrations"><div class="section-heading"><div><p class="eyebrow">WHO IS COMING</p><h2>Player registrations</h2>'
  . '<p class="sub">Everyone who has bought a ticket, read live from Givebutter.</p></div></div>';

 if (null === $data) {
  echo '<div class="panel"><p class="empty">Givebutter could not be reached just now, so registrations cannot be shown. '
   . 'This does not mean nobody has registered &mdash; try refreshing in a minute.</p></div></section>';
  return;
 }


 /* Two views of the same people, in one panel with one toolbar, so they share
    the space rather than one scrolling past the other. Without JavaScript the
    toolbar stays hidden and both tables simply show. */
 echo '<div class="panel">'
  . ymc_captain_dash_toolbar('reg', ['teams' => 'Teams', 'players' => 'Players breakdown'],
    'Search registrations', 'Name, email, color or team');

 echo '<div data-reg-panel="teams"><div class="table-scroll">';
 echo '<table><thead><tr><th>Team</th><th>Players</th><th>Raised</th></tr></thead><tbody>';
 foreach ($data['teams'] as $team) {
  $dot = $team['colour'] ? '<span class="dot" style="background:' . esc_attr(ymc_captain_colour_hex($team['colour'])) . '"></span>' : '';
  printf('<tr data-row><td>%s%s</td><td>%s</td><td class="muted">$%s</td></tr>',
   $dot,
   $team['url'] ? '<a href="' . esc_url($team['url']) . '" target="_blank" rel="noopener">' . esc_html($team['name']) . '</a>' : esc_html($team['name']),
   /* Nobody at all is the thing worth noticing, so it says so in words. */
   0 === $team['signed_up'] ? '<span class="muted">Nobody yet</span>' : esc_html($team['signed_up'] . (1 === $team['signed_up'] ? ' player' : ' players')),
   esc_html(number_format($team['raised'])));
 }
 echo '</tbody></table></div>' . ymc_captain_dash_pager('teams') . '</div>';

 /* No heading of its own: the tab is already called this, and repeating it
    pushes the table it describes further down the screen. */
 echo '<div data-reg-panel="players"><p class="panel-note top">Every person in those teams &mdash; what they bought, '
  . 'which color they play for, and anything that still needs doing.</p><div class="table-scroll">'
  . '<table><thead><tr><th>Registered</th><th>Name</th><th>Bought</th><th>Playing for</th><th>Raised</th><th>Notes</th></tr></thead><tbody>';
 if (!$data['people']) {
  echo '<tr><td colspan="6"><p class="empty">Nobody has registered yet. Test tickets are not counted here.</p></td></tr>';
 }
 foreach ($data['people'] as $p) {
  printf('<tr data-row><td>%s</td><td><strong>%s</strong><span class="sub">%s</span></td><td>%s%s</td><td>%s</td><td class="muted">$%s</td><td>%s</td></tr>',
   esc_html($p['date'] ? mysql2date('j M, g:i a', $p['date']) : '-'),
   esc_html($p['name'] ?: '-'), esc_html($p['email']),
   /* What the ticket was, not what they turned out to be: staff looking for the
      transaction in Givebutter need the name it was sold under. */
   esc_html(false !== stripos($p['title'], '(Captain Only)') ? 'Captain ticket' : 'Player ticket'),
   $p['mislabelled'] ? '<span class="sub">but said they are the captain</span>' : '',
   $p['colour']
    ? '<span class="dot" style="background:' . esc_attr(ymc_captain_colour_hex($p['colour'])) . '"></span>' . esc_html($p['colour'])
    : '<span class="muted">Not given</span>',
   esc_html(number_format($p['raised'])),
   ymc_captain_dash_person_note($p));
 }
 echo '</tbody></table></div>' . ymc_captain_dash_pager('people') . '</div>'
  . '<p class="filter-empty empty" hidden>Nothing matches that search.</p></div></section>';
}

/** The one thing worth saying about a registration, or nothing. */
function ymc_captain_dash_person_note($p) {
 /* Ordered by what staff would act on first. Only one shows: a row with three
    warnings on it is a row nobody reads. Each says what to do with it, because
    a note at the foot of the table explaining the tags goes unread. */
 /* Each is the instruction itself, not an explanation of one. A paragraph here
    is a paragraph nobody reads on a screen they are skimming. */
 if ($p['mislabelled']) {
  return '<span class="tag taken">Captain on a player ticket</span>'
   . '<span class="sub">' . esc_html('Set ' . $p['colour'] . ' to Taken') . '</span>';
 }
 if ('' === $p['colour']) {
  return '<span class="tag reserved">No color</span><span class="sub">Ask which team</span>';
 }
 if ($p['noteam']) {
  return '<span class="tag reserved">' . esc_html('No ' . $p['colour'] . ' team') . '</span>'
   . '<span class="sub">Create it or move them</span>';
 }
 if ($p['adrift']) {
  return '<span class="tag reserved">Not added</span>'
   . '<span class="sub">' . esc_html('Add to ' . $p['colour']) . '</span>';
 }
 return '<span class="muted">&mdash;</span>';
}

/** Everyone waiting to be placed on a team, on a page of its own. */
function ymc_captain_dash_team_requests_screen() {
 $asks = get_posts([
  'post_type'      => 'ymc_team_request',
  'post_status'    => ['publish', 'draft', 'pending', 'private'],
  'posts_per_page' => 200,
  'orderby'        => 'date',
  'order'          => 'DESC',
 ]);

 ymc_captain_dash_head('Team requests');
 printf('<header class="bar" id="overview"><div><p class="eyebrow">TOURNAMENT OPERATIONS</p><h1>Team requests</h1>'
  . '<p>People who asked for help joining a team.</p></div><div class="bar-actions">'
  . '<a class="refresh-link" href="%s">Back to the dashboard</a>%s</div></header>',
  esc_url(ymc_captain_dash_url()),
  current_user_can('manage_options')
   ? '<a href="' . esc_url(admin_url('edit.php?post_type=ymc_team_request')) . '">Open in WordPress</a>'
   : '<a href="' . esc_url(ymc_captain_dash_url(['logout' => 1])) . '">Sign out</a>');

 ymc_captain_dash_print_notice();

 printf('<div class="cards"><a href="#placements" class="card%s"><span>Waiting to be placed</span><b>%d</b>'
  . '<small>Asked on the website, not yet registered</small></a></div>',
  $asks ? ' attention' : '', count($asks));

 echo '<section id="placements"><div class="section-heading"><div><p class="eyebrow">LOOKING FOR A TEAM</p><h2>Who is waiting</h2>'
  . '<p class="sub">These people have not paid &mdash; they are waiting for someone to place them on a team.</p></div></div><div class="panel">';
 if (!$asks) {
  echo '<p class="empty">Nobody is waiting for a placement right now. Submissions from the Need a Team form appear here.</p></div></section>';
  ymc_captain_dash_foot();
  return;
 }
 echo ymc_captain_dash_toolbar('placements', ['all' => 'Everyone waiting'], 'Search', 'Name, email or phone')
  . '<div class="table-scroll"><table><thead><tr><th>Asked</th><th>Name</th><th>Phone</th><th>Staff emailed</th></tr></thead><tbody>';
 foreach ($asks as $ask) {
  /* Read back out of the body, which is where this form writes them. */
  printf('<tr data-row><td>%s</td><td><strong>%s</strong><span class="sub">%s</span></td><td class="muted">%s</td><td class="muted">%s</td></tr>',
   esc_html(get_the_date('j M, g:i a', $ask)),
   esc_html($ask->post_title),
   esc_html(ymc_captain_body_field($ask->post_content, 'Email')),
   esc_html(ymc_captain_body_field($ask->post_content, 'Phone') ?: '-'),
   'yes' === get_post_meta($ask->ID, 'ymc_notification_sent', true) ? 'Sent' : 'Not sent');
 }
 echo '</tbody></table></div><p class="filter-empty empty" hidden>Nobody matches that search.</p>'
  . ymc_captain_dash_pager('people') . '</div></section>';
 ymc_captain_dash_foot();
}

/** The colour board: the same reading the public form works from. */
function ymc_captain_dash_colour_board() {
 $states  = ymc_captain_colour_states();
 $paid    = (array) ymc_captain_paid_colours();
 $tickets = ymc_captain_ticket_colours();
 echo '<section id="team-colors" class="color-section"><div class="section-heading"><div><p class="eyebrow">TEAM AVAILABILITY</p><h2>Team colors</h2><p class="sub">Available, reserved, and paid captain colors.</p></div></div><div class="panel color-panel">'
  . ymc_captain_dash_toolbar('colour', [
    'all'      => 'All colors',
    'free'     => 'Available',
    'reserved' => 'Reserved',
    'taken'    => 'Taken',
   ], 'Search colors', 'Color or who holds it')
  . '<div class="table-scroll"><table><thead><tr>'
  . '<th>Color</th><th>Status</th><th>Detail</th></tr></thead><tbody>';
 foreach (ymc_captain_colours() as $colour) {
  $dot = '<span class="dot" style="background:' . esc_attr(ymc_captain_colour_hex($colour)) . '"></span>';
  if (!isset($states[$colour])) {
   /* Bucket "off" belongs to no tab, so a colour with no ticket shows only
      under All rather than padding out whichever list it least belongs in. */
   printf('<tr data-colour data-bucket="off"><td>%s%s</td><td><span class="tag off">Not offered</span></td>'
    . '<td class="muted">No &ldquo;(Captain Only)&rdquo; ticket for this color exists in Givebutter.</td></tr>',
    $dot, esc_html($colour));
   continue;
  }
  $staff  = ymc_captain_colour_tag($colour);
  $bucket = $states[$colour];
  if ('taken' === $states[$colour]) {
   /* One chip for both. A color is Taken, Reserved or Available and nothing
      else; whether that came from a sale or from staff is what the detail
      beside it is for. "Paid" against a colour nobody paid for would send staff
      hunting through Givebutter for a transaction that is not there. */
   $tag    = '<span class="tag taken">Taken</span>';
   $detail = !empty($paid[$colour])
    ? 'Captain ticket paid &mdash; ' . esc_html($paid[$colour])
    : 'Marked taken by staff';
  } elseif ('reserved' === $states[$colour]) {
   $holder = ymc_captain_colour_holder($colour);
   $tag    = '<span class="tag reserved">Reserved</span>';
   if ('reserved' === $staff) {
    $detail = 'Marked reserved by staff';
   } else {
    $detail = 'reserved' === $holder ? 'Held manually' : 'Requested &mdash; awaiting payment in Givebutter';
   }
  } else {
   $tag    = '<span class="tag free">Available</span>';
   $detail = '';
  }
  printf('<tr data-colour data-bucket="%s"><td>%s%s</td><td>%s</td><td class="muted">%s</td></tr>',
   esc_attr($bucket), $dot, esc_html($colour), $tag, $detail);
 }
 echo '</tbody></table></div><p class="filter-empty empty" hidden>No colors match.</p>'
  . ymc_captain_dash_pager('colors') . '</div>';
 if (null === $tickets) {
  echo '<p class="sub">Givebutter&rsquo;s ticket list could not be read just now. Color availability may be incomplete.</p>';
 }
 echo '</section>';
}

/**
 * True when a captain has their link but has not bought their ticket.
 *
 * "Link sent" was being read as finished, which it is not: the colour is only
 * reserved until the (Captain Only) ticket sells, and a reserved colour is one
 * nobody else can take either. Red sat like this for days looking settled.
 * Unknown colours are left alone - if Givebutter could not be read, silence is
 * better than telling staff to chase someone who has already paid.
 */
function ymc_captain_dash_awaiting_payment($id, $states) {
 if ('yes' !== get_post_meta($id, 'ymc_link_sent', true)) return false;
 if (ymc_captain_request_is_closed(get_post_meta($id, 'ymc_status', true))) return false;
 $colour = (string) get_post_meta($id, 'ymc_colour', true);
 if ('' === $colour || !isset($states[$colour])) return false;
 return 'taken' !== $states[$colour];
}

/** One row per request, with the controls that apply to it. */
function ymc_captain_dash_request_row($post, $states = []) {
 $id     = $post->ID;
 $colour = (string) get_post_meta($id, 'ymc_colour', true);
 $status = (string) get_post_meta($id, 'ymc_status', true) ?: 'pending';
 $team   = (string) get_post_meta($id, 'ymc_team_name', true);
 $url    = (string) get_post_meta($id, 'ymc_team_url', true);
 $sent   = (string) get_post_meta($id, 'ymc_link_sent', true);
 $isCap  = (string) get_post_meta($id, 'ymc_is_captain', true);
 list($email, $name) = ymc_captain_target($id);

 $reason = ymc_captain_request_reason($id);
 /* Four buckets, not three. A request the site turned away and one staff called
    off both end here, but only one of them is anybody's decision, and lumping
    them together is what made the screen read as if we had cancelled on him. */
 if ('unsuccessful' === $status)   $buckets = ['unsuccessful'];
 elseif ('cancelled' === $status)  $buckets = ['cancelled'];
 else                              $buckets = ['yes' === $sent ? 'sent' : 'waiting'];
 /* A row can sit in more than one list. Awaiting payment is not an alternative
    to Link sent, it is a fact about a link that was sent, and moving the row out
    of Link sent to say so would just hide it somewhere else. */
 $owed = ymc_captain_dash_awaiting_payment($id, $states);
 if ($owed) $buckets[] = 'unpaid';
 echo '<tr data-request data-bucket="' . esc_attr(implode(' ', $buckets)) . '"><td>' . esc_html(get_the_date('j M, g:i a', $post)) . '</td>';

 /* The captain is the person the link is owed to, which is not always the one
    who filled the form in - showing only the submitter hid that entirely. */
 printf('<td><strong>%s</strong><span class="sub">%s</span>%s</td>',
  esc_html($name ?: '-'), esc_html($email),
  'no' === $isCap
   ? '<span class="sub">requested by ' . esc_html((string) get_post_meta($id, 'ymc_name', true)) . '</span>'
   : '');

 /* A refused request holds no colour, but the one it asked for is the point of
    the row, so it shows with a note rather than a dash. */
 $wanted = $colour ?: (string) get_post_meta($id, 'ymc_wanted_colour', true);
 printf('<td>%s%s%s</td>',
  $wanted ? '<span class="dot" style="background:' . esc_attr(ymc_captain_colour_hex($wanted)) . '"></span>' : '',
  esc_html($wanted ?: '-'),
  ('' === $colour && '' !== $wanted) ? '<span class="sub">asked for, not held</span>' : '');

 echo '<td><span class="request-status status-' . esc_attr($buckets[0]) . '">'
  . esc_html('unsuccessful' === $status ? 'Unsuccessful' : ucwords(str_replace('_', ' ', $status))) . '</span>';
 if ('unsuccessful' === $status) echo '<span class="sub">not cancelled by staff</span>';
 /* Sits beside the status rather than replacing it, because both are true: the
    link really was sent, and the ticket really has not been bought. */
 if ($owed) {
  echo '<span class="request-status status-unpaid">Awaiting payment</span>'
   . '<span class="sub">' . esc_html($colour) . ' is still only reserved &mdash; the captain ticket has not been bought</span>';
 }
 if ($n = (int) get_post_meta($id, 'ymc_link_resent_count', true)) {
  $at = (string) get_post_meta($id, 'ymc_link_resent_at', true);
  echo '<span class="sub">link resent ' . esc_html($n > 1 ? $n . ' times' : 'once');
  if ($at) echo ', last ' . esc_html(mysql2date('j M, g:i a', $at));
  echo '</span>';
 }
 echo '</td>';

 echo '<td>';
 if ('' === $team) {
  echo '<span class="sub">Not created yet</span>';
 } else {
  echo $url
   ? '<a href="' . esc_url($url) . '" target="_blank" rel="noopener">' . esc_html($team) . '</a>'
   : esc_html($team);
  echo '<span class="sub">link ' . ('yes' === $sent ? 'sent' : 'not sent') . '</span>';
 }
 echo '</td><td><div class="request-actions">';

 if (ymc_captain_request_is_closed($status)) {
  /* The reason sits behind a disclosure rather than in the row: it is a
     sentence, and a closed request is the one people stop and ask about. Older
     records predate the reason being stored, so they say so instead of
     offering an empty panel. */
  printf('<details class="why"><summary>%s</summary><p>%s</p>%s</details>',
   esc_html('unsuccessful' === $status ? 'Why it failed' : 'Why it was cancelled'),
   esc_html($reason ?: 'No reason was recorded. This request closed before the dashboard started keeping them.'),
   'unsuccessful' === $status
    ? '<p class="muted">Nothing was sent to the captain beyond the message on the form. If they never came back, they still need a team.</p>'
    : '');
 } else {
  if ('' !== $team) {
   printf('<form method="post" class="inline"><input type="hidden" name="ymc_dash_action" value="resend">'
    . '<input type="hidden" name="ymc_dash_token" value="%s"><input type="hidden" name="id" value="%d">'
    . '<button type="submit" class="ghost">Resend link</button></form>',
    esc_attr(ymc_captain_dash_token('resend')), $id);
  }
  /* Only offered while the link is still owed: the manual send exists for teams
     the poll cannot match by name, not as a way to mail a captain twice. */
  if ('yes' !== $sent) {
   printf('<details class="send"><summary>Team created? Send the link</summary>'
    . '<form method="post"><input type="hidden" name="ymc_dash_action" value="send">'
    . '<input type="hidden" name="ymc_dash_token" value="%s"><input type="hidden" name="id" value="%d">'
    . '<input type="text" name="team" placeholder="Team name in Givebutter" value="%s" required>'
    . '<input type="url" name="url" placeholder="https://givebutter.com/..." required>'
    . '<button type="submit">Send link</button></form></details>',
    esc_attr(ymc_captain_dash_token('send')), $id, esc_attr($team ?: get_the_title($post)));
  }
  if ($colour) {
   printf('<form method="post" class="inline" onsubmit="return confirm(%s)">'
    . '<input type="hidden" name="ymc_dash_action" value="cancel">'
    . '<input type="hidden" name="ymc_dash_token" value="%s"><input type="hidden" name="id" value="%d">'
    . '<button type="submit" class="danger">Cancel &amp; free %s</button></form>',
    esc_attr(wp_json_encode('Cancel this request and make ' . $colour . ' available again?')),
    esc_attr(ymc_captain_dash_token('cancel')), $id, esc_html($colour));
  }
 }
 echo '</div></td></tr>';
}

function ymc_captain_dash_screen() {
 $requests = get_posts([
  'post_type'      => 'ymc_captain_request',
  'post_status'    => ['publish', 'draft', 'pending', 'private'],
  'posts_per_page' => 200,
  'orderby'        => 'date',
  'order'          => 'DESC',
 ]);

 /* Read once and handed down to every row. It calls Givebutter, so asking per
    row would mean one request each time the table is drawn. */
 $states = ymc_captain_colour_states();
 $taken  = count(array_keys($states, 'taken', true));

 /* Counted from the records rather than the colour board: this answers "who is
    waiting on me", which is the question the screen exists for. */
 $waiting = 0;
 $done    = 0;
 $unpaid  = 0;
 foreach ($requests as $post) {
  if (ymc_captain_request_is_closed(get_post_meta($post->ID, 'ymc_status', true))) continue;
  if ('yes' !== get_post_meta($post->ID, 'ymc_link_sent', true)) { $waiting++; continue; }
  $done++;
  /* Sending the link is not the finish line. Until the captain buys their
     ticket the colour is only reserved, and a request sitting here looked
     settled while nobody had paid for it. */
  if (ymc_captain_dash_awaiting_payment($post->ID, $states)) $unpaid++;
 }

 ymc_captain_dash_head('Team dashboard');

 /* Shown to whoever opens the screen, not buried in a log nobody reads. A few
    failures a day is someone mistyping; a hundred is not. */
 $failures = ymc_captain_dash_recent_failures();
 if (10 <= count($failures)) {
  printf('<p class="note bad" role="status">%s</p>', esc_html(sprintf(
   '%d wrong passwords were tried in the last 24 hours, from %d different addresses. If that was not staff, tell Nikka.',
   count($failures),
   count(array_unique(wp_list_pluck($failures, 'ip')))
  )));
 }

 printf('<header class="bar" id="overview"><div><p class="eyebrow">TOURNAMENT OPERATIONS</p><h1>Team registrations</h1>'
  . '<p>Your teams, captain requests, and next steps in one place.</p></div><div class="bar-actions"><a class="refresh-link" href="' . esc_url(ymc_captain_dash_url()) . '">Refresh overview</a>%s</div></header>',
  current_user_can('manage_options')
   ? '<a href="' . esc_url(admin_url('edit.php?post_type=ymc_captain_request')) . '">Open in WordPress</a>'
   : '<a href="' . esc_url(ymc_captain_dash_url(['logout' => 1])) . '">Sign out</a>');

 ymc_captain_dash_print_notice();

 printf('<div class="cards">'
  . '<a href="#requests" class="card"><span>Total requests</span><b>%d</b><small>Latest 200 submissions</small></a>'
  . '<a href="#requests" data-filter-shortcut="waiting" class="card attention"><span>Needs follow-up</span><b>%d</b><small>Registration link not sent</small></a>'
  . '<a href="#requests" data-filter-shortcut="unpaid" class="card attention"><span>Awaiting payment</span><b>%d</b><small>Link sent, captain ticket not bought</small></a>'
  . '<a href="#requests" data-filter-shortcut="sent" class="card"><span>Links sent</span><b>%d</b><small>Captains have their next step</small></a>'
  . '<a href="#team-colors" class="card"><span>Paid colors</span><b>%d</b><small>Captain tickets purchased</small></a></div>',
  count($requests), $waiting, $unpaid, $done, $taken);

 echo '<section id="requests"><div class="section-heading"><div><p class="eyebrow">CAPTAIN MANAGEMENT</p><h2>Team registrations</h2></div><span class="sub">Newest requests first</span></div><div class="panel requests-panel">';
 echo '<div class="request-toolbar" hidden><div class="request-filters" role="group" aria-label="Filter requests"><button type="button" data-filter="all" aria-pressed="true">All requests</button><button type="button" data-filter="waiting" aria-pressed="false">Needs follow-up</button><button type="button" data-filter="sent" aria-pressed="false">Link sent</button><button type="button" data-filter="unpaid" aria-pressed="false">Awaiting payment</button><button type="button" data-filter="unsuccessful" aria-pressed="false">Unsuccessful</button><button type="button" data-filter="cancelled" aria-pressed="false">Cancelled by staff</button></div><label class="search-label">Search requests<input id="request-search" type="search" placeholder="Name, email, color or team"></label></div><div class="request-table-scroll" tabindex="0" role="region" aria-label="Team requests">';
 if (!$requests) {
  echo '<p class="empty">No team requests yet. They will appear here as captains submit the form.</p>';
 } else {
  echo '<table><thead><tr><th>Submitted</th><th>Captain</th><th>Color</th>'
   . '<th>Status</th><th>Team</th><th>Actions</th></tr></thead><tbody>';
  foreach ($requests as $post) { ymc_captain_dash_request_row($post, $states); }
  echo '</tbody></table>';
 }
 echo '</div><p class="filter-empty empty" hidden>No requests match. Try another name or filter.</p><div class="table-footer"><span id="request-count" role="status">' . count($requests) . ' requests</span><span>Showing up to 200 most recent requests</span></div>' . ymc_captain_dash_pager('requests') . '</div></section>';

 ymc_captain_dash_registration_board();
 ymc_captain_dash_colour_board();
 ymc_captain_dash_foot();
}

/* ------------------------------------------------------------------- routing */

/**
 * Takes over the request when the dashboard path is asked for.
 *
 * Matched on the path rather than registered as a rewrite rule, because a
 * rewrite needs its rules flushed to work and the uploader deactivates this
 * plugin without re-running activation - the same trap that silently killed the
 * team-link cron. A path check cannot fall out of step.
 */
function ymc_captain_dash_route() {
 $path = trim(parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '', '/');
 if (YMC_CAPTAIN_DASH_PATH !== $path) return;

 /* template_redirect runs after WordPress has already concluded this URL is a
    404, so the status has to be corrected by hand. Left alone the screen renders
    perfectly but answers 404, and the proxy cache in front of the live site
    treats it as a missing page. */
 global $wp_query;
 if ($wp_query instanceof WP_Query) {
  $wp_query->is_404 = false;
 }
 status_header(200);

 if (isset($_GET['logout'])) {
  ymc_captain_dash_end_session();
  wp_safe_redirect(ymc_captain_dash_url());
  exit;
 }

 if (!empty($_POST['ymc_dash_login'])) {
  if (ymc_captain_dash_locked_out()) {
   wp_safe_redirect(ymc_captain_dash_url(['msg' => 'lockedout']));
   exit;
  }
  $stored = (string) get_option('ymc_captain_dash_password', '');
  if ($stored && wp_check_password(wp_unslash((string) $_POST['password']), $stored)) {
   ymc_captain_dash_start_session();
   /* Only this address is forgiven. The site-wide tally stands, or one correct
      sign-in in the middle of an attack would clear the evidence of it. */
   delete_transient(ymc_captain_dash_throttle_key());
   wp_safe_redirect(ymc_captain_dash_url());
   exit;
  }
  ymc_captain_dash_count_failure();
  wp_safe_redirect(ymc_captain_dash_url(['msg' => 'wrongpass']));
  exit;
 }

 if (!ymc_captain_dash_may_view()) {
  ymc_captain_dash_login_screen();
  exit;
 }

 ymc_captain_dash_handle_action();
 if ('team-requests' === ymc_captain_dash_view()) {
  ymc_captain_dash_team_requests_screen();
 } else {
  ymc_captain_dash_screen();
 }
 exit;
}
/* Early enough to answer before WordPress decides the URL is a 404. */
add_action('template_redirect', 'ymc_captain_dash_route', 1);
