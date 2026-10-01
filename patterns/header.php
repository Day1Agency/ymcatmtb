<?php
/**
 * Title: Header
 * Slug: ymcatmtb/header
 * Categories: header
 * Block Types: core/template-part/header
 * Description: The crest on the left, two buttons on the right.
 *
 * @package WordPress
 * @subpackage YMCatMT
 * @since 1.0.0
 */

$ymc_register = function_exists( 'ymcatmtb_link' ) ? ymcatmtb_link( 'teen' ) : '#';
$ymc_sponsor  = function_exists( 'ymcatmtb_link' ) ? ymcatmtb_link( 'sponsor' ) : '#';
?>
<!-- wp:group {"align":"full","className":"ymc-header","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull ymc-header">
	<!-- wp:html -->
	<div class="ymc-header__inner">
		<a class="ymc-header__home" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/event-crest.png' ) ); ?>" alt="<?php echo esc_attr__( 'YMC at M&T Bank Stadium', 'ymcatmtb' ); ?>">
		</a>

		<p class="ymc-header__actions">
			<a class="ymc-button ymc-button--ghost" href="<?php echo esc_url( $ymc_sponsor ); ?>" target="_blank" rel="noreferrer noopener"><?php echo esc_html__( 'Sponsor', 'ymcatmtb' ); ?></a>
			<a class="ymc-button" href="<?php echo esc_url( $ymc_register ); ?>" target="_blank" rel="noreferrer noopener"><?php echo esc_html__( 'Register', 'ymcatmtb' ); ?></a>
		</p>
	</div>
	<!-- /wp:html -->
</div>
<!-- /wp:group -->
