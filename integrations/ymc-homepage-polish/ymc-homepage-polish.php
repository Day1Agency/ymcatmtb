<?php
/**
 * Plugin Name: YMC Homepage Polish
 * Description: Reversible October 28 homepage presentation changes. No registration or payment changes.
 * Version: 1.0.1
 */
namespace YMC\HomepagePolish;
defined('ABSPATH') || exit;
add_filter('the_content', function ($content) {
    if (!is_front_page() || strpos($content, 'ymc-stadium-scroll') === false) return $content;
    $content = str_replace('PLAY<br><span>FOR YMC</span>', 'PLAY <span>FOR YMC</span>', $content);
    $content = preg_replace('~<p class="ymc-stadium-reveal-deck">.*?</p>~s', '', $content, 1);
    $cards = '<div class="ymc-stadium-reveal-cards"><a href="#tournaments" class="ymc-stadium-reveal-card"><h3>Register<br> to play</h3></a><a href="#tickets" class="ymc-stadium-reveal-card"><h3>Come<br> watch</h3></a><a href="#sponsor" class="ymc-stadium-reveal-card"><h3>Become a<br> corporate sponsor</h3></a></div>';
    $content = preg_replace('~<div class="ymc-stadium-reveal-cards">.*?</div>~s', $cards, $content, 1);
    $content = preg_replace('~<nav class="ymc-stadium-dock[^\"]*"[^>]*>.*?</nav>~s', '', $content, 1);
    $campaign = '<section id="campaign" class="ymc-clean-campaign" aria-label="YMC annual fundraising campaign"><div class="ymc-clean-campaign-inner"><h2>$750,000<span class="ymc-sr-only"> fundraising goal</span></h2><div data-dc-tpl="59"><p class="ymc-sr-only">Fundraising progress is loading.</p></div><a href="https://givebutter.com/ymc-annual-campaign/donate" class="ymc-clean-donate">Donate now</a></div></section>';
    $content = preg_replace_callback('~<section id="campaign"[^>]*>.*?</section>~s', function () use ($campaign) { return $campaign; }, $content, 1);
    return $content;
}, 99);
add_action('wp_enqueue_scripts', function () {
    if (is_front_page()) wp_enqueue_style('ymc-homepage-polish', plugins_url('polish.css', __FILE__), array(), '1.0.1');
}, 99);
