<?php
/**
 * Title: Choose your tournament
 * Slug: ymcatmtb/tournaments
 * Categories: featured, call-to-action
 * Description: The teen and adult tournaments side by side, with fees, commitments and the buttons into Givebutter.
 *
 * @package WordPress
 * @subpackage YMCatMT
 * @since 1.0.0
 */

$ymc_teen  = function_exists( 'ymcatmtb_link' ) ? ymcatmtb_link( 'teen' ) : '#';
$ymc_start = function_exists( 'ymcatmtb_link' ) ? ymcatmtb_link( 'team-start' ) : '#';
$ymc_join  = function_exists( 'ymcatmtb_link' ) ? ymcatmtb_link( 'team-join' ) : '#';
?>
<!-- wp:group {"align":"full","className":"ymc-tournaments","layout":{"type":"constrained","contentSize":"1140px"}} -->
<div class="wp-block-group alignfull ymc-tournaments">
	<!-- wp:paragraph {"className":"ymc-eyebrow"} -->
	<p class="ymc-eyebrow"><?php echo esc_html__( 'Two tournaments · one stadium', 'ymcatmtb' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":2,"className":"ymc-section__title"} -->
	<h2 class="wp-block-heading ymc-section__title"><?php echo esc_html__( 'Choose your tournament', 'ymcatmtb' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:html -->
	<div class="ymc-cards">

		<div class="ymc-card">
			<p class="ymc-card__number">01</p>
			<p class="ymc-card__meta"><?php echo esc_html__( 'Grades 8 to 12 · register individually', 'ymcatmtb' ); ?></p>
			<h3 class="ymc-card__name"><?php echo esc_html__( 'Teen Tournament', 'ymcatmtb' ); ?></h3>
			<p class="ymc-card__text"><?php echo esc_html__( 'Eighth graders and up welcome. Sign up on your own, and organizers will place you in the appropriate division.', 'ymcatmtb' ); ?></p>

			<div class="ymc-card__figures">
				<p class="ymc-card__figure"><strong><?php echo esc_html__( '$100', 'ymcatmtb' ); ?></strong><?php echo esc_html__( 'to register today', 'ymcatmtb' ); ?></p>
				<p class="ymc-card__figure"><strong><?php echo esc_html__( '$850', 'ymcatmtb' ); ?></strong><?php echo esc_html__( 'total fundraising commitment', 'ymcatmtb' ); ?></p>
			</div>

			<p class="ymc-card__note"><?php echo esc_html__( 'Your $100 registration counts toward your goal, leaving $750 to raise.', 'ymcatmtb' ); ?></p>

			<p class="ymc-card__actions">
				<a class="ymc-button" href="<?php echo esc_url( $ymc_teen ); ?>" target="_blank" rel="noreferrer noopener"><?php echo esc_html__( 'Register for the Teen Tournament', 'ymcatmtb' ); ?></a>
			</p>
		</div>

		<div class="ymc-card ymc-card--adult">
			<p class="ymc-card__number">02</p>
			<p class="ymc-card__meta"><?php echo esc_html__( 'Start a team or join your captain\'s team', 'ymcatmtb' ); ?></p>
			<h3 class="ymc-card__name"><?php echo esc_html__( 'Adult Tournament', 'ymcatmtb' ); ?></h3>
			<p class="ymc-card__text"><?php echo esc_html__( 'Bring your team together and compete in support of YMC. All adult players must be part of a team.', 'ymcatmtb' ); ?></p>

			<div class="ymc-card__figures">
				<p class="ymc-card__figure"><strong><?php echo esc_html__( '$180', 'ymcatmtb' ); ?></strong><?php echo esc_html__( 'per player to register today', 'ymcatmtb' ); ?></p>
				<p class="ymc-card__figure"><strong><?php echo esc_html__( '$15,000', 'ymcatmtb' ); ?></strong><?php echo esc_html__( 'total fundraising commitment per team', 'ymcatmtb' ); ?></p>
			</div>

			<p class="ymc-card__note"><?php echo esc_html__( 'Every player registration payment counts toward the team goal.', 'ymcatmtb' ); ?></p>

			<p class="ymc-card__actions">
				<a class="ymc-button" href="<?php echo esc_url( $ymc_start ); ?>" target="_blank" rel="noreferrer noopener"><?php echo esc_html__( 'Start a Team', 'ymcatmtb' ); ?></a>
				<a class="ymc-button ymc-button--ghost" href="<?php echo esc_url( $ymc_join ); ?>" target="_blank" rel="noreferrer noopener"><?php echo esc_html__( 'Join Your Team', 'ymcatmtb' ); ?></a>
			</p>

			<p class="ymc-card__invite"><?php echo esc_html__( 'Joining an existing team? Use the invitation provided by your captain.', 'ymcatmtb' ); ?></p>
		</div>

	</div>
	<!-- /wp:html -->
</div>
<!-- /wp:group -->
