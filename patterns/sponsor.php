<?php
/**
 * Title: sponsor
 * Slug: ymcatmtb/sponsor
 * Categories: featured
 * Description: VIP sponsorship packages, compared before any payment is asked for.
 */

/*
 * Prices and benefits come from the school's sponsorship sheet.
 * "Play in the Ravens Experience" is deliberately absent from the top
 * package: the stadium experience is not to be advertised until the school
 * has settled its logistics.
 */
$ymcatmtb_packages = array(
	array(
		'name'      => 'First Down',
		'fund'      => '70797',
		'price'     => '$1,800',
		'amount'    => '1800',
		'benefits'  => array(
			'4 general admission tickets',
			'Team sponsorship',
			'VIP entry not included',
			'Free parking',
			'Dedicate one week of Erev Shabbos Chizuk by Rabbi Silber',
		),
	),
	array(
		'name'      => 'Touchdown',
		'fund'      => '70798',
		'price'     => '$2,500',
		'amount'    => '2500',
		'benefits'  => array(
			'2 tickets to the VIP experience',
			'Small logo placement',
			'Free parking',
			'Dedicate a day of learning at YMC',
		),
	),
	array(
		'name'      => 'Pro Bowl',
		'fund'      => '70799',
		'price'     => '$3,600',
		'amount'    => '3600',
		'benefits'  => array(
			'4 tickets to the VIP experience',
			'Small logo placement',
			'VIP reception signage',
			'Free parking',
			'Dedicate one week of learning at YMC',
		),
	),
	array(
		'name'      => 'MVP',
		'fund'      => '70800',
		'price'     => '$5,000',
		'amount'    => '5000',
		'benefits'  => array(
			'4 tickets to the VIP experience',
			'Logo placement on merchandise',
			'Logo placement on boards',
			'VIP reception signage',
			'Free parking',
			'Dedicate one month of learning at YMC',
		),
	),
	array(
		'name'      => 'Playoffs',
		'fund'      => '70801',
		'price'     => '$7,500',
		'amount'    => '7500',
		'benefits'  => array(
			'6 tickets to the VIP experience',
			'Logo placement on merchandise',
			'Logo placement on boards',
			'VIP reception signage',
			'Free parking',
			'Dedicate two months of learning at YMC',
		),
	),
	array(
		'name'      => 'Super Bowl',
		'fund'      => '70802',
		'price'     => '$10,000',
		'amount'    => '10000',
		'benefits'  => array(
			'8 tickets to the VIP experience',
			'Premium advertising placement across event media',
			'Logo placement on merchandise',
			'VIP reception signage',
			'Free parking',
			'Dedicate three months of learning at YMC',
		),
	),
);

ob_start();
?>
<!-- wp:html -->
<section data-dc-tpl="174" id="sponsor" data-ymc-section="8" style="position: relative; overflow: hidden; background: rgb(36, 16, 70);">

    <div data-dc-tpl="175" style="position: absolute; top: 0px; right: 0px; width: 45%; height: 100%; background: linear-gradient(115deg, transparent 0px, transparent 30%, rgba(212, 175, 55, 0.12) 30%, rgba(212, 175, 55, 0.12) 34%, transparent 34%, transparent 40%, rgba(212, 175, 55, 0.08) 40%, rgba(212, 175, 55, 0.08) 43%, transparent 43%); pointer-events: none;"></div>
    <div data-dc-tpl="176" style="position: relative; max-width: 1240px; margin: 0px auto; padding: clamp(64px, 8vw, 104px) clamp(20px, 4vw, 40px);">
      <div data-dc-tpl="177" style="max-width: 640px;">
        <div data-dc-tpl="178" style="display: flex; align-items: center; gap: 14px;">
          <span data-dc-tpl="179" style="width: 36px; height: 2px; background: rgb(212, 175, 55);"></span>
          <span data-dc-tpl="180" style="font-weight: 600; font-size: 12px; letter-spacing: 0.22em; text-transform: uppercase; color: rgb(212, 175, 55);">VIP Sponsorships</span>
        </div>
        <h2 data-dc-tpl="181" style="margin: 14px 0px 0px; font-family: var(--font-display); font-weight: 400; font-size: clamp(52px, 7vw, 88px); line-height: 0.88; text-transform: uppercase; color: rgb(255, 255, 255);">Sponsor<br data-dc-tpl="182"><span data-dc-tpl="183" style="color: transparent; -webkit-text-stroke: 2px rgb(212, 175, 55);">the game</span></h2>
        <p data-dc-tpl="184" style="margin: 22px 0px 0px; font-size: 16px; line-height: 1.65; color: rgba(255, 255, 255, 0.84);">Put your business in front of the community on game day. Every package goes toward YMC&rsquo;s $750,000 Annual Campaign and dedicates learning at the yeshiva in your name.</p>
      </div>

      <div class="ymc-ref-packages" style="margin-top: 48px; display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 300px), 1fr)); gap: 16px;">
