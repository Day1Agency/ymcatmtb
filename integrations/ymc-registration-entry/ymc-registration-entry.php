<?php
/**
 * Plugin Name: YMC Registration Entry
 * Description: Artwork-first registration and captain color instructions. Givebutter handles payment and final availability.
 * Version: 1.0.2
 */
namespace YMC\RegistrationEntry;
defined('ABSPATH') || exit;
const ADULT = 'https://givebutter.com/ymc-adult-player-registration/register';
const TEEN = 'https://givebutter.com/ymc-teen-player-registration/register';
add_filter('ymcatmtb_links',function($links){$links['team-start']=home_url('/adult-registration/');$links['team-join']=home_url('/adult-registration/?role=player');$links['teen']=home_url('/teen-registration/');return $links;});
add_filter('the_content',function($html){
 if(!is_front_page())return $html;
 $html=preg_replace_callback('~<a\b[^>]*href="https://givebutter.com/ymc-adult-player-registration/register"[^>]*>.*?</a>~s',function($m){$join=stripos($m[0],'join')!==false;$out=str_replace(ADULT,esc_url(home_url('/adult-registration/'.($join?'?role=player':''))),$m[0]);return str_ireplace('Start a Team','Register as captain',$out);},$html);
 return str_replace(TEEN,esc_url(home_url('/teen-registration/')),$html);
},110);
add_action('template_redirect',function(){
 $path=trim((string)wp_parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH),'/');
 if(!in_array($path,array('adult-registration','teen-registration'),true))return;
 $teen=$path==='teen-registration';status_header(200);nocache_headers();
 $role=isset($_GET['role'])&&$_GET['role']==='player'?'player':(isset($_GET['role'])&&$_GET['role']==='captain'?'captain':'');
 $colors=array('Blue'=>'#357bea','Red'=>'#d34040','Orange'=>'#ed8b32','Green'=>'#339463','Purple'=>'#9365ce','Yellow'=>'#f2cc4d','White'=>'#f5f3ed','Gray'=>'#a8a8af','Maroon'=>'#8b354e','Teal'=>'#369b9c','Brown'=>'#94683f');
 $color=isset($_GET['color'])&&is_string($_GET['color'])&&isset($colors[$_GET['color']])?$_GET['color']:'';
 $cover='https://ymcatmtb.com/wp-content/uploads/2026/10/ymc-approved-logo-transparent.png';
 ?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?php echo $teen?'Teen':'Adult';?> registration | YMC at M&amp;T</title><link rel="stylesheet" href="<?php echo esc_url(plugins_url('entry.css',__FILE__).'?ver=1.0.2');?>"></head><body><header><a href="/" class="brand"><img src="<?php echo esc_url(plugins_url('wordmark.png',__FILE__));?>" alt="YMC at M&amp;T Bank Stadium"></a><a href="/#tournaments">Back to event</a></header><main><img class="artwork" src="<?php echo esc_url($cover);?>" alt="YMC at M&amp;T — <?php echo $teen?'Teen':'Adult';?> Tournament"><section class="entry"><p class="eyebrow">SUNDAY, MARCH 28, 2027</p>
 <?php if($teen):?><h1>Your next game starts here.</h1><p class="intro">Grades 8–12. $100 registration counts toward your $850 fundraising commitment.</p><p>Register individually; organizers will place you in the appropriate division. Your waiver and media-release agreements are included in registration.</p><a class="primary" href="<?php echo esc_url(TEEN);?>">Continue to teen registration</a>
 <?php elseif(!$role):?><h1>Take the field with your team.</h1><p class="intro">$180 per player. One $15,000 fundraising commitment per team.</p><div class="choices"><a href="?role=captain"><span class="eyebrow">LEAD YOUR TEAM</span><h2>Register as captain</h2><p>Choose your team color and register. Your team is already set up—no name or logo needed.</p><strong>Choose a color <span aria-hidden="true">→</span></strong></a><a href="?role=player"><span class="eyebrow">PLAY TOGETHER</span><h2>Join a team</h2><p>Already have a captain? Join their team and register as a player.</p><strong>Join your captain <span aria-hidden="true">→</span></strong></a></div><p class="help">Need a team? <a href="/need-a-team/">Ask us to help you find one.</a></p>
 <?php elseif($role==='captain'&&!$color):?><a class="back" href="/adult-registration/">← Back</a><h1>Choose your team color.</h1><p class="intro">Each color has one captain ticket. Your $180 counts toward your team’s $15,000 goal.</p><div class="colors"><?php foreach($colors as $name=>$hex):?><a href="<?php echo esc_url(add_query_arg(array('role'=>'captain','color'=>$name),home_url('/adult-registration/')));?>"><i style="background:<?php echo esc_attr($hex);?>"></i><?php echo esc_html($name);?></a><?php endforeach;?><span class="taken"><i style="background:#111"></i>Black · claimed</span></div><p class="help">Final availability is confirmed in Givebutter. A color is reserved only when its captain ticket purchase is complete.</p>
 <?php elseif($role==='captain'):?><a class="back" href="?role=captain">← Change color</a><h1>Lead the <?php echo esc_html($color);?> team.</h1><ol class="steps"><li>Sign in to Givebutter or create your account.</li><li>Choose <strong>Join a team</strong>, then select <strong><?php echo esc_html($color);?></strong>. We’ve already created it for you.</li><li>Choose <strong>Adult Tournament — <?php echo esc_html($color);?> (Captain Only)</strong> and complete your $180 registration.</li></ol><p class="note">Use the same color for your team and ticket. If that captain ticket is sold out, return here and choose another color.</p><a class="primary" href="<?php echo esc_url(ADULT);?>">Continue as <?php echo esc_html($color);?> captain</a><p class="help">Givebutter opens next. Your team and ticket are not automatically selected.</p>
 <?php else:?><a class="back" href="/adult-registration/">← Back</a><h1>Join your captain’s team.</h1><ol class="steps"><li>Have your captain’s team name or color ready.</li><li>In Givebutter, choose <strong>Join a team</strong> and select your captain’s team.</li><li>Choose <strong>Adult Tournament — Player</strong> and complete your $180 registration.</li></ol><p class="note">Black team players: select <strong>Chaim Finkelstein</strong>. Players should use the Player ticket, not a Captain Only ticket.</p><a class="primary" href="<?php echo esc_url(ADULT);?>">Continue to player registration</a><p class="help">Your team is selected in Givebutter. If you don’t have a captain yet, <a href="/need-a-team/">request help finding a team</a>.</p><?php endif;?>
 </section></main><footer>Yeshivas Mekor Chaim · YMC at M&amp;T Bank Stadium</footer></body></html><?php exit;
},1);
