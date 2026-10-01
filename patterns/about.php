<?php
/**
 * Title: About the event
 * Slug: ymcatmtb/about
 * Categories: text, featured
 * Description: What the tournament is, with four facts: when, where, who plays and why.
 *
 * @package WordPress
 * @subpackage YMCatMT
 * @since 1.0.0
 */

$ymc_facts = array(
	array(
		'label' => __( 'When', 'ymcatmtb' ),
		'value' => __( 'March 28, 2027', 'ymcatmtb' ),
		'note'  => __( 'One day of games. Game-day details are sent to players before the event.', 'ymcatmtb' ),
	),
	array(
		'label' => __( 'Where', 'ymcatmtb' ),
		'value' => __( 'M&T Bank Stadium', 'ymcatmtb' ),
		'note'  => __( 'Baltimore, Maryland. Home of the Baltimore Ravens.', 'ymcatmtb' ),
	),
	array(
		'label' => __( 'Who plays', 'ymcatmtb' ),
		'value' => __( 'Teens and adults', 'ymcatmtb' ),
		'note'  => __( 'A Teen Tournament for grades 8 to 12, and an Adult Tournament played in teams.', 'ymcatmtb' ),
	),
	array(
		'label' => __( 'Why', 'ymcatmtb' ),
		'value' => __( '$750,000 goal', 'ymcatmtb' ),
		'note'  => __( 'Every registration and every dollar raised supports the YMC Annual Campaign.', 'ymcatmtb' ),
	),
);
?>
<!-- wp:group {"align":"full","className":"ymc-about","layout":{"type":"constrained","contentSize":"1140px"}} -->
<div class="wp-block-group alignfull ymc-about">
	<!-- wp:paragraph {"className":"ymc-eyebrow"} -->
	<p class="ymc-eyebrow"><?php echo esc_html__( 'About the event', 'ymcatmtb' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":2,"className":"ymc-section__title"} -->
	<h2 class="wp-block-heading ymc-section__title"><?php echo esc_html__( 'What is YMC at M&T?', 'ymcatmtb' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"className":"ymc-about__text"} -->
	<p class="ymc-about__text"><?php echo esc_html__( 'YMC at M&T is a community flag football tournament on the field at M&T Bank Stadium, home of the Baltimore Ravens. Talmidim, alumni, parents and friends of the yeshiva take the field together, and every player fundraises in support of the YMC Annual Campaign.', 'ymcatmtb' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:html -->
	<div class="ymc-facts">
		<?php foreach ( $ymc_facts as $ymc_fact ) : ?>
			<div class="ymc-fact">
				<p class="ymc-fact__label"><?php echo esc_html( $ymc_fact['label'] ); ?></p>
				<p class="ymc-fact__value"><?php echo esc_html( $ymc_fact['value'] ); ?></p>
				<p class="ymc-fact__note"><?php echo esc_html( $ymc_fact['note'] ); ?></p>
			</div>
		<?php endforeach; ?>
	</div>
	<!-- /wp:html -->
</div>
<!-- /wp:group -->
