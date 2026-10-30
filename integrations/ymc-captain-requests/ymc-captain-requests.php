<?php
/**
 * Plugin Name: YMC Captain Team Requests
 * Description: Captains request a team here; staff create it in Givebutter and send the link back.
 * Version: 1.2.0
 */
if (!defined('ABSPATH')) exit;

const YMC_CAPTAIN_REQUEST_FALLBACK = 'ykohen@yeshivasmekorchaim.org';

/** Adult Tournament, by numeric id. Its campaign code RMSQHT 404s on sub-resources. */
const YMC_CAPTAIN_CAMPAIGN_ID = '729740';

/** The list shipped with the plugin, used until staff edit it. */
function ymc_captain_default_colours() {
 return [
  'Blue'=>'#2563c9','Brown'=>'#7b4a25','Gray'=>'#8a8f98','Green'=>'#2f8f4e',
  'Maroon'=>'#7d2340','Orange'=>'#e07b26','Purple'=>'#6b3fa0','Red'=>'#c62f32',
  'Teal'=>'#1c8f8f','White'=>'#f2f2f2','Yellow'=>'#e8c53c','Black'=>'#1a1a1a',
 ];
}

/**
 * The colours on offer, as name => swatch, read from the settings screen.
 *
 * Editable rather than hard-coded because the set of teams is YMC's decision
 * and changes without a developer: a colour is dropped by deleting its line.
 * Removing a colour only stops it being offered; any request already holding
 * it keeps its record, so nobody loses a team because of an edit here.
 */
function ymc_captain_colour_map() {
 $raw = trim((string) get_option('ymc_captain_colour_list', ''));
 if ('' === $raw) { return ymc_captain_default_colours(); }
 $map = [];
 foreach (preg_split('/
|
|
/', $raw) as $line) {
  $line = trim($line);
  if ('' === $line) { continue; }
  /* "Teal #1c8f8f", or just "Teal" to fall back to a known or neutral swatch. */
  if (preg_match('/^(.*?)\s+(#[0-9a-f]{3,6})$/i', $line, $m)) {
   $name = trim($m[1]); $hex = $m[2];
  } else {
   $name = $line; $hex = ymc_captain_default_colours()[$line] ?? '#999999';
  }
  if ('' !== $name) { $map[$name] = $hex; }
 }
 return $map ?: ymc_captain_default_colours();
}

/**
 * Team colors, in the order they are offered.
 *
 * Each colour belongs to one team only. A color is held the moment a request
 * claims it and is only offered again if staff release it, so this list is the
 * full set and never the available set.
 */
function ymc_captain_colours() {
 return array_keys(ymc_captain_colour_map());
}

/**
 * The swatch shown beside each colour name.
 *
 * A name alone is harder to scan and useless to anyone who does not read
 * English confidently, so the dot carries the same information visually. The
 * name stays as the label, because a dot alone fails for colour-blind readers.
 */
function ymc_captain_colour_hex($colour) {
 $map = ymc_captain_colour_map();
 return $map[$colour] ?? '#999999';
}

/** Option name holding a colour claim. One row per colour; its existence is the claim. */
function ymc_captain_colour_key($colour) {
 return 'ymc_cap_colour_'.sanitize_key($colour);
}

/**
 * Claims a colour, or reports that somebody else already holds it.
 *
 * add_option() is a single INSERT against a unique index, so two requests
 * submitted in the same instant cannot both succeed. A check-then-write would
 * let both through and hand two captains the same colour.
 */
function ymc_captain_claim_colour($colour, $request_id) {
 return add_option(ymc_captain_colour_key($colour), (string) $request_id, '', false);
}

/** Frees a colour so it is offered again. Used when staff cancel a request. */
function ymc_captain_release_colour($colour) {
 delete_option(ymc_captain_colour_key($colour));
}

/** Which request holds a colour, or '' when the color is free. */
function ymc_captain_colour_holder($colour) {
 return (string) get_option(ymc_captain_colour_key($colour), '');
}

/**
 * Colours whose (Captain Only) ticket has been bought, or null if unknown.
 *
 * The ticket title carries the colour - "Adult Tournament - Gray (Captain
 * Only)" - so a purchase identifies the colour by itself. That matters because
 * the buyer's email need not match the request: Gray was requested from one
 * address and paid from another, so matching on email alone would have missed
 * it. Each colour ticket is limited to one, so a sale means sold out.
 *
 * Null on any failure is deliberate: a Givebutter outage must not release
 * colours that are genuinely gone.
 */
function ymc_captain_paid_colours() {
 $cached = get_transient('ymc_captain_paid_colours');
 if (is_array($cached)) return $cached;
 if (!function_exists('YMC\\CaptainInvitations\\api')) return null;
 $found = [];
 try {
  for ($page = 1; $page <= 20; $page++) {
   $r = call_user_func('YMC\\CaptainInvitations\\api','tickets?campaign_id='.YMC_CAPTAIN_CAMPAIGN_ID.'&page='.$page);
   foreach ($r['data'] as $ticket) {
    $title = (string) ($ticket['title'] ?? '');
    if (false === stripos($title,'(Captain Only)')) continue;
    /* The test tickets carry no colour, so they fall out here on their own. */
    $colour = ymc_captain_colour_in_team($title);
    if ('' !== $colour && !isset($found[$colour])) $found[$colour] = (string) ($ticket['email'] ?? '');
   }
   if (empty($r['links']['next'])) break;
  }
 } catch (\Throwable $e) {
  return null;
 }
 set_transient('ymc_captain_paid_colours', $found, 120);
 return $found;
}

/** Just the colours, for the places that do not care who bought them. */
function ymc_captain_paid_colour_names() {
 $paid = ymc_captain_paid_colours();
 return null === $paid ? null : array_keys($paid);
}

/**
 * Colours that actually have a (Captain Only) ticket, or null if unknown.
 *
 * A colour with no ticket cannot be registered for, so offering it strands the
 * captain. Givebutter's API has no endpoint for ticket definitions - only for
 * tickets already sold - so this reads the public registration page, which
 * carries every ticket name whether sold or not.
 *
 * Null on any failure, and an unparsable page counts as a failure rather than
 * as "no tickets exist": a layout change at Givebutter must not empty the form.
 */
function ymc_captain_ticket_colours() {
 $cached = get_transient('ymc_captain_ticket_colours');
 if (is_array($cached)) return $cached;
 $url = defined('YMC\\CaptainInvitations\\REGISTRATION')
  ? constant('YMC\\CaptainInvitations\\REGISTRATION')
  : 'https://givebutter.com/ymc-adult-player-registration/register';
 $response = wp_remote_get($url, ['timeout' => 20]);
 if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) return null;
 if (!preg_match_all('/Adult Tournament[^"]{0,60}\(Captain Only\)/i', wp_remote_retrieve_body($response), $m)) return null;
 $found = [];
 foreach ($m[0] as $title) {
  /* The page is JSON, so the em dash arrives as an escape sequence. */
  $decoded = json_decode('"'.$title.'"');
  $colour = ymc_captain_colour_in_team(is_string($decoded) ? $decoded : $title);
  if ('' !== $colour) $found[$colour] = true;
 }
 if (!$found) return null;
 $found = array_keys($found);
 set_transient('ymc_captain_ticket_colours', $found, 600);
 return $found;
}

/**
 * Every colour the form should list, as colour => state.
 *
 * A claimed colour stays on the page, greyed out: seeing that Blue is gone is
 * clearer than Blue silently not being there, and it stops a captain wondering
 * whether they missed it. A colour with no team in Givebutter is a different
 * case and is left out entirely - it is not taken, it does not exist yet, and
 * offering it would strand whoever picked it.
 */
function ymc_captain_colour_states() {
 $paid    = ymc_captain_paid_colour_names();
 $tickets = ymc_captain_ticket_colours();
 $states  = [];
 foreach (ymc_captain_colours() as $colour) {
  /* Paid outranks everything. The captain ticket is limited to one, so a sale
     means that colour is gone for good and no reservation can revive it. */
  if (null !== $paid && in_array($colour, $paid, true)) { $states[$colour] = 'taken'; continue; }
  /* Requested but not yet paid for. It still blocks the colour - two captains
     promised the same colour is worse than one waiting - but unlike taken it
     can be released. */
  $held = ('' !== ymc_captain_colour_holder($colour));
  /* Adding a colour in Settings is not enough on its own: without a ticket to
     buy there is nothing for the captain to do with it. One already spoken for
     stays listed, so a ticket withdrawn after the fact does not hide it. */
  if (!$held && null !== $tickets && !in_array($colour, $tickets, true)) continue;
  $states[$colour] = $held ? 'reserved' : 'free';
 }
 return $states;
}

