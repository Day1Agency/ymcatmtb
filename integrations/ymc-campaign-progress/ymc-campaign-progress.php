<?php
/**
 * Plugin Name: YMC Combined Campaign Progress
 * Description: Read-only combined fundraising display for the four YMC campaigns. Reuses the existing server-side Givebutter connection.
 * Version: 1.0.2
 */
namespace YMC\Progress;
defined('ABSPATH') || exit;
const CACHE = 'ymc_combined_progress_v1';
const HOOK = 'ymc_combined_progress_refresh';
function campaigns() {
    return array(733202 => 'Donations and sponsorships', 729740 => 'Adult tournament', 749484 => 'Teen tournament', 725223 => 'General admission and VIP');
}
function collect($fetch) {
    $rows = array(); $cents = 0;
    foreach (campaigns() as $id => $label) {
        $row = $fetch($id);
        if ((string)($row['id'] ?? '') !== (string)$id || (string)($row['account_id'] ?? '') !== 'Smvzsgm4Xm6LD2XB' || strtoupper((string)($row['currency'] ?? '')) !== 'USD' || !isset($row['raised']) || !is_numeric($row['raised']) || !is_finite((float)$row['raised']) || (float)$row['raised'] < 0) {
            throw new \RuntimeException('Campaign '.$id.' response: id='.(string)($row['id'] ?? 'missing').', account='.(string)($row['account_id'] ?? 'missing').', currency='.(string)($row['currency'] ?? 'missing').', raised='.(is_numeric($row['raised'] ?? null) ? (string)$row['raised'] : 'missing').'. Last complete total retained.');
        }
        $amount = (int)round((float)$row['raised'] * 100);
        $cents += $amount;
        $rows[] = array('id' => $id, 'label' => $label, 'raised' => $amount / 100);
    }
    return array('raised' => $cents / 100, 'goal' => 750000, 'currency' => 'USD', 'campaigns' => $rows, 'updated_at' => gmdate('c'));
}
function refresh() {
    if (get_transient('ymc_progress_refresh_lock')) { return false; }
    set_transient('ymc_progress_refresh_lock', 1, 120);
    try {
        $config = (array)get_option('ymc_captain_invitations', array());
        $key = $config['api_key'] ?? '';
        if (!$key) { throw new \RuntimeException('Configure the existing Givebutter connection in Captain Invitations first.'); }
        $data = collect(function($id) use ($key) {
            $response = wp_remote_get('https://api.givebutter.com/v1/campaigns/' . $id, array('timeout'=>15, 'redirection'=>0, 'headers'=>array('Authorization'=>'Bearer '.$key, 'Accept'=>'application/json')));
            if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) { throw new \RuntimeException('Givebutter could not return all four campaigns. Last complete total retained.'); }
            $row = json_decode(wp_remote_retrieve_body($response), true);
            if (!is_array($row)) { throw new \RuntimeException('Invalid campaign response.'); }
            return isset($row['data']) && is_array($row['data']) ? $row['data'] : $row;
        });
        update_option(CACHE, $data, false);
        delete_option('ymc_progress_error');
        return true;
    } catch (\Throwable $e) {
        update_option('ymc_progress_error', $e->getMessage(), false);
        return false;
    } finally { delete_transient('ymc_progress_refresh_lock'); }
}
add_filter('cron_schedules', function($s) { $s['ymc_progress_five_minutes'] = array('interval'=>300,'display'=>'Every five minutes'); return $s; });
add_action(HOOK, __NAMESPACE__.'\\refresh');
register_activation_hook(__FILE__, function() { if (!wp_next_scheduled(HOOK)) { wp_schedule_event(time()+10,'ymc_progress_five_minutes',HOOK); } });
register_deactivation_hook(__FILE__, function() { wp_clear_scheduled_hook(HOOK); });
add_action('rest_api_init', function() {
    register_rest_route('ymc/v1','/campaign-progress',array('methods'=>'GET','permission_callback'=>'__return_true','callback'=>function() {
        $data = get_option(CACHE);
        if (!$data) { return new \WP_Error('not_ready','Campaign progress is temporarily unavailable.',array('status'=>503)); }
        $response = new \WP_REST_Response($data);
        $response->header('Cache-Control','public, max-age=60');
        return $response;
    }));
});
add_action('admin_menu',function() { add_options_page('Combined Campaign Progress','Campaign Progress','manage_options','ymc-campaign-progress',__NAMESPACE__.'\\admin'); });
function admin() {
    if (!current_user_can('manage_options')) { return; }
    if ($_SERVER['REQUEST_METHOD']==='POST') {
        check_admin_referer('ymc_progress_settings');
        if (isset($_POST['refresh'])) { refresh(); }
        if (isset($_POST['save_display'])) { update_option('ymc_progress_enabled',isset($_POST['enabled']),false); }
    }
    echo '<div class="wrap"><h1>Combined Campaign Progress</h1><p>Read-only total of four campaigns. No transactions are moved or duplicated. Existing registration links and goals are unchanged.</p>';
    $error = get_option('ymc_progress_error');
    if ($error) { echo '<div class="notice notice-warning"><p>'.esc_html($error).'</p></div>'; }
    $data = get_option(CACHE);
    if ($data) {
        echo '<h2>Combined total: $'.esc_html(number_format($data['raised'],2)).' / $750,000</h2><p>Updated '.esc_html($data['updated_at']).'</p><table class="widefat"><thead><tr><th>Campaign</th><th>ID</th><th>Raised</th></tr></thead><tbody>';
        foreach ($data['campaigns'] as $row) { echo '<tr><td>'.esc_html($row['label']).'</td><td>'.esc_html($row['id']).'</td><td>$'.esc_html(number_format($row['raised'],2)).'</td></tr>'; }
        echo '</tbody></table>';
    } else { echo '<p>No verified total yet. Refresh before enabling the homepage display.</p>'; }
    echo '<form method="post">'; wp_nonce_field('ymc_progress_settings');
    echo '<p><button class="button" name="refresh" value="1">Refresh totals now</button></p><p><label><input type="checkbox" name="enabled" value="1" '.checked(get_option('ymc_progress_enabled'),true,false).'> Show combined total in the homepage Annual Campaign section</label></p><button class="button button-primary" name="save_display" value="1">Save display setting</button></form><p>Rollback: uncheck the display setting or deactivate this plugin. The original campaign-goal display remains in the page.</p></div>';
}
add_action('wp_enqueue_scripts', function() {
    if (!is_front_page() || !get_option('ymc_progress_enabled')) { return; }
    wp_enqueue_script('ymc-campaign-progress',plugins_url('progress.js',__FILE__),array(),'1.0.0',true);
    wp_add_inline_script('ymc-campaign-progress','window.ymcProgressEndpoint='.wp_json_encode(rest_url('ymc/v1/campaign-progress')).';','before');
    wp_enqueue_style('ymc-campaign-progress',plugins_url('progress.css',__FILE__),array(),'1.0.0');
});
