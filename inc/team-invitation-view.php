<?php
namespace YMC\CaptainInvitations;
defined( 'ABSPATH' ) || exit;
$found = $invite && ! $error && ! $closed;
$lookup = add_query_arg( 'ymc_join', '1', home_url( '/' ) );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head><meta charset="<?php bloginfo( 'charset' ); ?>"><meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?></head>
<body <?php body_class( 'ymc-invitation-page' ); ?>>
<?php wp_body_open(); ?>
<a class="ymci-skip" href="#invitation">Skip to invitation</a>
<div class="ymci-shell">
 <header class="ymci-header">
  <a class="ymci-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/event-site-icon.png' ) ); ?>" alt="YMC at M&amp;T" width="58" height="58"><span>YMC AT M&amp;T<span>PLAY WITH PURPOSE</span></span></a>
  <a class="ymci-home" href="<?php echo esc_url( home_url( '/' ) ); ?>">Back to event <span aria-hidden="true">↗</span></a>
 </header>
 <main id="invitation" class="ymci-layout">
  <section class="ymci-story" aria-labelledby="invitation-title">
   <p class="ymci-eyebrow"><span></span> ADULT TOURNAMENT · 2027</p>
   <h1 id="invitation-title">Your team.<br>Your field.<br><em>Your moment.</em></h1>
   <p class="ymci-intro">Take the field at M&amp;T Bank Stadium.<br>Bring your team. Make a difference for YMC.</p>
   <div class="ymci-event"><span class="ymci-date">28<small>MAR</small></span><div><strong>Sunday, March 28, 2027</strong><span>M&amp;T Bank Stadium · Baltimore</span></div></div>
   <div class="ymci-field" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><b>YMC</b></div>
  </section>
  <section class="ymci-card" aria-labelledby="team-heading">
   <p class="ymci-kicker"><?php echo $found ? 'YOUR TEAM INVITATION' : 'LET’S GET YOU ON THE FIELD'; ?></p>
   <?php if ( $closed ) : ?>
    <h2 id="team-heading">Registration<br>has closed.</h2><p class="ymci-description">Adult tournament registration closed on February 1, 2027.</p><a class="ymci-button" href="<?php echo esc_url( home_url( '/' ) ); ?>">Return to the event <span aria-hidden="true">→</span></a>
   <?php elseif ( $found ) : ?>
    <div class="ymci-success"><span aria-hidden="true">✓</span> Team found</div>
    <h2 id="team-heading"><?php echo esc_html( $invite['team_name'] ); ?></h2>
    <p class="ymci-description">Your captain invited you to be part of the team.</p>
    <div class="ymci-code"><span>TEAM CODE</span><strong><?php echo esc_html( $code ); ?></strong></div>
    <?php if ( $full ) : ?>
     <div class="ymci-notice" role="status">This team is full or its availability could not be confirmed. Please contact your captain.</div>
    <?php else : ?>
     <div class="ymci-next"><span class="ymci-step">NEXT</span><div><strong>Complete your player registration</strong><p>In Givebutter, select <b><?php echo esc_html( $invite['team_name'] ); ?></b> under “Join a team,” then choose the <b>Adult Tournament — Player</b> ticket.</p></div></div>
     <a class="ymci-button" href="<?php echo esc_url( REGISTRATION ); ?>">Continue to Givebutter <span aria-hidden="true">→</span></a>
     <p class="ymci-note">Your place is confirmed after registration.</p>
    <?php endif; ?>
    <a class="ymci-other" href="<?php echo esc_url( $lookup ); ?>">Have a different team code?</a>
   <?php else : ?>
    <h2 id="team-heading">Every great game<br>starts with a team.</h2>
    <p class="ymci-description">Enter the code your captain shared to find your team.</p>
    <?php if ( $error ) : ?><div id="ymci-error" class="ymci-notice" role="alert"><?php echo esc_html( $error ); ?></div><?php endif; ?>
    <form class="ymci-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
     <label for="ymc-team-code">Your team code</label>
     <input id="ymc-team-code" name="ymc_join" maxlength="12" required autocomplete="off" autocapitalize="characters" spellcheck="false" pattern="[A-Fa-f0-9]{12}" placeholder="e.g. A3AB72621C16" value="<?php echo esc_attr( $error && preg_match( '/^[A-F0-9]{12}$/', $code ) ? $code : '' ); ?>" aria-describedby="ymci-code-help<?php echo $error ? ' ymci-error' : ''; ?>" <?php echo $error ? 'aria-invalid="true"' : ''; ?>>
     <p id="ymci-code-help" class="ymci-note">Use the 12-character code from your invitation.</p>
     <button class="ymci-button" type="submit">Find my team <span aria-hidden="true">→</span></button>
    </form>
    <div class="ymci-help"><strong>Have an invitation link?</strong><p>Open the link from your captain to go straight to your team’s invitation.</p></div>
   <?php endif; ?>
  </section>
 </main>
 <footer class="ymci-footer"><span>ONE FIELD. ONE COMMUNITY.</span><span>Yeshivas Mekor Chaim</span></footer>
</div>
<?php wp_footer(); ?>
</body></html>