/**
 * Colours still on offer.
 *
 * Teams are created by hand after a request now, so a colour is no longer
 * required to have a Givebutter team before it can be chosen; requiring that
 * would leave the form with nothing to offer.
 */
function ymc_captain_available_colours() {
 return array_keys(array_filter(ymc_captain_colour_states(), function ($state) { return 'free' === $state; }));
}

/**
 * Black belongs to a team that exists already, so it must never be offered.
 *
 * Seeded on activation rather than hard-coded out of the list: staff can release
 * it like any other colour if that team ever changes.
 */
register_activation_hook(__FILE__, function () {
 add_option(ymc_captain_colour_key('Black'), 'reserved', '', false);
});

/** Where notifications go. Settings value wins; the fallback keeps delivery working if it is blank. */
function ymc_captain_request_recipient() {
 $email = sanitize_email((string) get_option('ymc_captain_request_recipient',''));
 return is_email($email) ? $email : YMC_CAPTAIN_REQUEST_FALLBACK;
}

/** Shares the Team Placement Requests email shell when that plugin is active, so both look alike. */
function ymc_captain_email($heading, $intro, $content, $footnote = '') {
 if (function_exists('ymc_team_request_email')) return ymc_team_request_email($heading,$intro,$content,$footnote);
 $purple = '#241046'; $gold = '#d4af37'; $ink = '#1c1c1c'; $muted = '#5c5c5c';
 return '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"></head>'
  .'<body style="margin:0;padding:0;background:#f4f4f6">'
  .'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f6;padding:24px 12px">'
  .'<tr><td align="center">'
  .'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:6px;overflow:hidden">'
  .'<tr><td style="background:'.$purple.';padding:24px 28px">'
  .'<p style="margin:0;font:700 11px/1.4 Arial,Helvetica,sans-serif;letter-spacing:.18em;text-transform:uppercase;color:'.$gold.'">YMC at M&amp;T Bank Stadium</p>'
  .'<p style="margin:8px 0 0;font:700 21px/1.3 Arial,Helvetica,sans-serif;color:#ffffff">'.esc_html($heading).'</p>'
  .'</td></tr>'
  .'<tr><td style="padding:28px">'
  .'<p style="margin:0 0 18px;font:400 15px/1.6 Arial,Helvetica,sans-serif;color:'.$ink.'">'.$intro.'</p>'
  .$content
  .($footnote ? '<p style="margin:22px 0 0;padding-top:18px;border-top:1px solid #e6e6e9;font:400 13px/1.6 Arial,Helvetica,sans-serif;color:'.$muted.'">'.$footnote.'</p>' : '')
  .'</td></tr>'
  .'<tr><td style="background:#faf9fb;padding:16px 28px;border-top:1px solid #e6e6e9">'
  .'<p style="margin:0;font:400 12px/1.6 Arial,Helvetica,sans-serif;color:'.$muted.'">Yeshivas Mekor Chaim &middot; 7401 Park Heights Ave, Baltimore, MD 21208</p>'
  .'</td></tr></table></td></tr></table></body></html>';
}

/* Records are written by the form and only read in the admin, so the post type
   carries no editor: the details show as a read-only panel instead. */
add_action('init', function () {
 register_post_type('ymc_captain_request', ['label'=>'Captain Requests','public'=>false,'show_ui'=>true,'supports'=>['title'],'capabilities'=>['create_posts'=>'do_not_allow'],'map_meta_cap'=>true]);
});

add_action('add_meta_boxes', function () {
 add_meta_box('ymc_captain_request_detail','Request', function ($post) {
  echo '<pre style="white-space:pre-wrap;margin:0;font-family:inherit;font-size:14px;line-height:1.6">'.esc_html($post->post_content).'</pre>';
  $rows = ['Captain'=>'ymc_captain_name','Captain email'=>'ymc_captain_email','Submitted by themselves'=>'ymc_is_captain','Color held'=>'ymc_colour','Status'=>'ymc_status','Team name'=>'ymc_team_name','Team link'=>'ymc_team_url','Team link sent'=>'ymc_link_sent','Team ID'=>'ymc_team_id','Rename notices sent'=>'ymc_rename_count','Notification'=>'ymc_notification_sent','Sent to'=>'ymc_notification_recipient','Confirmation to captain'=>'ymc_confirmation_sent'];
  echo '<table class="widefat striped" style="margin-top:16px"><tbody>';
  foreach ($rows as $label=>$meta) printf('<tr><td style="width:200px"><strong>%s</strong></td><td>%s</td></tr>', esc_html($label), esc_html(get_post_meta($post->ID,$meta,true) ?: '-'));
  echo '</tbody></table>';
  $colour = (string) get_post_meta($post->ID,'ymc_colour',true);
  $status = (string) get_post_meta($post->ID,'ymc_status',true);
  if ($colour && 'cancelled' !== $status) {
   echo '<form method="post" style="margin-top:16px">';
   wp_nonce_field('ymc_captain_release','ymc_release_nonce');
   echo '<input type="hidden" name="ymc_release_request" value="'.esc_attr($post->ID).'">';
   echo get_submit_button('Cancel request and release '.$colour,'delete small','submit',false);
   echo '<p class="description">Frees the color so another captain can request it. The record stays.</p></form>';
  }
  /* The poll only matches a team named after the captain. Anything else has to
     be sent by hand, so the way to do that lives next to the record. */
  if ('yes' !== get_post_meta($post->ID,'ymc_link_sent',true) && 'cancelled' !== $status) {
   echo '<form method="post" style="margin-top:20px;padding-top:16px;border-top:1px solid #dcdcde">';
   wp_nonce_field('ymc_captain_send_link','ymc_send_link_nonce');
   echo '<input type="hidden" name="ymc_send_link_request" value="'.esc_attr($post->ID).'">';
   echo '<p><strong>Team created in Givebutter?</strong></p>';
   echo '<p><label>Team name<br><input type="text" name="ymc_team_name" class="regular-text" value="'.esc_attr(get_the_title($post)).'"></label></p>';
   echo '<p><label>Team link<br><input type="url" name="ymc_team_url" class="regular-text" placeholder="https://givebutter.com/..."></label></p>';
   echo get_submit_button('Send the captain their team link','primary small','submit',false);
   echo '<p class="description">Only needed if the team was not named after the captain. Otherwise this sends on its own within five minutes of the team appearing in Givebutter.</p></form>';
  }
 },'ymc_captain_request','normal','high');
});

/* Releasing runs on the edit screen rather than a row action so the person doing
   it is looking at the request they are cancelling. */
add_action('admin_init', function () {
 if (empty($_POST['ymc_release_request'])) return;
 if (!current_user_can('manage_options')) return;
 check_admin_referer('ymc_captain_release','ymc_release_nonce');
 $id = absint($_POST['ymc_release_request']);
 if (!$id || 'ymc_captain_request' !== get_post_type($id)) return;
 $colour = (string) get_post_meta($id,'ymc_colour',true);
 /* Only release a colour this request actually holds, or cancelling an old
    record would free a colour a later captain is relying on. */
 if ($colour && ymc_captain_colour_holder($colour) === (string) $id) ymc_captain_release_colour($colour);
 update_post_meta($id,'ymc_status','cancelled');
 wp_safe_redirect(add_query_arg('ymc_released','1',get_edit_post_link($id,'raw')));
 exit;
});

/**
 * Trashing or deleting a request frees its colour too.
 *
 * Releasing only from the Cancel button strands a colour the moment a record is
 * removed any other way, which is exactly what happens while testing. The holder
 * check means deleting an old record cannot free a colour a later captain holds.
 */
function ymc_captain_release_for_post($id) {
 if ('ymc_captain_request' !== get_post_type($id)) return;
 $colour = (string) get_post_meta($id,'ymc_colour',true);
 if ($colour && ymc_captain_colour_holder($colour) === (string) $id) ymc_captain_release_colour($colour);
}
add_action('wp_trash_post','ymc_captain_release_for_post');
add_action('before_delete_post','ymc_captain_release_for_post');

