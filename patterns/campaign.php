<?php
/**
 * Title: Annual campaign band
 * Slug: ymcatmtb/campaign
 * Categories: call-to-action
 * Description: Purple band tying the tournament to the school's $750,000 annual campaign.
 *
 * @package WordPress
 * @subpackage YMCatMT
 * @since 1.0.0
 */

$ymc_donate = function_exists( 'ymcatmtb_link' ) ? ymcatmtb_link( 'donate' ) : '#';
?>
<!-- wp:group {"align":"full","className":"ymc-band","layout":{"type":"constrained","contentSize":"1140px"}} -->
<div class="wp-block-group alignfull ymc-band">
	<!-- wp:html -->
	<div class="ymc-band__inner">
		<div class="ymc-band__copy">
			<p class="ymc-band__label"><?php echo esc_html__( 'YMC Annual Campaign', 'ymcatmtb' ); ?></p>
			<h2 class="ymc-band__title"><?php echo esc_html__( 'Begin the year with tzedakah', 'ymcatmtb' ); ?></h2>
			<p class="ymc-band__text"><?php echo esc_html__( 'Every registration and every gift goes toward our $750,000 Annual Campaign goal.', 'ymcatmtb' ); ?></p>
		</div>

		<div class="ymc-band__goal">
			<p class="ymc-band__goallabel"><?php echo esc_html__( 'Campaign goal', 'ymcatmtb' ); ?></p>
			<p class="ymc-band__amount"><?php echo esc_html__( '$750,000', 'ymcatmtb' ); ?></p>
			<p class="ymc-band__actions">
				<a class="ymc-button" href="<?php echo esc_url( $ymc_donate ); ?>" target="_blank" rel="noreferrer noopener"><?php echo esc_html__( 'Give now', 'ymcatmtb' ); ?></a>
				<a class="ymc-button ymc-button--quiet" href="https://ymc.school/"><?php echo esc_html__( 'Learn more', 'ymcatmtb' ); ?></a>
			</p>
		</div>
	</div>
	<!-- /wp:html -->
</div>
<!-- /wp:group -->
