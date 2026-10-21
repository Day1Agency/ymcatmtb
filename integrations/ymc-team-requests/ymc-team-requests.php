<?php
/**
 * Plugin Name: YMC Team Placement Requests
 * Description: Private adult team placement requests and email notifications.
 * Version: 1.2.0
 */
if (!defined('ABSPATH')) exit;
const YMC_TEAM_REQUEST_FALLBACK = 'ykohen@yeshivasmekorchaim.org';
/** Where notifications go. Settings value wins; the fallback keeps delivery working if it is blank. */
function ymc_team_request_recipient() {
 $email = sanitize_email((string) get_option('ymc_team_request_recipient',''));
 return is_email($email) ? $email : YMC_TEAM_REQUEST_FALLBACK;
}
/* Records are written by the form and only read in the admin. Nothing here should be retyped by
   hand, so the post type carries no editor: the details show as a read-only panel instead. */
add_action('init', function () {
 register_post_type('ymc_team_request', ['label'=>'Team Requests','public'=>false,'show_ui'=>true,'supports'=>['title'],'capabilities'=>['create_posts'=>'do_not_allow'],'map_meta_cap'=>true]);
});
add_action('add_meta_boxes', function () {
 add_meta_box('ymc_team_request_detail','Request', function ($post) {
  echo '<pre style="white-space:pre-wrap;margin:0;font-family:inherit;font-size:14px;line-height:1.6">'.esc_html($post->post_content).'</pre>';
  $rows = ['Notification'=>'ymc_notification_sent','Sent to'=>'ymc_notification_recipient','Confirmation to sender'=>'ymc_confirmation_sent'];
  echo '<table class="widefat striped" style="margin-top:16px"><tbody>';
  foreach ($rows as $label=>$meta) printf('<tr><td style="width:200px"><strong>%s</strong></td><td>%s</td></tr>', esc_html($label), esc_html(get_post_meta($post->ID,$meta,true) ?: '-'));
  echo '</tbody></table>';
 },'ymc_team_request','normal','high');
});
add_action('admin_menu', function () {
 add_submenu_page('edit.php?post_type=ymc_team_request','Team Request Settings','Settings','manage_options','ymc-team-requests', function () {
  if (!current_user_can('manage_options')) return;
  /* A custom screen does not get the core "Settings saved." notice on its own. */
  if (isset($_GET['settings-updated'])) add_settings_error('ymc_team_request','ymc_saved','Settings saved.','success');
  echo '<div class="wrap"><h1>Team Request Settings</h1>';
  settings_errors('ymc_team_request');
  printf('<p>Requests are currently emailed to <strong>%s</strong>%s.</p>', esc_html(ymc_team_request_recipient()),
   is_email((string) get_option('ymc_team_request_recipient','')) ? '' : ' (the built-in default, because no address is set)');
  echo '<form action="options.php" method="post">';
  settings_fields('ymc_team_request');
  do_settings_sections('ymc-team-requests');
  submit_button();
  echo '</form></div>';
 });
});
add_action('admin_init', function () {
 register_setting('ymc_team_request','ymc_team_request_recipient',['type'=>'string','sanitize_callback'=>'sanitize_email','default'=>'']);
 add_settings_section('ymc_team_request_main','','__return_false','ymc-team-requests');
 add_settings_field('ymc_team_request_recipient','Notification email', function () {
  printf('<input type="email" id="ymc_team_request_recipient" name="ymc_team_request_recipient" value="%s" class="regular-text" placeholder="%s"><p class="description">Where team placement requests are emailed. Leave empty to use %s.</p>',
   esc_attr((string) get_option('ymc_team_request_recipient','')), esc_attr(YMC_TEAM_REQUEST_FALLBACK), esc_html(YMC_TEAM_REQUEST_FALLBACK));
 },'ymc-team-requests','ymc_team_request_main');
});
/* A failed send is otherwise invisible: requests would pile up privately with nobody notified. */
add_filter('manage_ymc_team_request_posts_columns', function ($cols) {
 $cols['ymc_notification'] = 'Notification';
 $cols['ymc_confirmation'] = 'Confirmation';
 return $cols;
});
add_action('manage_ymc_team_request_posts_custom_column', function ($col,$id) {
 if ($col !== 'ymc_notification' && $col !== 'ymc_confirmation') return;
 $sent = get_post_meta($id, $col === 'ymc_notification' ? 'ymc_notification_sent' : 'ymc_confirmation_sent', true);
 if ($sent === 'yes') {
  echo 'Sent';
  if ($col === 'ymc_notification' && $to = get_post_meta($id,'ymc_notification_recipient',true)) echo '<br><span class="description">'.esc_html($to).'</span>';
  return;
 }
 echo $sent === 'no' ? '<strong style="color:#b32d2e">Failed</strong>' : '&mdash;';
},10,2);
/* Every rejection used to redirect identically, so a throttled retry looked exactly like a typo
   and the visitor was told nothing they could act on. */