/* Restoring from the trash takes the colour back only if it is still free.
   Otherwise the record returns flagged, rather than quietly handing the same
   colour to two captains. */
add_action('untrashed_post', function ($id) {
 if ('ymc_captain_request' !== get_post_type($id)) return;
 $colour = (string) get_post_meta($id,'ymc_colour',true);
 if (!$colour) return;
 if (ymc_captain_claim_colour($colour,$id) || ymc_captain_colour_holder($colour) === (string) $id) {
  update_post_meta($id,'ymc_status','pending');
  return;
 }
 update_post_meta($id,'ymc_status','colour taken by another request');
});

/**
 * The captain's team link, sent once their team exists in Givebutter.
 *
 * Deliberately not the same mail as inc/captain-invitations.php: that one fires
 * on a paid "(Captain Only)" ticket and tells a captain to invite players. This
 * one fires earlier, when staff have built the team, and tells the captain to
 * go and register themselves under it.
 */
function ymc_captain_link_email($name, $team, $url, $colour = '') {
 /* The button points at registration, not at the team page. Team URLs carry a
    generated slug (blue-27, red-25) that changes when YMC renames the team to a
    sponsor, which would break every link already emailed. The registration
    address never moves, and the captain picks the team by name on arrival. */
 $register = defined('YMC\\CaptainInvitations\\REGISTRATION')
  ? constant('YMC\\CaptainInvitations\\REGISTRATION')
  : 'https://givebutter.com/ymc-adult-player-registration/register';
 $steps = '<p style="margin:0 0 16px;font:400 15px/1.6 Arial,Helvetica,sans-serif;color:#1c1c1c">Your team <strong>'.esc_html($team).'</strong> is ready. Register yourself as a player under it to complete your spot.</p>'
  .'<ol style="margin:0 0 18px;padding-left:20px;font:400 15px/1.7 Arial,Helvetica,sans-serif;color:#1c1c1c">'
  .'<li>Open the registration link below.</li>'
  .'<li>Choose <strong>Join a team</strong>.</li>'
  .'<li>Select <strong>'.esc_html($team).'</strong>.</li>'
  .'<li>Choose <strong>Adult Tournament &mdash; '.esc_html($colour ?: $team).' (Captain Only)</strong> and complete payment.</li></ol>'
  .'<p style="margin:0"><a href="'.esc_url($register).'" style="display:inline-block;padding:15px 24px;background:#241046;color:#ffffff;text-decoration:none;border-radius:4px;font:700 13px/1.3 Arial,Helvetica,sans-serif;letter-spacing:.08em;text-transform:uppercase">Register under my team</a></p>'
  .'<p style="margin:16px 0 0;font:400 12px/1.6 Arial,Helvetica,sans-serif;color:#5c5c5c;overflow-wrap:anywhere">Or open: <a href="'.esc_url($register).'">'.esc_html($register).'</a></p>'
  .($url ? '<p style="margin:14px 0 0;font:400 13px/1.6 Arial,Helvetica,sans-serif;color:#5c5c5c;overflow-wrap:anywhere">Your team page: <a href="'.esc_url($url).'">'.esc_html($url).'</a></p>' : '')
  /* The captain is the one who briefs their players, so the players' steps go
     here rather than relying on each of them reading the website. Their ticket
     differs from the captain's, which is the mistake worth preventing. */
  .'<div style="margin:26px 0 0;padding:18px 20px;background:#faf8fc;border:1px solid #e6e0ee;border-radius:6px">'
  .'<p style="margin:0 0 10px;font:700 13px/1.4 Arial,Helvetica,sans-serif;letter-spacing:.1em;text-transform:uppercase;color:#241046">For your players</p>'
  .'<p style="margin:0 0 10px;font:400 14px/1.6 Arial,Helvetica,sans-serif;color:#1c1c1c">Send them these steps. Their ticket is not the same as yours.</p>'
  .'<ol style="margin:0;padding-left:20px;font:400 14px/1.7 Arial,Helvetica,sans-serif;color:#1c1c1c">'
  .'<li>Open the same registration link.</li>'
  .'<li>Choose <strong>Join a team</strong> and select <strong>'.esc_html($team).'</strong>.</li>'
  .'<li>Choose <strong>Adult Tournament &mdash; Player</strong> and complete payment.</li></ol></div>';
 return ymc_captain_email('Your team is ready','Hi '.esc_html($name).', your team has been set up.',$steps,
  'YMC may add sponsor branding to the team later; that does not affect your registration or theirs.');
}

/**
 * The captain's own name and address, which may not be the submitter's.
 *
 * Someone can request a team for their captain, and it is the captain who buys
 * the (Captain Only) ticket - so the link has to reach them, not the person who
 * filled in the form. Older requests have neither field and fall back.
 */
function ymc_captain_target($id) {
 $email = sanitize_email((string) get_post_meta($id,'ymc_captain_email',true));
 if (is_email($email)) return [$email, (string) get_post_meta($id,'ymc_captain_name',true)];
 return [(string) get_post_meta($id,'ymc_email',true), (string) get_post_meta($id,'ymc_name',true)];
}

/** Copies in the person who asked, when that is not the captain themselves. */
function ymc_captain_cc($id, $to) {
 $sub = sanitize_email((string) get_post_meta($id,'ymc_email',true));
 return (is_email($sub) && strtolower($sub) !== strtolower($to)) ? ['Cc: '.$sub] : [];
}

/** Sends the link, records the outcome, and never sends twice for one request. */
function ymc_captain_send_link($id, $team, $url, $team_id = '') {
 if ('yes' === get_post_meta($id,'ymc_link_sent',true)) return false;
 list($email, $name) = ymc_captain_target($id);
 if (!is_email($email)) return false;
 $sent = wp_mail($email,'YMC: Your team is ready — register now',ymc_captain_link_email($name,$team,$url,(string) get_post_meta($id,'ymc_colour',true)),
  array_merge(['Content-Type: text/html; charset=UTF-8','Reply-To: '.ymc_captain_request_recipient()], ymc_captain_cc($id,$email)));
 update_post_meta($id,'ymc_link_sent',$sent ? 'yes':'no');
 update_post_meta($id,'ymc_team_name',$team);
 update_post_meta($id,'ymc_team_url',$url);
 /* The id is what survives a rename; the colour and the name both stop being
    reliable the moment YMC swaps in sponsor branding. */
 if ($team_id) update_post_meta($id,'ymc_team_id',(string) $team_id);
 if ($sent) update_post_meta($id,'ymc_status','team created, link sent');
 return (bool) $sent;
}

/**
 * Tells a captain their team was renamed, so their players look for the new name.
 *
 * Without this the captain keeps directing players to a name that no longer
 * appears in the team list, and the players have nothing to pick.
 */
function ymc_captain_send_rename($id, $was, $now, $url) {
 $email = (string) get_post_meta($id,'ymc_email',true);
 $name  = (string) get_post_meta($id,'ymc_name',true);
 if (!is_email($email)) return false;
 $register = defined('YMC\\CaptainInvitations\\REGISTRATION')
  ? constant('YMC\\CaptainInvitations\\REGISTRATION')
  : 'https://givebutter.com/ymc-adult-player-registration/register';
 $body = '<p style="margin:0 0 16px;font:400 15px/1.6 Arial,Helvetica,sans-serif;color:#1c1c1c">Your team is now called <strong>'.esc_html($now).'</strong>. It was <strong>'.esc_html($was).'</strong>.</p>'
  .'<p style="margin:0 0 18px;font:400 15px/1.6 Arial,Helvetica,sans-serif;color:#1c1c1c">It is the same team, with the same members and the same total. Only the name has changed. Please tell your players to look for <strong>'.esc_html($now).'</strong> when they choose <strong>Join a team</strong>.</p>'
  .'<p style="margin:0"><a href="'.esc_url($register).'" style="display:inline-block;padding:15px 24px;background:#241046;color:#ffffff;text-decoration:none;border-radius:4px;font:700 13px/1.3 Arial,Helvetica,sans-serif;letter-spacing:.08em;text-transform:uppercase">Open registration</a></p>'
  .($url ? '<p style="margin:16px 0 0;font:400 13px/1.6 Arial,Helvetica,sans-serif;color:#5c5c5c;overflow-wrap:anywhere">Your team page: <a href="'.esc_url($url).'">'.esc_html($url).'</a></p>' : '');
 $sent = wp_mail($email,'YMC: Your team is now called '.$now,
  ymc_captain_email('Your team has a new name','Hi '.esc_html($name).', a quick update about your team.',$body,
   'Nobody needs to register again. Players who already joined stay on the team.'),
  ['Content-Type: text/html; charset=UTF-8','Reply-To: '.ymc_captain_request_recipient()]);
 if ($sent) {
  update_post_meta($id,'ymc_team_name',$now);
  update_post_meta($id,'ymc_team_url',$url);
  $count = (int) get_post_meta($id,'ymc_rename_count',true);
  update_post_meta($id,'ymc_rename_count',$count + 1);
 }
 return (bool) $sent;
}

