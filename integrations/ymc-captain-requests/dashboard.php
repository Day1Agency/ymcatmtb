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

function ymc_captain_dash_locked_out() {
 return YMC_CAPTAIN_DASH_TRIES <= (int) get_transient(ymc_captain_dash_throttle_key());
}

function ymc_captain_dash_count_failure() {
 $key = ymc_captain_dash_throttle_key();
 set_transient($key, (int) get_transient($key) + 1, 15 * MINUTE_IN_SECONDS);
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
  'lockedout'  => ['bad', 'Too many attempts. Please wait fifteen minutes.'],
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
<p class="nav-label">WORKSPACE</p><nav aria-label="Dashboard"><a href="#overview" class="active">Overview</a><a href="#requests">Team requests</a><a href="#team-colors">Team colors</a></nav>
<div class="sidebar-bottom"><span>ADULT TOURNAMENT</span><p>Keep your teams<br>moving forward.</p><a href="<?php echo esc_url(home_url('/')); ?>">View website &nearr;</a></div></aside>
<?php endif; ?><main class="wrap" id="dashboard-main">
<?php }

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

/** The colour board: the same reading the public form works from. */
function ymc_captain_dash_colour_board() {
 $states  = ymc_captain_colour_states();
 $paid    = (array) ymc_captain_paid_colours();
 $tickets = ymc_captain_ticket_colours();
 echo '<section id="team-colors" class="color-section"><div class="section-heading"><div><p class="eyebrow">TEAM AVAILABILITY</p><h2>Team colors</h2><p class="sub">Available, reserved, and paid captain colors.</p></div></div><div class="panel color-panel"><table><thead><tr>'
  . '<th>Color</th><th>Status</th><th>Detail</th></tr></thead><tbody>';
 foreach (ymc_captain_colours() as $colour) {
  $dot = '<span class="dot" style="background:' . esc_attr(ymc_captain_colour_hex($colour)) . '"></span>';
  if (!isset($states[$colour])) {
   printf('<tr><td>%s%s</td><td><span class="tag off">Not offered</span></td>'
    . '<td class="muted">No &ldquo;(Captain Only)&rdquo; ticket for this color exists in Givebutter.</td></tr>',
    $dot, esc_html($colour));
   continue;
  }
  $staff = ymc_captain_colour_tag($colour);
  if ('taken' === $states[$colour]) {
   $tag = '<span class="tag taken">Taken</span>';
   /* Says which of the two it is. "Paid" against a colour nobody paid for sends
      staff hunting through Givebutter for a transaction that is not there. */
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
  printf('<tr><td>%s%s</td><td>%s</td><td class="muted">%s</td></tr>', $dot, esc_html($colour), $tag, $detail);
 }
 echo '</tbody></table></div>';
 if (null === $tickets) {
  echo '<p class="sub">Givebutter&rsquo;s ticket list could not be read just now. Color availability may be incomplete.</p>';
 }
 echo '</section>';
}

/** One row per request, with the controls that apply to it. */
function ymc_captain_dash_request_row($post) {
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
 if ('unsuccessful' === $status)   $bucket = 'unsuccessful';
 elseif ('cancelled' === $status)  $bucket = 'cancelled';
 else                              $bucket = 'yes' === $sent ? 'sent' : 'waiting';
 echo '<tr data-request data-bucket="' . esc_attr($bucket) . '"><td>' . esc_html(get_the_date('j M, g:i a', $post)) . '</td>';

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

 echo '<td><span class="request-status status-' . esc_attr($bucket) . '">'
  . esc_html('unsuccessful' === $status ? 'Unsuccessful' : ucwords(str_replace('_', ' ', $status))) . '</span>';
 if ('unsuccessful' === $status) echo '<span class="sub">not cancelled by staff</span>';
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

 /* Counted from the records rather than the colour board: this answers "who is
    waiting on me", which is the question the screen exists for. */
 $waiting = 0;
 $done    = 0;
 foreach ($requests as $post) {
  if (ymc_captain_request_is_closed(get_post_meta($post->ID, 'ymc_status', true))) continue;
  if ('yes' === get_post_meta($post->ID, 'ymc_link_sent', true)) { $done++; } else { $waiting++; }
 }
 $states = ymc_captain_colour_states();
 $taken  = count(array_keys($states, 'taken', true));

 ymc_captain_dash_head('Team dashboard');
 printf('<header class="bar" id="overview"><div><p class="eyebrow">TOURNAMENT OPERATIONS</p><h1>Team dashboard</h1>'
  . '<p>Your teams, captain requests, and next steps in one place.</p></div><div class="bar-actions"><a class="refresh-link" href="' . esc_url(ymc_captain_dash_url()) . '">Refresh overview</a>%s</div></header>',
  current_user_can('manage_options')
   ? '<a href="' . esc_url(admin_url('edit.php?post_type=ymc_captain_request')) . '">Open in WordPress</a>'
   : '<a href="' . esc_url(ymc_captain_dash_url(['logout' => 1])) . '">Sign out</a>');

 ymc_captain_dash_print_notice();

 printf('<div class="cards">'
  . '<a href="#requests" class="card"><span>Total requests</span><b>%d</b><small>Latest 200 submissions</small></a>'
  . '<a href="#requests" data-filter-shortcut="waiting" class="card attention"><span>Needs follow-up</span><b>%d</b><small>Registration link not sent</small></a>'
  . '<a href="#requests" data-filter-shortcut="sent" class="card"><span>Links sent</span><b>%d</b><small>Captains have their next step</small></a>'
  . '<a href="#team-colors" class="card"><span>Paid colors</span><b>%d</b><small>Captain tickets purchased</small></a></div>',
  count($requests), $waiting, $done, $taken);

 echo '<section id="requests"><div class="section-heading"><div><p class="eyebrow">CAPTAIN MANAGEMENT</p><h2>Team requests</h2></div><span class="sub">Newest requests first</span></div><div class="panel requests-panel">';
 echo '<div class="request-toolbar" hidden><div class="request-filters" role="group" aria-label="Filter requests"><button type="button" data-filter="all" aria-pressed="true">All requests</button><button type="button" data-filter="waiting" aria-pressed="false">Needs follow-up</button><button type="button" data-filter="sent" aria-pressed="false">Link sent</button><button type="button" data-filter="unsuccessful" aria-pressed="false">Unsuccessful</button><button type="button" data-filter="cancelled" aria-pressed="false">Cancelled by staff</button></div><label class="search-label">Search requests<input id="request-search" type="search" placeholder="Name, email, color or team"></label></div><div class="request-table-scroll" tabindex="0" role="region" aria-label="Team requests">';
 if (!$requests) {
  echo '<p class="empty">No team requests yet. They will appear here as captains submit the form.</p>';
 } else {
  echo '<table><thead><tr><th>Submitted</th><th>Captain</th><th>Color</th>'
   . '<th>Status</th><th>Team</th><th>Actions</th></tr></thead><tbody>';
  foreach ($requests as $post) { ymc_captain_dash_request_row($post); }
  echo '</tbody></table>';
 }
 echo '</div><p class="filter-empty empty" hidden>No requests match. Try another name or filter.</p><div class="table-footer"><span id="request-count" role="status">' . count($requests) . ' requests</span><span>Showing up to 200 most recent requests</span></div></div></section>';

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
 ymc_captain_dash_screen();
 exit;
}
/* Early enough to answer before WordPress decides the URL is a 404. */
add_action('template_redirect', 'ymc_captain_dash_route', 1);
