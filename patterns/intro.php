<?php
/**
 * Title: Opening statement
 * Slug: ymcatmtb/intro
 * Categories: text
 * Description: The case for the day in three lines, above the tournament cards.
 *
 * @package WordPress
 * @subpackage YMCatMT
 * @since 1.0.0
 */

?>
<!-- wp:group {"align":"full","className":"ymc-intro","layout":{"type":"constrained","contentSize":"860px"}} -->
<div class="wp-block-group alignfull ymc-intro">
	<!-- wp:paragraph {"className":"ymc-eyebrow"} -->
	<p class="ymc-eyebrow"><?php echo esc_html__( 'On the field at M&T', 'ymcatmtb' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":2,"className":"ymc-intro__title"} -->
	<h2 class="wp-block-heading ymc-intro__title"><?php echo esc_html__( 'Take the field. Support YMC.', 'ymcatmtb' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"className":"ymc-intro__text"} -->
	<p class="ymc-intro__text"><?php echo esc_html__( 'Join us for a day of football and community at M&T Bank Stadium, supporting the learning and growth of YMC talmidim.', 'ymcatmtb' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:paragraph {"className":"ymc-intro__cue"} -->
	<p class="ymc-intro__cue"><?php echo esc_html__( 'Choose your tournament to get started', 'ymcatmtb' ); ?></p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