function ymc_team_request_message($reason) {
 $messages = [
  'throttled' => 'You have just sent a request. Please wait a minute before sending another.',
  'invalid'   => 'Please check your name and email address, then try again.',
  'expired'   => 'That form had been open a while and the session expired. Please try again.',
 ];
 return isset($messages[$reason]) ? $messages[$reason] : 'We could not submit your request. Please try again or email '.YMC_TEAM_REQUEST_FALLBACK.'.';
}
add_shortcode('ymc_team_request', function () {
 $status = isset($_GET['team_request']) ? sanitize_key($_GET['team_request']) : '';
 ob_start(); ?>
 <section class="ymct-page" aria-labelledby="ymct-title">
 <div class="ymct-shell">
 <div class="ymct-intro">
 <p class="ymct-eyebrow">YMC AT M&amp;T &middot; ADULT TOURNAMENT</p>
 <h1 id="ymct-title">Your game.<br>Your future team.</h1>
 <p class="ymct-lead">Want to play but don&rsquo;t have a team? Let&rsquo;s help you find one.</p>
 <p>Send your details to the YMC team for help with placement.</p>
 <ol class="ymct-steps"><li><strong>Tell us about yourself</strong><span>Share your name and contact details.</span></li><li><strong>Hear from the YMC team</strong><span>We&rsquo;ll contact you about team placement.</span></li><li><strong>Register after placement</strong><span>Wait for placement instructions before registering.</span></li></ol>
 </div>
 <div class="ymct-card">
 <?php if ($status === 'received') : ?>
 <div class="ymct-success" role="status"><span class="ymct-check" aria-hidden="true">&#10003;</span><p class="ymct-eyebrow">REQUEST RECEIVED</p><h2>You&rsquo;re on our radar.</h2><p>Your request has been received. YMC will contact you about team placement.</p><p class="ymct-note">This is not a registration or a guaranteed place. Please wait for placement instructions before registering.</p><a class="ymct-button" href="<?php echo esc_url(home_url('/')); ?>">Back to the event <span aria-hidden="true">&rarr;</span></a></div>
 <?php else : ?>
 <p class="ymct-eyebrow">LET&rsquo;S GET STARTED</p><h2>Need a team?</h2><p class="ymct-card-intro">Leave your details below and we&rsquo;ll be in touch.</p>
 <?php if ($status === 'error') echo '<p class="ymct-alert" role="alert">'.esc_html(ymc_team_request_message(isset($_GET['reason']) ? sanitize_key($_GET['reason']) : '')).'</p>'; ?>
 <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
 <input type="hidden" name="action" value="ymc_team_request">
 <?php wp_nonce_field('ymc_team_request','ymc_nonce'); ?>
 <?php /* Named so browser autofill leaves it alone: a filled honeypot silently rejects a real person. */ ?>
 <p style="position:absolute;left:-10000px" aria-hidden="true"><label>Leave empty<input name="ymc_hp" tabindex="-1" autocomplete="off"></label></p>
 <p class="ymct-field"><label for="ymc-name">Full name <span>(required)</span></label><input id="ymc-name" name="full_name" required maxlength="120" autocomplete="name" placeholder="Your full name"></p>
 <p class="ymct-field"><label for="ymc-email">Email address <span>(required)</span></label><input id="ymc-email" name="email" type="email" required maxlength="254" autocomplete="email" placeholder="you@example.com"></p>
 <p class="ymct-field"><label for="ymc-phone">Phone number <span>(optional)</span></label><input id="ymc-phone" name="phone" type="tel" maxlength="40" autocomplete="tel" placeholder="Your phone number"></p>
 <p class="ymct-note" id="ymct-privacy">We&rsquo;ll use these details to contact you about team placement. Submitting this request does not register you or reserve a place.</p>
 <button class="ymct-button" type="submit" aria-describedby="ymct-privacy">Request team placement</button>
 </form>
 <?php endif; ?>
 </div></div></section>
 <?php return ob_get_clean();
});
function ymc_handle_team_request() {
 $redirect = home_url('/need-a-team/');
 $fail = function($reason = '') use ($redirect) {
  wp_safe_redirect(add_query_arg(['team_request'=>'error','reason'=>$reason],$redirect));
  exit;
 };
 if ($_SERVER['REQUEST_METHOD'] !== 'POST') $fail();
 if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ymc_nonce'] ?? '')),'ymc_team_request')) $fail('expired');
 if (!empty($_POST['ymc_hp'])) $fail();
 $name = sanitize_text_field(wp_unslash($_POST['full_name'] ?? ''));
 $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
 $phone = sanitize_text_field(wp_unslash($_POST['phone'] ?? ''));
 if (!$name || strlen($name)>120 || !is_email($email) || strlen($phone)>40) $fail('invalid');
 /* Rate limiting protects the public form; it should not block staff testing delivery. */
 $key = 'ymc_req_'.hash_hmac('sha256',$_SERVER['REMOTE_ADDR'] ?? '',wp_salt());
 $throttled = !current_user_can('manage_options');
 if ($throttled && get_transient($key)) $fail('throttled');
 $body = "Adult team placement request\n\nName: $name\nEmail: $email\nPhone: $phone\n\nPlease contact this person about placement before they register.";
 $id = wp_insert_post(['post_type'=>'ymc_team_request','post_status'=>'private','post_title'=>$name,'post_content'=>$body],true);
 if (is_wp_error($id) || !$id) $fail();
 if ($throttled) set_transient($key,1,60);
 $recipient = ymc_team_request_recipient();
 $sent = wp_mail($recipient,'YMC: Adult team placement request',$body,['Reply-To: '.$email]);
 update_post_meta($id,'ymc_notification_sent',$sent ? 'yes':'no');
 update_post_meta($id,'ymc_notification_recipient',$recipient);
 /* The sender otherwise has no proof the request went through. */
 $receipt = "Hi $name,\n\nThanks for asking about team placement for the Adult Tournament at YMC at M&T Bank Stadium.\n\nYMC will be in touch about finding you a team. Please wait for placement instructions before registering.\n\nThis message confirms your request only. It is not a registration and does not reserve a place.\n\nYMC at M&T";
 $confirmed = wp_mail($email,'YMC: We received your team placement request',$receipt,['Reply-To: '.$recipient]);
 update_post_meta($id,'ymc_confirmation_sent',$confirmed ? 'yes':'no');
 wp_safe_redirect(add_query_arg('team_request','received',$redirect)); exit;
}
add_action('admin_post_nopriv_ymc_team_request','ymc_handle_team_request');
add_action('admin_post_ymc_team_request','ymc_handle_team_request');
