<?php
/**
 * Plugin Name: YMC Homepage Polish
 * Description: Reversible October 28 homepage presentation changes. No registration or payment changes.
 * Version: 1.1.5
 */
namespace YMC\HomepagePolish;
defined('ABSPATH') || exit;
add_filter('the_content', function ($content) {
    if (!is_front_page() || strpos($content, 'ymc-stadium-scroll') === false) return $content;
    $content = str_ireplace('Become a corporate sponsor', 'Sponsor the event', $content);
    foreach (array('adult','teen') as $division) {
        $content = str_replace('https://givebutter.com/ymc-'.$division.'-player-registration/register', 'https://givebutter.com/ymc-'.$division.'-player-registration', $content);
    }
    $content = preg_replace('~<i[^>]*class="fas fa-envelope-open-text"[^>]*></i>\s*Joining a team\? Ask your captain and choose their team as you register\.\s*~', '', $content);
    $wordmark = '<img class="ymc-menu-artwork" src="' . esc_url(plugins_url('menu-transparent.png', __FILE__)) . '" alt="YMC at M&amp;T Bank Stadium" width="269" height="225">';
    $content = preg_replace_callback('~<img[^>]*class="ymc-ref-header-logo"[^>]*>~s', function () use ($wordmark) { return $wordmark; }, $content, 1);
    $content = str_replace('PLAY<br><span>FOR YMC</span>', 'PLAY <span>FOR YMC</span>', $content);
    $content = preg_replace('~<p class="ymc-stadium-reveal-deck">.*?</p>~s', '', $content, 1);
    $cards = '<div class="ymc-stadium-reveal-cards"><a href="#tournaments" class="ymc-stadium-reveal-card"><h3>Register<br> to play</h3></a><a href="#tickets" class="ymc-stadium-reveal-card"><h3>Come<br> watch</h3></a><a href="#sponsor" class="ymc-stadium-reveal-card"><h3>Sponsor<br> the event</h3></a></div>';
    $content = preg_replace('~<div class="ymc-stadium-reveal-cards">.*?</div>~s', $cards, $content, 1);
    $content = preg_replace('~<nav class="ymc-stadium-dock[^\"]*"[^>]*>.*?</nav>~s', '', $content, 1);
    $campaign = '<section id="campaign" class="ymc-clean-campaign" aria-label="YMC fundraising"><div class="ymc-clean-campaign-inner"><p class="ymc-campaign-brand">YMC AT M&amp;T</p><h2 id="ymc-raised">—</h2><p class="ymc-goal-caption">raised toward our $750,000 goal</p><progress id="ymc-goal-progress" max="750000" value="0" aria-label="Fundraising progress"></progress><p id="ymc-goal-percent"></p><a href="https://givebutter.com/ymc-annual-campaign/donate" class="ymc-clean-donate">Donate now</a><div class="ymc-donor-panel"><div class="ymc-feed-controls"><button type="button" id="ymc-feed-pause" aria-pressed="false">Pause scrolling</button></div><div id="ymc-donor-feed" tabindex="0" role="region" aria-label="Recent public donations. Scroll to read more."></div><a class="ymc-feed-link" href="https://givebutter.com/ymc-annual-campaign" target="_blank" rel="noopener">View all donations <span aria-hidden="true">→</span></a></div></div></section>';
    $content = preg_replace_callback('~<section id="campaign"[^>]*>.*?</section>~s', function () use ($campaign) { return $campaign; }, $content, 1);
    $content = preg_replace('~(<h2[^>]*data-dc-tpl="73"[^>]*>).*?</h2>~s', '$1MORE THAN<br><span style="color:#d4af37">A GAME</span></h2>', $content, 1);
    $content = preg_replace('~(<p[^>]*data-dc-tpl="76"[^>]*>).*?</p>~s', '$1Every player, fan and sponsor helps support the learning and growth of YMC’s talmidim. Be part of their next chapter.</p>', $content, 1);
    $content = preg_replace('~<div[^>]*>\s*<p[^>]*><i[^>]*class="fas fa-file-signature".*?</p>\s*<a[^>]*>Get the Forms</a>\s*</div>~s', '', $content, 1);
    $content = preg_replace('~<a[^>]*href="/player-forms/"[^>]*>Player Forms</a>~s', '', $content);
    return $content;
}, 99);
add_action('wp_enqueue_scripts', function () {
    if (is_front_page()) wp_enqueue_style('ymc-homepage-polish', plugins_url('polish.css', __FILE__), array(), '1.1.5');
}, 99);

// Only public giving-space values are exposed; never fall back to billing names or amounts.
function donor_rows($rows) {
 $out=array();
 foreach($rows as $r){
  if((int)($r['campaign_id']??0)!==733202 || ($r['status']??'')!=='succeeded')continue;
  $g=$r['giving_space']??null;
  if(!is_array($g)||empty($g['id'])||empty($g['created_at']))continue;
  $out[(string)$g['id']]=array('name'=>sanitize_text_field($g['name']??'Anonymous')?:'Anonymous','amount'=>is_numeric($g['amount']??null)&&$g['amount']>0?(float)$g['amount']:null,'message'=>sanitize_textarea_field($g['message']??''),'date'=>$g['created_at']);
 }
 $out=array_values($out);usort($out,function($a,$b){return strcmp($b['date'],$a['date']);});return array_slice($out,0,12);
}
function donors(){
 $cached=get_transient('ymc_public_donors_v1');if($cached!==false)return $cached;
 $c=(array)get_option('ymc_captain_invitations',array());if(empty($c['api_key']))return new \WP_Error('unavailable','Feed unavailable',array('status'=>503));
 $r=wp_remote_get('https://api.givebutter.com/v1/transactions?sortByDesc=created_at&per_page=100',array('timeout'=>15,'redirection'=>0,'headers'=>array('Authorization'=>'Bearer '.$c['api_key'],'Accept'=>'application/json')));
 if(is_wp_error($r)||wp_remote_retrieve_response_code($r)!==200)return new \WP_Error('unavailable','Feed unavailable',array('status'=>503));
 $d=json_decode(wp_remote_retrieve_body($r),true);if(!isset($d['data'])||!is_array($d['data']))return new \WP_Error('unavailable','Feed unavailable',array('status'=>503));
 $out=donor_rows($d['data']);set_transient('ymc_public_donors_v1',$out,120);return $out;
}
add_action('rest_api_init',function(){register_rest_route('ymc/v1','/public-donors',array('methods'=>'GET','permission_callback'=>'__return_true','callback'=>__NAMESPACE__.'\\donors'));});
add_action('wp_enqueue_scripts',function(){if(!is_front_page())return;wp_enqueue_script('ymc-public-donors',plugins_url('donors.js',__FILE__),array(),'1.1.5',true);wp_add_inline_script('ymc-public-donors','window.ymcDonorEndpoint='.wp_json_encode(rest_url('ymc/v1/public-donors')).';','before');});