/**
 * Looks for a Givebutter team matching each pending request and sends the link.
 *
 * Matching is on the team name against the captain's name, because that is how
 * staff are told to create it. A team renamed to a sponsor later will not match,
 * which is harmless: by then the link has already gone. Where the name does not
 * match, staff send it by hand from the request screen.
 */
function ymc_captain_poll_teams() {
 if (!function_exists('YMC\\CaptainInvitations\\collection')) return;
 $pending = get_posts([
  'post_type'   => 'ymc_captain_request',
  'post_status' => 'private',
  'numberposts' => 50,
  'meta_query'  => [
   ['key'=>'ymc_link_sent','compare'=>'NOT EXISTS'],
   ['key'=>'ymc_status','value'=>'pending'],
  ],
 ]);
 if (!$pending) return;
 try {
  /* The numeric campaign id, not the six-letter campaign code. Givebutter's
     campaign sub-resources answer 404 to the code - campaigns/RMSQHT/teams
     fails where campaigns/729740/teams returns all twelve. */
  $teams = call_user_func('YMC\\CaptainInvitations\\collection', 'campaigns/'.YMC_CAPTAIN_CAMPAIGN_ID.'/teams');
 } catch (\Throwable $e) {
  /* Givebutter being unreachable is not an error worth surfacing: the next run retries. */
  return;
 }
 /* Work out every request/team pairing first, then only act on the ones that
    are unambiguous. Sending as soon as a match is found would mail the wrong
    captain the moment two of them share a first name. */
 /* Colour is the strongest signal there is: exactly one captain can hold each
    one, so a colour in the team name identifies its captain outright. Names are
    only consulted when the team name carries no colour at all. */
 $pairs = [];
 foreach ($pending as $post) {
  $name = (string) get_post_meta($post->ID,'ymc_name',true);
  $colour = (string) get_post_meta($post->ID,'ymc_colour',true);
  foreach ($teams as $team) {
   if (empty($team['url']) || empty($team['name'])) continue;
   $teamName = (string) $team['name'];
   $teamColour = ymc_captain_colour_in_team($teamName);
   if ('' !== $teamColour) {
    if ($colour && 0 === strcasecmp($teamColour, $colour)) {
     $pairs[] = ['post'=>$post->ID,'team'=>$teamName,'url'=>(string) $team['url'],'id'=>(string) ($team['id'] ?? '')];
    }
    /* A team that names a different captain's colour is theirs, not this one's,
       whatever the rest of the name says. */
    continue;
   }
   if ($name && ymc_captain_name_in_team($name, $teamName)) {
    $pairs[] = ['post'=>$post->ID,'team'=>$teamName,'url'=>(string) $team['url'],'id'=>(string) ($team['id'] ?? '')];
   }
  }
 }
 foreach ($pairs as $pair) {
  $sameRequest = 0; $sameTeam = 0;
  foreach ($pairs as $other) {
   if ($other['post'] === $pair['post']) $sameRequest++;
   if ($other['team'] === $pair['team']) $sameTeam++;
  }
  /* One captain matching two teams, or one team matching two captains, is a
     guess. Staff resolve those by hand from the request screen. */
  if (1 !== $sameRequest || 1 !== $sameTeam) {
   update_post_meta($pair['post'],'ymc_status','team match unclear, send link manually');
   continue;
  }
  ymc_captain_send_link($pair['post'], $pair['team'], $pair['url'], $pair['id']);
 }
 ymc_captain_check_renames($teams);
 ymc_captain_release_deleted_teams($teams);
}

/**
 * Frees a colour whose team no longer exists in Givebutter.
 *
 * Deleting the Blue team should put Blue back on the form. Which test applies
 * depends on how far the request got:
 *
 * - link already sent: judged on the stored team id, so a team renamed to a
 *   sponsor keeps its hold. Judging on the colour would free it the moment the
 *   word "Blue" left the team name.
 * - not yet matched: judged on whether any team still carries that colour.
 *
 * Only ever called with a successful Givebutter read. Running it on a failed
 * one would read "no teams" as "every team deleted" and empty every hold.
 */
function ymc_captain_release_deleted_teams($teams) {
 $ids = [];
 $colours = [];
 foreach ($teams as $team) {
  if (!empty($team['id'])) $ids[(string) $team['id']] = true;
  $c = ymc_captain_colour_in_team((string) ($team['name'] ?? ''));
  if ('' !== $c) $colours[strtolower($c)] = true;
 }
 /* An empty campaign is more likely a bad read than a deliberate wipe. */
 if (!$ids) return;
 $requests = get_posts([
  'post_type'   => 'ymc_captain_request',
  'post_status' => 'private',
  'numberposts' => 100,
  'meta_query'  => [['key'=>'ymc_colour','compare'=>'EXISTS']],
 ]);
 foreach ($requests as $post) {
  $colour = (string) get_post_meta($post->ID,'ymc_colour',true);
  if ('' === $colour) continue;
  if (ymc_captain_colour_holder($colour) !== (string) $post->ID) continue;
  $teamId = (string) get_post_meta($post->ID,'ymc_team_id',true);
  $gone = '' !== $teamId ? !isset($ids[$teamId]) : !isset($colours[strtolower($colour)]);
  if (!$gone) continue;
  ymc_captain_release_colour($colour);
  update_post_meta($post->ID,'ymc_status','team deleted in Givebutter, '.$colour.' released');
 }
}

/**
 * Re-mails any captain whose team has been renamed since their link went out.
 *
 * Tracked by team id, because that is the only thing a rename does not change.
 * A renamed team keeps its members and its total, so this is an informational
 * message, not a second invitation.
 */
function ymc_captain_check_renames($teams) {
 $byId = [];
 foreach ($teams as $team) {
  if (!empty($team['id'])) $byId[(string) $team['id']] = $team;
 }
 if (!$byId) return;
 $sent = get_posts([
  'post_type'   => 'ymc_captain_request',
  'post_status' => 'private',
  'numberposts' => 100,
  'meta_query'  => [['key'=>'ymc_team_id','compare'=>'EXISTS']],
 ]);
 foreach ($sent as $post) {
  if ('yes' !== get_post_meta($post->ID,'ymc_link_sent',true)) continue;
  $teamId = (string) get_post_meta($post->ID,'ymc_team_id',true);
  if ('' === $teamId || !isset($byId[$teamId])) continue;
  $known = (string) get_post_meta($post->ID,'ymc_team_name',true);
  $now   = (string) ($byId[$teamId]['name'] ?? '');
  if ('' === $now || $now === $known) continue;
  ymc_captain_send_rename($post->ID, $known, $now, (string) ($byId[$teamId]['url'] ?? ''));
 }
}

/**
 * The team colour named in a team title, or '' when it names none.
 *
 * Only one captain can hold a colour, so this is an exact identifier rather than
 * a guess - "Blue Thunder", "Team Blue" and "Blue" all belong to whoever holds
 * Blue. A title naming two colours is not an identifier at all, so it is
 * rejected rather than resolved to the first one found.
 */
function ymc_captain_colour_in_team($team) {
 $team = strtolower(trim(preg_replace('/[^a-z0-9 ]+/iu',' ', (string) $team)));
 if ('' === $team) return '';
 $found = '';
 foreach (ymc_captain_colours() as $colour) {
  if (!preg_match('/\b'.preg_quote(strtolower($colour),'/').'\b/u', $team)) continue;
  if ('' !== $found) return '';
  $found = $colour;
 }
 return $found;
}

