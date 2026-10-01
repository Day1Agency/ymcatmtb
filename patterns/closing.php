<?php
/**
 * Title: Closing call to action
 * Slug: ymcatmtb/closing
 * Categories: call-to-action, featured
 * Description: The last word before the footer: the date, the place, and the two buttons again.
 *
 * @package WordPress
 * @subpackage YMCatMT
 * @since 1.0.0
 */

$ymc_register = function_exists( 'ymcatmtb_link' ) ? ymcatmtb_link( 'teen' ) : '#';
$ymc_sponsor  = function_exists( 'ymcatmtb_link' ) ? ymcatmtb_link( 'sponsor' ) : '#';
?>
<!-- wp:group {"align":"full","className":"ymc-closing","layout":{"type":"constrained","contentSize":"1140px"}} -->
<div class="wp-block-group alignfull ymc-closing">
	<!-- wp:html -->
	<div class="ymc-closing__inner">
		<p class="ymc-closing__when"><?php echo esc_html__( 'March 28, 2027 · M&T Bank Stadium', 'ymcatmtb' ); ?></p>
		<h2 class="ymc-closing__title"><?php echo esc_html__( 'See you on the field', 'ymcatmtb' ); ?></h2>
		<p class="ymc-closing__actions">
			<a class="ymc-button" href="<?php echo esc_url( $ymc_register ); ?>" target="_blank" rel="noreferrer noopener"><?php echo esc_html__( 'Register', 'ymcatmtb' ); ?></a>
			<a class="ymc-button ymc-button--ghost" href="<?php echo esc_url( $ymc_sponsor ); ?>" target="_blank" rel="noreferrer noopener"><?php echo esc_html__( 'Sponsor', 'ymcatmtb' ); ?></a>
		</p>
	</div>
	<!-- /wp:html -->
</div>
<!-- /wp:group -->
