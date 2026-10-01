<?php
/**
 * Title: Sponsor the game
 * Slug: ymcatmtb/sponsor
 * Categories: call-to-action
 * Description: For people who are not playing: sponsor the event, a player or a team.
 *
 * @package WordPress
 * @subpackage YMCatMT
 * @since 1.0.0
 */

$ymc_sponsor = function_exists( 'ymcatmtb_link' ) ? ymcatmtb_link( 'sponsor' ) : '#';
$ymc_teen    = function_exists( 'ymcatmtb_link' ) ? ymcatmtb_link( 'teen' ) : '#';
$ymc_adult   = function_exists( 'ymcatmtb_link' ) ? ymcatmtb_link( 'team-start' ) : '#';
?>
<!-- wp:group {"align":"full","className":"ymc-sponsor","layout":{"type":"constrained","contentSize":"1140px"}} -->
<div class="wp-block-group alignfull ymc-sponsor">
	<!-- wp:html -->
	<div class="ymc-sponsor__inner">
		<p class="ymc-eyebrow"><?php echo esc_html__( 'Not playing?', 'ymcatmtb' ); ?></p>
		<h2 class="ymc-sponsor__title"><?php echo esc_html__( 'Sponsor the game', 'ymcatmtb' ); ?></h2>
		<p class="ymc-sponsor__text"><?php echo esc_html__( 'Support a player, a team, or the event itself. Every gift goes toward the YMC $750,000 Annual Campaign.', 'ymcatmtb' ); ?></p>

		<p class="ymc-sponsor__actions">
			<a class="ymc-button" href="<?php echo esc_url( $ymc_sponsor ); ?>" target="_blank" rel="noreferrer noopener"><?php echo esc_html__( 'Become a sponsor', 'ymcatmtb' ); ?></a>
			<a class="ymc-button ymc-button--ghost" href="<?php echo esc_url( $ymc_teen ); ?>" target="_blank" rel="noreferrer noopener"><?php echo esc_html__( 'Support a player', 'ymcatmtb' ); ?></a>
			<a class="ymc-button ymc-button--ghost" href="<?php echo esc_url( $ymc_adult ); ?>" target="_blank" rel="noreferrer noopener"><?php echo esc_html__( 'Support a team', 'ymcatmtb' ); ?></a>
		</p>
	</div>
	<!-- /wp:html -->
</div>
<!-- /wp:group -->