/**
 * Does this team belong to this captain?
 *
 * Staff name teams freely - "Nikka", "Nikka Team", "Nikka Cutie Team" should all
 * reach Nikka. So the captain's name is matched loosely: the whole name, or
 * failing that their first name, appearing as whole words anywhere in the team
 * name. Whole words matter, or "Al" would claim "Alex United".
 *
 * Short first names are not matched on their own. "Jo" sits inside too many
 * ordinary words to be evidence of anything, and a wrong match here emails a
 * stranger someone else's team link.
 */
function ymc_captain_name_in_team($captain, $team) {
 $clean = function ($s) {
  $s = strtolower(trim(preg_replace('/\s+/u',' ', (string) $s)));
  return trim(preg_replace('/[^a-z0-9 ]+/u',' ', $s));
 };
 $captain = $clean($captain);
 $team = $clean($team);
 if ('' === $captain || '' === $team) return false;
 $whole = '/\b'.preg_quote($captain,'/').'\b/u';
 if (preg_match($whole, $team)) return true;
 $parts = array_values(array_filter(explode(' ', $captain)));
 if (!$parts) return false;
 $first = $parts[0];
 if (strlen($first) < 3) return false;
 return 1 === preg_match('/\b'.preg_quote($first,'/').'\b/u', $team);
}
add_action('ymc_captain_poll_teams','ymc_captain_poll_teams');

add_filter('cron_schedules', function ($s) {
 $s['ymc_one_minute'] = ['interval'=>60,'display'=>'Every minute'];
 return $s;
});
register_activation_hook(__FILE__, function () {
 if (!wp_next_scheduled('ymc_captain_poll_teams')) wp_schedule_event(time()+60,'ymc_one_minute','ymc_captain_poll_teams');
});
register_deactivation_hook(__FILE__, function () {
 wp_clear_scheduled_hook('ymc_captain_poll_teams');
});

/* Staff who named the team something else need a way through; the poll would
   never match it. */
add_action('admin_init', function () {
 if (empty($_POST['ymc_send_link_request'])) return;
 if (!current_user_can('manage_options')) return;
 check_admin_referer('ymc_captain_send_link','ymc_send_link_nonce');
 $id = absint($_POST['ymc_send_link_request']);
 if (!$id || 'ymc_captain_request' !== get_post_type($id)) return;
 $team = sanitize_text_field(wp_unslash($_POST['ymc_team_name'] ?? ''));
 $url  = esc_url_raw(wp_unslash($_POST['ymc_team_url'] ?? ''));
 if ($team && $url) ymc_captain_send_link($id,$team,$url);
 wp_safe_redirect(get_edit_post_link($id,'raw'));
 exit;
});

add_action('admin_menu', function () {
 add_submenu_page('edit.php?post_type=ymc_captain_request','Captain Request Settings','Settings','manage_options','ymc-captain-requests', function () {
  if (!current_user_can('manage_options')) return;
  if (isset($_GET['settings-updated'])) add_settings_error('ymc_captain_request','ymc_saved','Settings saved.','success');
  echo '<div class="wrap"><h1>Captain Request Settings</h1>';
  settings_errors('ymc_captain_request');
  printf('<p>Requests are currently emailed to <strong>%s</strong>%s.</p>', esc_html(ymc_captain_request_recipient()),
   is_email((string) get_option('ymc_captain_request_recipient','')) ? '' : ' (the built-in default, because no address is set)');
  /* Read from the same source as the public form. Showing the stored hold
     instead made a colour someone had paid for read as merely reserved. */
  $states  = ymc_captain_colour_states();
  $paid    = (array) ymc_captain_paid_colours();
  $tickets = ymc_captain_ticket_colours();
  echo '<h2>Colors</h2><table class="widefat striped" style="max-width:720px"><thead><tr><th>Color</th><th>Status</th><th>Detail</th></tr></thead><tbody>';
  foreach (ymc_captain_colours() as $colour) {
   if (!isset($states[$colour])) {
    printf('<tr><td>%s</td><td>&mdash;</td><td class="description">Not shown: no &ldquo;(Captain Only)&rdquo; ticket for this color in Givebutter.</td></tr>', esc_html($colour));
    continue;
   }
   if ('taken' === $states[$colour]) {
    $status = '<strong style="color:#1d6f42">Taken</strong>';
    $detail = 'Captain ticket purchased' . (!empty($paid[$colour]) ? ' by ' . esc_html($paid[$colour]) : '');
   } elseif ('reserved' === $states[$colour]) {
    $holder = ymc_captain_colour_holder($colour);
    $status = '<strong style="color:#8a6d1f">Reserved</strong>';
    $detail = 'reserved' === $holder ? 'Held manually' : 'Requested &mdash; request #' . esc_html($holder) . ', awaiting payment';
   } else {
    $status = 'Available';
    $detail = '';
   }
   printf('<tr><td>%s</td><td>%s</td><td class="description">%s</td></tr>', esc_html($colour), $status, $detail);
  }
  echo '</tbody></table>';
  if (null === $tickets) echo '<p class="description">Givebutter&rsquo;s ticket list could not be read just now, so every color in the list is being offered.</p>';
  echo '<form action="options.php" method="post">';
  settings_fields('ymc_captain_request');
  do_settings_sections('ymc-captain-requests');
  submit_button();
  echo '</form></div>';
 });
});

add_action('admin_init', function () {
 register_setting('ymc_captain_request','ymc_captain_request_recipient',['type'=>'string','sanitize_callback'=>'sanitize_email','default'=>'']);
 add_settings_section('ymc_captain_request_main','','__return_false','ymc-captain-requests');
 add_settings_field('ymc_captain_request_recipient','Notification email', function () {
  printf('<input type="email" id="ymc_captain_request_recipient" name="ymc_captain_request_recipient" value="%s" class="regular-text" placeholder="%s"><p class="description">Where captain team requests are emailed. Leave empty to use %s.</p>',
   esc_attr((string) get_option('ymc_captain_request_recipient','')), esc_attr(YMC_CAPTAIN_REQUEST_FALLBACK), esc_html(YMC_CAPTAIN_REQUEST_FALLBACK));
 },'ymc-captain-requests','ymc_captain_request_main');

 /* Stored as text rather than a repeater: adding or dropping a colour is a
    rare, deliberate edit, and a line per colour is obvious to anyone. */
 register_setting('ymc_captain_request','ymc_captain_colour_list',['type'=>'string','sanitize_callback'=>'ymc_captain_sanitize_colour_list','default'=>'']);
 add_settings_field('ymc_captain_colour_list','Team colors', function () {
  $value = (string) get_option('ymc_captain_colour_list','');
  if ('' === $value) {
   $lines = [];
   foreach (ymc_captain_default_colours() as $name => $hex) { $lines[] = $name.' '.$hex; }
   $value = implode("\n", $lines);
  }
  printf('<textarea id="ymc_captain_colour_list" name="ymc_captain_colour_list" rows="13" class="large-text code">%s</textarea>', esc_textarea($value));
  echo '<p class="description">One color per line, as <code>Name #hex</code>. Delete a line to stop offering that color; the hex sets the dot shown beside the name. '
   . 'A color only appears on the form if a matching team also exists in Givebutter. Removing a line never affects a request that already holds that color.</p>';
 },'ymc-captain-requests','ymc_captain_request_main');
});

/** Keeps the stored list to one clean "Name #hex" per line. */
function ymc_captain_sanitize_colour_list($value) {
 $out = [];
 foreach (preg_split('/\r\n|\r|\n/', (string) $value) as $line) {
  $line = trim(sanitize_text_field($line));
  if ('' === $line) { continue; }
  if (preg_match('/^(.*?)\s+(#[0-9a-fA-F]{3,6})$/', $line, $m)) {
   $out[] = trim($m[1]).' '.strtolower($m[2]);
  } else {
   $out[] = $line;
  }
 }
 return implode("\n", $out);
}

/* A failed send is otherwise invisible: requests would pile up privately with nobody notified. */
add_filter('manage_ymc_captain_request_posts_columns', function ($cols) {
 $cols['ymc_colour'] = 'Color';
 $cols['ymc_status'] = 'Status';
 $cols['ymc_notification'] = 'Notification';
 return $cols;
});