<?php foreach ( $ymcatmtb_packages as $ymcatmtb_package ) : ?>
        <div style="display: flex; flex-direction: column; padding: 28px 26px 26px; border-radius: 6px; border: 1px solid rgba(212, 175, 55, 0.4); background: rgb(13, 13, 13);">
          <div style="display: flex; align-items: baseline; justify-content: space-between; gap: 12px;">
            <h3 style="margin: 0px; font-family: var(--font-display); font-weight: 400; font-size: 34px; line-height: 1; text-transform: uppercase; color: rgb(255, 255, 255);"><?php echo esc_html( $ymcatmtb_package['name'] ); ?></h3>
          </div>
          <div style="margin-top: 10px; font-family: var(--font-condensed); font-weight: 700; font-size: 46px; line-height: 1; font-variant-numeric: tabular-nums; color: rgb(212, 175, 55);"><?php echo esc_html( $ymcatmtb_package['price'] ); ?></div>
          <ul style="margin: 20px 0px 0px; padding: 0px; list-style: none; display: grid; gap: 9px; flex: 1 1 auto;">
<?php foreach ( $ymcatmtb_package['benefits'] as $ymcatmtb_benefit ) : ?>
            <li style="display: flex; gap: 10px; font-size: 14px; line-height: 1.55; color: rgba(255, 255, 255, 0.84);"><span style="flex-shrink: 0; color: rgb(212, 175, 55);">&#9656;</span><span><?php echo esc_html( $ymcatmtb_benefit ); ?></span></li>
<?php endforeach; ?>
          </ul>
          <a href="https://givebutter.com/ymc-annual-campaign/donate?amount=<?php echo esc_attr( $ymcatmtb_package['amount'] ); ?>&amp;fund=<?php echo esc_attr( $ymcatmtb_package['fund'] ); ?>" target="_blank" rel="noopener" class="scpc" style="margin-top: 22px; display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 15px 20px; border-radius: 4px; background: rgb(212, 175, 55); color: rgb(36, 16, 70); font-weight: 700; font-size: 12px; letter-spacing: 0.16em; text-transform: uppercase; transition: all 200ms var(--ease-out);">Choose <?php echo esc_html( $ymcatmtb_package['name'] ); ?> <i class="fas fa-arrow-right" style="font-size: 12px;"></i></a>
        </div>
<?php endforeach; ?>
      </div>

      <p style="margin: 26px 0px 0px; font-size: 14px; line-height: 1.6; color: rgba(255, 255, 255, 0.7);">Sponsoring on behalf of a business, or want to talk a package through first? Contact Rabbi Yossi Kohen at <a href="mailto:ykohen@yeshivasmekorchaim.org" style="color: rgb(212, 175, 55); word-break: break-word;">ykohen@yeshivasmekorchaim.org</a>.</p>
    </div>
  </section>
<!-- /wp:html -->

<?php
echo ymcatmtb_section_markup( 'Sponsorship', ob_get_clean() );
