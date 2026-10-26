<?php
/**
 * Title: Event page not found
 * Slug: ymcatmtb/hidden-404
 * Inserter: no
 */
?>
<!-- wp:html -->
<section class="ymce-404" aria-labelledby="ymce-404-title">
 <div class="ymce-404__hero">
  <div class="ymce-404__copy">
   <p class="ymce-404__eyebrow">404 &middot; PAGE NOT FOUND</p>
   <h1 id="ymce-404-title">Let&rsquo;s get you<br>back in the game.</h1>
   <p class="ymce-404__intro">This page is out of bounds. The link may have changed, or the address may have a typo.</p>
   <a class="ymce-404__home" href="<?php echo esc_url(home_url('/')); ?>">Back to home</a>
  </div>
  <div class="ymce-404__number" aria-hidden="true">404<span>THE EVENT IS STILL ON.</span></div>
 </div>
 <div class="ymce-404__body">
  <p class="ymce-404__eyebrow">YOUR NEXT MOVE</p>
  <h2>Find your way to the field.</h2>
  <nav class="ymce-404__links" aria-label="Event pages">
   <?php foreach (array(
    array('/#tournaments','01','Register to play','Explore the adult and teen tournaments.'),
    array('/#tickets','02','Tickets','Find admission options for the event.'),
    array('/player-forms/','03','Player forms','Download the forms you need to participate.'),
    array('/need-a-team/','04','Need a team?','Request help with adult team placement.'),
   ) as $item) : ?>
   <a href="<?php echo esc_url(home_url($item[0])); ?>"><span class="ymce-404__index"><?php echo esc_html($item[1]); ?></span><strong><?php echo esc_html($item[2]); ?></strong><span class="ymce-404__description"><?php echo esc_html($item[3]); ?></span><span class="ymce-404__arrow" aria-hidden="true">&rarr;</span></a>
   <?php endforeach; ?>
  </nav>
  <p class="ymce-404__help">Still need help? <a href="mailto:ykohen@yeshivasmekorchaim.org">Contact the YMC team</a></p>
 </div>
</section>
<!-- /wp:html -->