add_action('manage_ymc_captain_request_posts_custom_column', function ($col,$id) {
 if ('ymc_colour' === $col) { echo esc_html(get_post_meta($id,'ymc_colour',true) ?: '—'); return; }
 if ('ymc_status' === $col) {
  echo esc_html(get_post_meta($id,'ymc_status',true) ?: 'pending');
  /* The admin notice is gone on the next page load, so the row carries the
     record of a resend instead. */
  if ($n = (int) get_post_meta($id,'ymc_link_resent_count',true)) {
   $at = (string) get_post_meta($id,'ymc_link_resent_at',true);
   echo '<br><span class="description">resent '.esc_html($n > 1 ? $n.' times' : 'once');
   if ($at) echo ', last '.esc_html(mysql2date(get_option('date_format').' g:i a',$at));
   echo '</span>';
  }
  return;
 }
 if ('ymc_notification' !== $col) return;
 $sent = get_post_meta($id,'ymc_notification_sent',true);
 if ('yes' === $sent) {
  echo 'Sent';
  if ($to = get_post_meta($id,'ymc_notification_recipient',true)) echo '<br><span class="description">'.esc_html($to).'</span>';
  return;
 }
 echo 'no' === $sent ? '<strong style="color:#b32d2e">Failed</strong>' : '&mdash;';
},10,2);

/**
 * Lets staff re-send a captain's team link from the list screen.
 *
 * Mail goes astray - a typo in the address, a spam folder, a captain who
 * deleted it. Without this the only repair is to rebuild the email by hand or
 * to clear the sent flag in the database, so in practice captains were simply
 * left waiting.
 */
function ymc_captain_resend_link($id) {
 $team = (string) get_post_meta($id,'ymc_team_name',true);
 /* No team yet means there is nothing to point the captain at; the cron sends
    the link by itself the moment the team appears. */
 if ('' === $team) return 'noteam';
 list($email, $name) = ymc_captain_target($id);
 if (!is_email($email)) return 'noemail';
 $sent = wp_mail($email,'YMC: Your team is ready — register now',
  ymc_captain_link_email($name,$team,(string) get_post_meta($id,'ymc_team_url',true),(string) get_post_meta($id,'ymc_colour',true)),
  array_merge(['Content-Type: text/html; charset=UTF-8','Reply-To: '.ymc_captain_request_recipient()], ymc_captain_cc($id,$email)));
 /* Kept in step with the first send so a failed resend cannot leave the row
    claiming the link went out. */
 update_post_meta($id,'ymc_link_sent',$sent ? 'yes':'no');
 if ($sent) {
  update_post_meta($id,'ymc_status','team created, link sent');
  update_post_meta($id,'ymc_link_resent_at',current_time('mysql'));
  $n = (int) get_post_meta($id,'ymc_link_resent_count',true);
  update_post_meta($id,'ymc_link_resent_count',$n + 1);
 }
 return $sent ? 'sent' : 'failed';
}

add_filter('post_row_actions', function ($actions, $post) {
 if ('ymc_captain_request' !== $post->post_type || !current_user_can('edit_post',$post->ID)) return $actions;
 if ('' === (string) get_post_meta($post->ID,'ymc_team_name',true)) return $actions;
 $url = wp_nonce_url(admin_url('admin-post.php?action=ymc_captain_resend&id='.$post->ID),'ymc_captain_resend_'.$post->ID);
 $actions['ymc_resend'] = '<a href="'.esc_url($url).'">Resend link</a>';
 return $actions;
},10,2);

add_action('admin_post_ymc_captain_resend', function () {
 $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
 check_admin_referer('ymc_captain_resend_'.$id);
 if (!$id || !current_user_can('edit_post',$id) || 'ymc_captain_request' !== get_post_type($id)) wp_die('Not allowed.');
 wp_safe_redirect(add_query_arg('ymc_resent',ymc_captain_resend_link($id),admin_url('edit.php?post_type=ymc_captain_request')));
 exit;
});

add_action('admin_notices', function () {
 if (!isset($_GET['ymc_resent']) || 'ymc_captain_request' !== get_query_var('post_type')) return;
 $notices = [
  'sent'    => ['updated','The team link was sent again.'],
  'failed'  => ['error','That email could not be sent. Check the mail setup and try again.'],
  'noteam'  => ['error','No team has been created for that request yet, so there is no link to send.'],
  'noemail' => ['error','That request has no valid email address on it.'],
 ];
 $key = sanitize_key(wp_unslash($_GET['ymc_resent']));
 if (!isset($notices[$key])) return;
 printf('<div class="notice notice-%s is-dismissible"><p>%s</p></div>',esc_attr('updated'===$notices[$key][0]?'success':'error'),esc_html($notices[$key][1]));
});

/* Every rejection used to redirect identically, so a taken colour looked exactly
   like a typo and the visitor was told nothing they could act on. */
function ymc_captain_request_message($reason) {
 $messages = [
  'throttled' => 'You have just sent a request. Please wait a minute before sending another.',
  'invalid'   => 'Please check your details and try again.',
  'expired'   => 'That form had been open a while and the session expired. Please try again.',
  'taken'     => 'That color was claimed while you were filling in the form. Please choose another.',
  'nocolour'  => 'Please choose a team color.',
  'nocaptain' => 'Please give your captain&rsquo;s full name and email address.',
 ];
 return isset($messages[$reason]) ? $messages[$reason] : 'We could not submit your request. Please try again or email '.YMC_CAPTAIN_REQUEST_FALLBACK.'.';
}

