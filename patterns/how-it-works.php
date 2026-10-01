<?php
/**
 * Title: How it works
 * Slug: ymcatmtb/how-it-works
 * Categories: text
 * Description: Three steps from registering to taking the field.
 *
 * @package WordPress
 * @subpackage YMCatMT
 * @since 1.0.0
 */

$ymc_steps = array(
	array(
		'number' => '01',
		'tag'    => __( 'Kickoff', 'ymcatmtb' ),
		'title'  => __( 'Register and pay', 'ymcatmtb' ),
		'text'   => __( 'Choose your tournament and complete your registration payment to confirm your place.', 'ymcatmtb' ),
	),
	array(
		'number' => '02',
		'tag'    => __( 'Midfield', 'ymcatmtb' ),
		'title'  => __( 'Start fundraising', 'ymcatmtb' ),
		'text'   => __( 'Share your fundraising page with family and friends. Your registration payment has already started you toward your goal.', 'ymcatmtb' ),
	),
	array(
		'number' => '03',
		'tag'    => __( 'End zone', 'ymcatmtb' ),
		'title'  => __( 'Take the field', 'ymcatmtb' ),
		'text'   => __( 'Join us at M&T Bank Stadium on March 28. We will send game-day details before the event.', 'ymcatmtb' ),
	),
);
?>
<!-- wp:group {"align":"full","className":"ymc-steps","layout":{"type":"constrained","contentSize":"1140px"}} -->
<div class="wp-block-group alignfull ymc-steps">
	<!-- wp:paragraph {"className":"ymc-eyebrow"} -->
	<p class="ymc-eyebrow"><?php echo esc_html__( 'The drive', 'ymcatmtb' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":2,"className":"ymc-section__title"} -->
	<h2 class="wp-block-heading ymc-section__title"><?php echo esc_html__( 'How it works', 'ymcatmtb' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:html -->
	<div class="ymc-steps__grid">
		<?php foreach ( $ymc_steps as $ymc_step ) : ?>
			<div class="ymc-step">
				<p class="ymc-step__number"><?php echo esc_html( $ymc_step['number'] ); ?></p>
				<p class="ymc-step__tag"><?php echo esc_html( $ymc_step['tag'] ); ?></p>
				<h3 class="ymc-step__title"><?php echo esc_html( $ymc_step['title'] ); ?></h3>
				<p class="ymc-step__text"><?php echo esc_html( $ymc_step['text'] ); ?></p>
			</div>
		<?php endforeach; ?>
	</div>
	<!-- /wp:html -->
</div>
<!-- /wp:group -->
