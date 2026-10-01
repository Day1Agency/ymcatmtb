<?php
/**
 * Title: Footer
 * Slug: ymcatmtb/footer
 * Categories: footer
 * Block Types: core/template-part/footer
 * Description: The school's name and address, the campaign links, and the copyright line.
 *
 * @package WordPress
 * @subpackage YMCatMT
 * @since 1.0.0
 */

$ymc_donate   = function_exists( 'ymcatmtb_link' ) ? ymcatmtb_link( 'donate' ) : '#';
$ymc_register = function_exists( 'ymcatmtb_link' ) ? ymcatmtb_link( 'teen' ) : '#';
?>
<!-- wp:group {"align":"full","className":"ymc-footer","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull ymc-footer">
	<!-- wp:html -->
	<div class="ymc-footer__inner">

		<div class="ymc-footer__brand">
			<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/ymc-logo-white.png' ) ); ?>" alt="<?php echo esc_attr__( 'Yeshivas Mekor Chaim', 'ymcatmtb' ); ?>">
			<p class="ymc-footer__name"><?php echo esc_html__( 'Yeshivas Mekor Chaim', 'ymcatmtb' ); ?></p>
		</div>

		<div class="ymc-footer__col">
			<p class="ymc-footer__label"><?php echo esc_html__( 'Visit', 'ymcatmtb' ); ?></p>
			<p><?php echo esc_html__( '7401 Park Heights Ave', 'ymcatmtb' ); ?><br><?php echo esc_html__( 'Baltimore, MD 21208', 'ymcatmtb' ); ?></p>
			<p><a href="tel:4105781111"><?php echo esc_html__( '410-578-1111', 'ymcatmtb' ); ?></a></p>
		</div>

		<div class="ymc-footer__col">
			<p class="ymc-footer__label"><?php echo esc_html__( 'Campaign', 'ymcatmtb' ); ?></p>
			<p><a href="<?php echo esc_url( $ymc_register ); ?>" target="_blank" rel="noreferrer noopener"><?php echo esc_html__( 'Register to Play', 'ymcatmtb' ); ?></a></p>
			<p><a href="<?php echo esc_url( $ymc_donate ); ?>" target="_blank" rel="noreferrer noopener"><?php echo esc_html__( 'Donate', 'ymcatmtb' ); ?></a></p>
			<p><a href="https://ymc.school/"><?php echo esc_html__( 'ymc.school', 'ymcatmtb' ); ?></a></p>
		</div>

	</div>

	<p class="ymc-footer__legal"><?php
		printf(
			/* translators: %s: current year. */
			esc_html__( '© %s Yeshivas Mekor Chaim · 7401 Park Heights Ave, Baltimore, MD 21208', 'ymcatmtb' ),
			esc_html( gmdate( 'Y' ) )
		);
	?></p>
	<!-- /wp:html -->
</div>
<!-- /wp:group -->