add_shortcode('ymc_captain_request', function () {
 $status = isset($_GET['captain_request']) ? sanitize_key($_GET['captain_request']) : '';
 $states    = ymc_captain_colour_states();
 $available = array_keys(array_filter($states, function ($state) { return 'free' === $state; }));
 ob_start(); ?>
 <section class="ymct-page" aria-labelledby="ymcc-title">
 <div class="ymct-shell">
 <div class="ymct-intro">
 <p class="ymct-eyebrow">YMC AT M&amp;T &middot; ADULT TOURNAMENT</p>
 <h1 id="ymcc-title">Lead a team.<br>Pick your color.</h1>
 <p class="ymct-lead">Captains start here. Request your team and YMC will set it up for you.</p>
 <ol class="ymct-steps">
 <li><strong>Request your team</strong><span>Choose a color and tell us about your team.</span></li>
 <li><strong>YMC creates it</strong><span>We set up your team and send your registration link.</span></li>
 <li><strong>Register as a player</strong><span>Use that link to register yourself under your team.</span></li>
 </ol>
 </div>
 <div class="ymct-card">
 <?php if ('received' === $status) : ?>
 <?php
 /* Only a colour that exists is echoed back, so a hand-edited query string
    cannot put arbitrary text on the page. */
 $held = isset($_GET['color']) ? sanitize_text_field(wp_unslash($_GET['color'])) : '';
 if (!in_array($held, ymc_captain_colours(), true)) { $held = ''; }
 /* Someone acting for their captain must not be sent to the captain ticket. */
 $proxy = isset($_GET['role']) && 'proxy' === sanitize_key(wp_unslash($_GET['role']));
 $register = defined('YMC\\CaptainInvitations\\REGISTRATION') ? constant('YMC\\CaptainInvitations\\REGISTRATION') : 'https://givebutter.com/ymc-adult-player-registration/register';
 ?>
 <div class="ymct-success" role="status"><span class="ymct-check" aria-hidden="true">&#10003;</span><p class="ymct-eyebrow">REQUEST RECEIVED</p>
 <h2><?php echo $held ? esc_html($held) . ($proxy ? ' is held.' : ' is yours.') : 'Your color is held.'; ?></h2>
 <?php /* The email repeats these steps; saying so once is enough, and the button
          means nobody has to go and find it. */ ?>
 <?php if ($proxy) : ?>
 <p>We have emailed your captain their registration link. They complete the team from there.</p>
 <?php else : ?>
 <p>Your team is ready. Register as a player to complete your place.</p>
 <?php endif; ?>
 <?php /* The same three steps the email carries. Repeating them here saves the
          captain going to find the email to do something they can do now. */ ?>
 <ol class="ymct-steps-compact">
  <li>Sign in to Givebutter or create your account.</li>
  <li>Choose <strong>Join a team</strong> and select <strong><?php echo $held ? esc_html($held) : 'your color'; ?></strong>.</li>
  <?php /* Captains buy the ticket named after their colour; anyone acting for a
           captain is a player and must not. Sending the wrong person to the
           (Captain Only) ticket is the mistake worth preventing here. */ ?>
  <?php if ($proxy) : ?>
  <li>Choose <strong>Adult Tournament &mdash; Player</strong> and complete your $180 registration.</li>
  <?php else : ?>
  <li>Pick <strong>Adult Tournament &mdash; <?php echo $held ? esc_html($held) : 'your color'; ?> (Captain Only)</strong> and complete your $180 registration.</li>
  <?php endif; ?>
 </ol>
 <?php if ($proxy) : ?>
 <p class="ymct-note">Those steps are for you, if you are playing on the team. Your captain uses the <strong>(Captain Only)</strong> ticket and must pay with the email address you gave us.</p>
 <?php endif; ?>
 <p class="ymct-note">We have emailed these steps to you as well, so you can finish later if you prefer.</p>
 <div class="ymct-actions">
  <a class="ymct-button" href="<?php echo esc_url($register); ?>" target="_blank" rel="noopener">Continue to registration</a>
  <a class="ymct-button ymct-button-alt" href="<?php echo esc_url(home_url('/')); ?>">Back to the event</a>
 </div></div>
 <?php elseif (!$available) : ?>
 <p class="ymct-eyebrow">ALL TEAMS TAKEN</p><h2>Every color is claimed.</h2><p class="ymct-card-intro">All team colors have been requested. Email <a href="mailto:<?php echo esc_attr(ymc_captain_request_recipient()); ?>"><?php echo esc_html(ymc_captain_request_recipient()); ?></a> and we will let you know if one frees up.</p>
 <?php else : ?>
 <p class="ymct-eyebrow">REQUEST YOUR TEAM</p><h2>Register your team</h2><p class="ymct-card-intro">Colors are first come, first served. Yours is held as soon as you submit.</p>
 <?php if ('error' === $status) echo '<p class="ymct-alert" role="alert">'.esc_html(ymc_captain_request_message(isset($_GET['reason']) ? sanitize_key($_GET['reason']) : '')).'</p>'; ?>
 <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
 <input type="hidden" name="action" value="ymc_captain_request">
 <?php wp_nonce_field('ymc_captain_request','ymc_nonce'); ?>
 <?php /* Named so browser autofill leaves it alone: a filled honeypot silently rejects a real person. */ ?>
 <p style="position:absolute;left:-10000px" aria-hidden="true"><label>Leave empty<input name="ymc_hp" tabindex="-1" autocomplete="off"></label></p>
 <?php /* The captain pays and their email is what we match against Givebutter,
         so we must know whether the person filling this in is that captain. */ ?>
 <p class="ymct-field" data-step="1"><label for="ymcc-iscaptain">Are you the team captain? <span>(required)</span></label>
 <select id="ymcc-iscaptain" name="is_captain" required>
 <option value="">Select an option</option>
 <option value="yes">Yes, I am the captain</option>
 <option value="no">No, I am requesting for our captain</option>
 </select></p>
 <p class="ymct-field" data-step="1"><label for="ymcc-name">Your full name <span>(required)</span></label><input id="ymcc-name" name="full_name" required maxlength="120" autocomplete="name" placeholder="Your full name"></p>
 <p class="ymct-field" data-step="1"><label for="ymcc-email">Email address <span>(required)</span></label><input id="ymcc-email" name="email" type="email" required maxlength="254" autocomplete="email" placeholder="you@example.com"></p>
 <p class="ymct-field" data-step="1"><label for="ymcc-phone">Phone number <span>(optional)</span></label><input id="ymcc-phone" name="phone" type="tel" maxlength="40" autocomplete="tel" placeholder="Your phone number"></p>
 <?php /* Radios rather than a dropdown: a select cannot show a swatch beside each
          option in any reliable way, and every colour being visible at once is
          quicker to scan than opening a list. */ ?>
 <?php /* Shown only when the answer is no. Without JavaScript both stay visible
         and optional, and the handler demands them only when no was chosen. */ ?>
 <p class="ymct-field ymcc-ifproxy" data-step="1"><label for="ymcc-capname">Your captain&rsquo;s full name <span>(required)</span></label><input id="ymcc-capname" name="captain_name" maxlength="120" placeholder="Their full name"></p>
 <p class="ymct-field ymcc-ifproxy" data-step="1"><label for="ymcc-capemail">Your captain&rsquo;s email address <span>(required)</span></label><input id="ymcc-capemail" name="captain_email" type="email" maxlength="254" placeholder="captain@example.com"><span class="ymct-note">They must use this same address when they pay in Givebutter.</span></p>
 <fieldset class="ymct-field ymcc-colours" data-step="2"><legend>Team color <span>(required)</span></legend>
 <div class="ymcc-swatches">
 <?php $first = true; foreach ($states as $colour => $state) : $gone = ('free' !== $state); ?>
  <?php /* Disabled rather than hidden, so a taken colour reads as gone rather
           than as never having been there. The server re-checks on submit, so
           re-enabling it in the browser gains nothing. */ ?>
  <label class="ymcc-swatch<?php echo $gone ? ' is-'.esc_attr($state) : ''; ?>">
   <input type="radio" name="colour" value="<?php echo esc_attr($colour); ?>"<?php
    if ($gone) { echo ' disabled'; }
    /* required belongs on the first colour that can actually be chosen. */
    elseif ($first) { echo ' required'; $first = false; } ?>>
   <span class="ymcc-dot" style="background:<?php echo esc_attr(ymc_captain_colour_hex($colour)); ?>" aria-hidden="true"></span>
   <span class="ymcc-name"><?php echo esc_html($colour); ?></span>
   <?php if ($gone) : ?><span class="ymcc-taken"><?php echo 'taken' === $state ? 'Taken' : 'Reserved'; ?></span><?php endif; ?>
  </label>
 <?php endforeach; ?>
 </div>
 <span class="ymct-note">Colors already claimed are crossed out. <em>Reserved</em> means a captain has asked for it; <em>Taken</em> means they have paid.</span></fieldset>
 <p class="ymct-field" data-step="2"><label for="ymcc-play">How competitive would your team like to play? <span>(required)</span></label>
 <select id="ymcc-play" name="competitiveness" required>
 <option value="">Select an option</option>
 <option value="Highly competitive">Highly competitive</option>
 <option value="Somewhat competitive">Somewhat competitive</option>
 <option value="Laid-back, mainly for fun">Laid-back, mainly for fun</option>
 </select></p>
 <p class="ymct-field" data-step="2"><label for="ymcc-story">Your team story <span>(optional)</span></label><textarea id="ymcc-story" name="story" rows="4" maxlength="1200" placeholder="Anything you would like on your team page"></textarea></p>
 <p class="ymct-note" id="ymcc-privacy">This is a team request only. It does not register you as a player and does not take payment. YMC names the team after you and assigns sponsor branding.</p>
 <button class="ymct-button" type="submit" aria-describedby="ymcc-privacy">Request my team</button>
 </form>
 <?php endif; ?>
 </div></div></section>
 <?php return ob_get_clean();
});

