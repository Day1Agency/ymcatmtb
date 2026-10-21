<?php
/**
 * Plugin Name: YMC Team Placement Requests
 * Description: Private adult team placement requests and email notifications.
 * Version: 1.1.0
 */
if (!defined('ABSPATH')) exit;
const YMC_TEAM_REQUEST_FALLBACK = 'ykohen@yeshivasmekorchaim.org';
/** Where notifications go. Settings value wins; the fallback keeps delivery working if it is blank. */
function ymc_team_request_recipient() {
 $email = sanitize_email((string) get_option('ymc_team_request_recipient',''));
 return is_email($email) ? $email : YMC_TEAM_REQUEST_FALLBACK;
}
add_action('init', function () {
 register_post_type('ymc_team_request', ['label'=>'Team Requests','public'=>false,'show_ui'=>true,'supports'=>['title','editor'],'capabilities'=>['create_posts'=>'do_not_allow'],'map_meta_cap'=>true]);
});
add_action('admin_menu', function () {
 add_submenu_page('edit.php?post_type=ymc_team_request','Team Request Settings','Settings','manage_options','ymc-team-requests', function () {
  if (!current_user_can('manage_options')) return;
  echo '<div class="wrap"><h1>Team Request Settings</h1><form action="options.php" method="post">';
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
 $meta = $col === 'ymc_notification' ? 'ymc_notification_sent' : 'ymc_confirmation_sent';
 $sent = get_post_meta($id,$meta,true);
 if ($sent === 'yes') { echo 'Sent'; if ($col === 'ymc_notification' && $to = get_post_meta($id,'ymc_notification_recipient',true)) echo '<br><span class="description">'.esc_html($to).'</span>'; return; }
 echo $sent === 'no' ? '<strong style="color:#b32d2e">Failed</strong>' : '&mdash;';
},10,2);
add_shortcode('ymc_team_request', function () {
 $status = isset($_GET['team_request']) ? sanitize_key($_GET['team_request']) : '';
 if ($status === 'received') return '<p role="status">Your request has been received. YMC will contact you about team placement. This is not a registration or a guaranteed place.</p>';
 ob_start(); ?>
 <section style="max-width:640px;margin:40px auto;padding:24px">
 <h1>Need a team?</h1><p>Interested in the Adult Tournament but don’t have a team? Send your details to Yossi and the YMC team for help with placement. Please wait for placement instructions before registering.</p>
 <?php if ($status === 'error') echo '<p role="alert">We couldn’t submit your request. Please try again or email '.esc_html(YMC_TEAM_REQUEST_FALLBACK).'.</p>'; ?>
 <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
 <input type="hidden" name="action" value="ymc_team_request">
 <?php wp_nonce_field('ymc_team_request','ymc_nonce'); ?>
 <p style="position:absolute;left:-10000px" aria-hidden="true"><label>Leave empty<input name="website" tabindex="-1" autocomplete="off"></label></p>
 <p><label for="ymc-name">Full name *</label><br><input id="ymc-name" name="full_name" required maxlength="120" autocomplete="name" style="width:100%;padding:12px"></p>
 <p><label for="ymc-email">Email *</label><br><input id="ymc-email" name="email" type="email" required maxlength="254" autocomplete="email" style="width:100%;padding:12px"></p>
 <p><label for="ymc-phone">Phone (optional)</label><br><input id="ymc-phone" name="phone" type="tel" maxlength="40" autocomplete="tel" style="width:100%;padding:12px"></p>
 <p>We’ll use these details to contact you about team placement. Submitting this request does not register you or reserve a place.</p>
 <button type="submit" style="padding:14px 24px;background:#241046;color:#fff;border:0;border-radius:4px">Request help finding a team</button>
 </form></section>
 <?php return ob_get_clean();
});
function ymc_handle_team_request() {
 $redirect = home_url('/need-a-team/');
 $fail = function() use ($redirect) { wp_safe_redirect(add_query_arg('team_request','error',$redirect)); exit; };
 if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ymc_nonce'] ?? '')),'ymc_team_request')) $fail();
 if (!empty($_POST['website'])) $fail();
 $key = 'ymc_req_'.hash_hmac('sha256',$_SERVER['REMOTE_ADDR'] ?? '',wp_salt());
 if (get_transient($key)) $fail();
 $name = sanitize_text_field(wp_unslash($_POST['full_name'] ?? ''));
 $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
 $phone = sanitize_text_field(wp_unslash($_POST['phone'] ?? ''));
 if (!$name || strlen($name)>120 || !is_email($email) || strlen($phone)>40) $fail();
 $body = "Adult team placement request\n\nName: $name\nEmail: $email\nPhone: $phone\n\nPlease contact this person about placement before they register.";
 $id = wp_insert_post(['post_type'=>'ymc_team_request','post_status'=>'private','post_title'=>$name,'post_content'=>$body],true);
 if (is_wp_error($id) || !$id) $fail();
 set_transient($key,1,60);
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
