<?php
/**
 * Title: Hero
 * Slug: ymcatmtb/hero
 * Categories: banner, featured
 * Description: Stadium photograph, the event name, the date and the two main calls to action.
 *
 * @package WordPress
 * @subpackage YMCatMT
 * @since 1.0.0
 */

$ymc_register = function_exists( 'ymcatmtb_link' ) ? ymcatmtb_link( 'teen' ) : '#';
$ymc_sponsor  = function_exists( 'ymcatmtb_link' ) ? ymcatmtb_link( 'sponsor' ) : '#';
?>
<!-- wp:group {"align":"full","className":"ymc-hero","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull ymc-hero">
	<!-- wp:html -->
	<div class="ymc-hero__media" aria-hidden="true">
		<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/stadium.jpg' ) ); ?>" alt="">
	</div>

	<div class="ymc-hero__inner">
		<p class="ymc-hero__flag"><?php echo esc_html__( 'Registration open', 'ymcatmtb' ); ?></p>
		<p class="ymc-hero__eyebrow"><?php echo esc_html__( 'YMC at M&T Bank Stadium', 'ymcatmtb' ); ?></p>
		<h1 class="ymc-hero__title"><?php echo esc_html__( 'Register', 'ymcatmtb' ); ?><span><?php echo esc_html__( 'to play', 'ymcatmtb' ); ?></span></h1>
		<p class="ymc-hero__when"><?php echo esc_html__( 'March 28, 2027 · Baltimore, Maryland', 'ymcatmtb' ); ?></p>
		<p class="ymc-hero__actions">
			<a class="ymc-button" href="<?php echo esc_url( $ymc_register ); ?>"><?php echo esc_html__( 'Register', 'ymcatmtb' ); ?></a>
			<a class="ymc-button ymc-button--ghost" href="<?php echo esc_url( $ymc_sponsor ); ?>"><?php echo esc_html__( 'Sponsor', 'ymcatmtb' ); ?></a>
		</p>
		<img class="ymc-hero__crest" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/event-crest.png' ) ); ?>" alt="<?php echo esc_attr__( 'YMC at M&T Bank Stadium', 'ymcatmtb' ); ?>">
	</div>
	<!-- /wp:html -->
</div>
<!-- /wp:group -->