function ymc_handle_captain_request() {
 $redirect = home_url('/register-a-team/');
 $fail = function($reason = '') use ($redirect) {
  wp_safe_redirect(add_query_arg(['captain_request'=>'error','reason'=>$reason],$redirect));
  exit;
 };
 if ('POST' !== ($_SERVER['REQUEST_METHOD'] ?? '')) $fail();
 if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ymc_nonce'] ?? '')),'ymc_captain_request')) $fail('expired');
 if (!empty($_POST['ymc_hp'])) $fail();

 $name  = sanitize_text_field(wp_unslash($_POST['full_name'] ?? ''));
 $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
 $phone = sanitize_text_field(wp_unslash($_POST['phone'] ?? ''));
 $story = sanitize_textarea_field(wp_unslash($_POST['story'] ?? ''));
 $colour = sanitize_text_field(wp_unslash($_POST['colour'] ?? ''));
 $play  = sanitize_text_field(wp_unslash($_POST['competitiveness'] ?? ''));
 $isCap    = sanitize_text_field(wp_unslash($_POST['is_captain'] ?? ''));
 $capName  = sanitize_text_field(wp_unslash($_POST['captain_name'] ?? ''));
 $capEmail = sanitize_email(wp_unslash($_POST['captain_email'] ?? ''));

 if (!in_array($isCap, ['yes','no'], true)) $fail('invalid');
 /* Whoever is named here is the one who pays, so a request made for someone
    else has to identify them or there is nothing to match in Givebutter. */
 if ('no' === $isCap && (!$capName || strlen($capName)>120 || !is_email($capEmail))) $fail('nocaptain');
 if ('yes' === $isCap) { $capName = $name; $capEmail = $email; }

 if (!$name || strlen($name)>120 || !is_email($email) || strlen($phone)>40 || strlen($story)>1200) $fail('invalid');
 if (!in_array($colour, ymc_captain_colours(), true)) $fail('nocolour');
 if (!in_array($play, ['Highly competitive','Somewhat competitive','Laid-back, mainly for fun'], true)) $fail('invalid');

 /* Rate limiting protects the public form; it should not block staff testing delivery. */
 $key = 'ymc_cap_'.hash_hmac('sha256',$_SERVER['REMOTE_ADDR'] ?? '',wp_salt());
 $throttled = !current_user_can('manage_options');
 if ($throttled && get_transient($key)) $fail('throttled');

 $who = ('yes' === $isCap) ? "Submitted by the captain." : "Submitted by $name for their captain.";
 $body = "Adult captain team request\n\n$who\n\nCaptain: $capName\nCaptain email: $capEmail\nSubmitted by: $name\nEmail: $email\nPhone: $phone\nColor: $colour\nPlay style: $play\n\nStory:\n".($story ?: 'Not given')
  ."\n\nCreate the team in Givebutter under this captain's name, set the goal to \$15,000, then send them their registration link. They still need to register as a player under the team.";
 $id = wp_insert_post(['post_type'=>'ymc_captain_request','post_status'=>'private','post_title'=>$name.' — '.$colour,'post_content'=>$body],true);
 if (is_wp_error($id) || !$id) $fail();

 /* Claimed after the record exists so the claim can name the request holding it.
    If someone beat them to it the record is cancelled rather than deleted, so a
    near-miss is still visible to staff. */
 if (!ymc_captain_claim_colour($colour,$id)) {
  update_post_meta($id,'ymc_status','cancelled');
  update_post_meta($id,'ymc_colour','');
  wp_update_post(['ID'=>$id,'post_title'=>$name.' — color unavailable']);
  $fail('taken');
 }
 update_post_meta($id,'ymc_colour',$colour);
 update_post_meta($id,'ymc_status','pending');
 /* Stored as meta as well as in the body: the team-ready email needs them back
    out again, and parsing them from the text would break the moment the body
    wording changes. */
 update_post_meta($id,'ymc_name',$name);
 update_post_meta($id,'ymc_email',$email);
 update_post_meta($id,'ymc_is_captain',$isCap);
 update_post_meta($id,'ymc_captain_name',$capName);
 update_post_meta($id,'ymc_captain_email',$capEmail);
 if ($throttled) set_transient($key,1,60);

 $recipient = ymc_captain_request_recipient();
 $rows = '<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse">';
 /* Staff need the captain's address at a glance: it is what the Givebutter
    payment is matched against, and it is not always the submitter's. */
 $fieldRows = ('yes' === $isCap)
  ? ['Name'=>$name,'Email'=>$email]
  : ['Captain'=>$capName,'Captain email'=>$capEmail,'Requested by'=>$name,'Their email'=>$email];
 $fieldRows += ['Phone'=>($phone ?: 'Not given'),'Color'=>$colour,'Play style'=>$play,'Story'=>($story ?: 'Not given')];
 foreach ($fieldRows as $label=>$value) {
  $rows .= '<tr><td style="padding:9px 0;width:96px;font:700 13px/1.5 Arial,Helvetica,sans-serif;color:#5c5c5c;vertical-align:top">'.esc_html($label).'</td>'
   .'<td style="padding:9px 0;font:400 15px/1.5 Arial,Helvetica,sans-serif;color:#1c1c1c">'.nl2br(esc_html($value)).'</td></tr>';
 }
 $rows .= '</table>';
 $html = ymc_captain_email('Adult captain team request',('yes' === $isCap ? 'A captain has requested a team. ' : esc_html($name).' has requested a team for '.esc_html($capName).'. ').'The '.esc_html($colour).' color is now held.',$rows,
  'Create the team in Givebutter under the captain&rsquo;s name, set the goal to $15,000, then send them their registration link. Replying to this email reaches the captain directly.');
 $sent = wp_mail($recipient,'YMC: Adult captain team request — '.$colour,$html,['Content-Type: text/html; charset=UTF-8','Reply-To: '.$email]);
 update_post_meta($id,'ymc_notification_sent',$sent ? 'yes':'no');
 update_post_meta($id,'ymc_notification_recipient',$recipient);

 /* The captain otherwise has no proof the request went through, and no way to
    know a second step is still coming. */
 /* A captain and someone acting for one need different instructions: only the
    captain buys the (Captain Only) ticket, and telling the wrong person to buy
    it is how captains end up registered as players and players as captains. */
 if ('yes' === $isCap) {
  $steps = '<p style="margin:0;font:400 15px/1.6 Arial,Helvetica,sans-serif;color:#1c1c1c">The <strong>'.esc_html($colour).'</strong> team color is held for you. YMC will create your team and email your registration link. <strong>You still need to register as a player under your team once that link arrives.</strong></p>'
   .'<p style="margin:14px 0 0;font:400 15px/1.6 Arial,Helvetica,sans-serif;color:#1c1c1c">When it does, choose <strong>Adult Tournament &mdash; '.esc_html($colour).' (Captain Only)</strong>. That ticket is what marks you as captain.</p>';
  $receipt = ymc_captain_email('We have your team request','Hi '.esc_html($name).', thanks for stepping up as a captain for the Adult Tournament.',$steps,
   'This message confirms your team request only. It is not a player registration and no payment has been taken.');
 } else {
  $steps = '<p style="margin:0;font:400 15px/1.6 Arial,Helvetica,sans-serif;color:#1c1c1c">The <strong>'.esc_html($colour).'</strong> team color is held for <strong>'.esc_html($capName).'</strong>. We have emailed them at <strong>'.esc_html($capEmail).'</strong> with their registration link.</p>'
   .'<p style="margin:14px 0 0;font:400 15px/1.6 Arial,Helvetica,sans-serif;color:#1c1c1c">They must register using <strong>Adult Tournament &mdash; '.esc_html($colour).' (Captain Only)</strong>, and pay with that same email address so we can confirm the team is theirs.</p>'
   .'<p style="margin:14px 0 0;font:400 15px/1.6 Arial,Helvetica,sans-serif;color:#1c1c1c">If you are playing on the team yourself, choose <strong>Adult Tournament &mdash; Player</strong> and join '.esc_html($capName).'&rsquo;s team. Do not use the captain ticket.</p>';
  $receipt = ymc_captain_email('We have your team request','Hi '.esc_html($name).', thanks for requesting a team for '.esc_html($capName).'.',$steps,
   'This message confirms the team request only. It is not a player registration and no payment has been taken.');
 }
 $confirmed = wp_mail($email,'YMC: We received your team request',$receipt,['Content-Type: text/html; charset=UTF-8','Reply-To: '.$recipient]);
 update_post_meta($id,'ymc_confirmation_sent',$confirmed ? 'yes':'no');

 /* The colour teams already exist, so the captain's link can usually go out now
    rather than on the next cron run. WP-Cron only fires on a page request, so on
    a quiet site "every minute" can mean considerably longer - the scheduled run
    stays as the safety net for when the team is not there yet. */
 ymc_captain_poll_teams();

 /* The colour travels back so the confirmation can name the team to pick,
    which is the whole of what the captain has to do next. */
 wp_safe_redirect(add_query_arg(array('captain_request'=>'received','color'=>rawurlencode($colour),'role'=>('yes' === $isCap ? 'captain' : 'proxy')),$redirect)); exit;
}
add_action('admin_post_nopriv_ymc_captain_request','ymc_handle_captain_request');
add_action('admin_post_ymc_captain_request','ymc_handle_captain_request');
