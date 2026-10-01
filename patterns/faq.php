<?php
/**
 * Title: Good to know
 * Slug: ymcatmtb/faq
 * Categories: text
 * Description: The three questions players ask, and who to contact.
 *
 * @package WordPress
 * @subpackage YMCatMT
 * @since 1.0.0
 */

$ymc_email = function_exists( 'ymcatmtb_contact_email' ) ? ymcatmtb_contact_email() : '';

$ymc_questions = array(
	array(
		'number' => '01',
		'q'      => __( 'Who can enter the Teen Tournament?', 'ymcatmtb' ),
		'a'      => __( 'Students in grades 8 to 12, including eighth graders. Players register individually and are grouped into grades 8 to 9 and grades 10 to 12.', 'ymcatmtb' ),
	),
	array(
		'number' => '02',
		'q'      => __( 'Can adults register without a team?', 'ymcatmtb' ),
		'a'      => __( 'Adults must start a team as captain or join an existing team through their captain invitation.', 'ymcatmtb' ),
	),
	array(
		'number' => '03',
		'q'      => __( 'Is registration extra on top of the fundraising goal?', 'ymcatmtb' ),
		'a'      => __( 'No. The $100 teen registration counts toward the individual $850 goal. Each adult $180 registration counts toward the team $15,000 goal.', 'ymcatmtb' ),
	),
);
?>
<!-- wp:group {"align":"full","className":"ymc-faq","layout":{"type":"constrained","contentSize":"1140px"}} -->
<div class="wp-block-group alignfull ymc-faq">
	<!-- wp:paragraph {"className":"ymc-eyebrow"} -->
	<p class="ymc-eyebrow"><?php echo esc_html__( 'FAQ', 'ymcatmtb' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":2,"className":"ymc-section__title"} -->
	<h2 class="wp-block-heading ymc-section__title"><?php echo esc_html__( 'Good to know', 'ymcatmtb' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:html -->
	<div class="ymc-faq__list">
		<?php foreach ( $ymc_questions as $ymc_question ) : ?>
			<div class="ymc-question">
				<p class="ymc-question__number"><?php echo esc_html( $ymc_question['number'] ); ?></p>
				<div class="ymc-question__copy">
					<p class="ymc-question__q"><?php echo esc_html( $ymc_question['q'] ); ?></p>
					<p class="ymc-question__a"><?php echo esc_html( $ymc_question['a'] ); ?></p>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<p class="ymc-faq__contact"><?php
		printf(
			/* translators: %s: email link for Rabbi Yossi Kohen. */
			esc_html__( 'Questions? Contact Rabbi Yossi Kohen at %s.', 'ymcatmtb' ),
			'<a href="mailto:' . esc_attr( $ymc_email ) . '">' . esc_html( $ymc_email ) . '</a>'
		);
	?></p>
	<!-- /wp:html -->
</div>
<!-- /wp:group -->
