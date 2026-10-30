<?php
/**
 * Title: footer
 * Slug: ymcatmtb/footer
 * Categories: featured
 * Description: Matches the supplied YMC Event Registration reference.
 *
 * The Campaign column is the site's only shared link list. Every template pulls
 * this part in, the homepage included since its own embedded copy was removed,
 * so a link added here appears everywhere and nowhere else needs editing.
 *
 * Bump Version in style.css when changing this file. WordPress caches the
 * pattern index against the theme version and will otherwise keep serving the
 * previous markup.
 */
ob_start();
?>
<!-- wp:html -->
<a data-dc-tpl="228" href="https://ymc.school/" target="_blank" rel="noopener" aria-label="Visit ymc.school" class="scpe scp3 ymc-ref-badge" data-ymc-section="11" style="position: fixed; right: clamp(14px, 2.5vw, 28px); bottom: clamp(14px, 2.5vw, 28px); z-index: 60; width: 112px; height: 112px; border-radius: 50%; display: block; background: rgb(36, 16, 70); border: 1px solid rgba(212, 175, 55, 0.6); box-shadow: rgba(0, 0, 0, 0.5) 0px 12px 30px; transition: transform 250ms var(--ease-out),box-shadow 250ms var(--ease-out);">
    <svg viewBox="0 0 120 120" style="position: absolute; inset: 0px; width: 100%; height: 100%; animation: 18s linear 0s infinite normal none running ymcSpin;"><defs><path id="ymcRing" d="M60,60 m-47,0 a47,47 0 1,1 94,0 a47,47 0 1,1 -94,0"></path></defs><text style="font-family: var(--font-body); font-weight: 700; font-size: 10.4px; letter-spacing: 1.8px; text-transform: uppercase; fill: rgb(212, 175, 55);"><textPath href="#ymcRing" textLength="294" lengthAdjust="spacing">Yeshivas Mekor Chaim · ymc.school · </textPath></text></svg>
    <img data-dc-tpl="229" src="<?php echo esc_url( get_theme_file_uri( 'assets/reference/3deee3107dd7b6f3.png' ) ); ?>" alt="" style="position: absolute; left: 50%; top: 50%; width: 46%; height: auto; transform: translate(-50%, -50%); display: block;">
  </a>
<footer data-dc-tpl="230" data-ymc-section="12" style="background: rgb(13, 13, 13); border-top: 1px solid rgba(212, 175, 55, 0.3);">
    <div data-dc-tpl="231" style="max-width: 1240px; margin: 0px auto; padding: 52px clamp(20px, 4vw, 40px) 28px; display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 220px), 1fr)); gap: 32px 48px;">
      <div data-dc-tpl="232">
        <div data-dc-tpl="233" style="display: flex; align-items: center; gap: 14px;">
          <img data-dc-tpl="234" src="<?php echo esc_url( get_theme_file_uri( 'assets/reference/3deee3107dd7b6f3.png' ) ); ?>" alt="Yeshivas Mekor Chaim" style="height: 56px; width: auto;">
          <img data-dc-tpl="235" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/ymc-event-logo-transparent.png' ) ); ?>" alt="YMC at M&amp;T" style="height: 64px; width: auto;">
        </div>
        <p data-dc-tpl="236" style="margin: 16px 0px 0px; font-family: var(--font-school); font-weight: 600; font-size: 15px; color: rgb(255, 255, 255);">Yeshivas Mekor Chaim</p>
        <p data-dc-tpl="237" style="margin: 4px 0px 0px; font-size: 15px; color: rgb(212, 175, 55); direction: rtl; text-align: left;">ישיבת מקור חיים</p>
      </div>
      <div data-dc-tpl="238">
        <div data-dc-tpl="239" style="font-weight: 600; font-size: 12px; letter-spacing: 0.18em; text-transform: uppercase; color: rgb(212, 175, 55);">Visit</div>
        <p data-dc-tpl="240" style="margin: 12px 0px 0px; font-size: 14px; line-height: 1.8; color: rgb(176, 179, 184);"><a data-dc-tpl="241" href="https://maps.google.com/?q=7401+Park+Heights+Ave,+Baltimore,+MD+21208" target="_blank" rel="noopener" class="scpf" style="color: rgb(176, 179, 184);">7401 Park Heights Ave<br data-dc-tpl="242">Baltimore, MD 21208</a><br data-dc-tpl="243"><a data-dc-tpl="244" href="tel:4105781111" class="scpf" style="color: rgb(176, 179, 184);">410-578-1111</a></p>
      </div>
      <div data-dc-tpl="245">
        <div data-dc-tpl="246" style="font-weight: 600; font-size: 12px; letter-spacing: 0.18em; text-transform: uppercase; color: rgb(212, 175, 55);">Campaign</div>
        <div data-dc-tpl="247" style="margin-top: 12px; display: grid; gap: 8px; font-size: 14px;">
          <a data-dc-tpl="248" href="<?php echo esc_url( home_url( '/#tournaments' ) ); ?>" class="scpf" style="color: rgb(176, 179, 184);">Register to Play</a>
          <a href="https://givebutter.com/ymc-at-mtb-tickets" class="scpf" style="color: rgb(176, 179, 184);">Come watch / Tickets</a>
          <a href="<?php echo esc_url( home_url( '/player-forms/' ) ); ?>" class="scpf" style="color: rgb(176, 179, 184);">Player Forms</a>
          <a href="<?php echo esc_url( home_url( '/register-a-team/' ) ); ?>" class="scpf" style="color: rgb(176, 179, 184);">Register a Team</a>
          <a href="<?php echo esc_url( home_url( '/need-a-team/' ) ); ?>" class="scpf" style="color: rgb(176, 179, 184);">Need a Team</a>
          <a data-dc-tpl="249" href="https://givebutter.com/ymc-annual-campaign/donate" target="_blank" rel="noopener" class="scpf" style="color: rgb(176, 179, 184);">Donate</a>
          <a data-dc-tpl="250" href="https://givebutter.com/ymc-annual-campaign" class="scpf" style="color: rgb(176, 179, 184);">Annual Campaign</a>
        </div>
      </div>
      <div data-dc-tpl="252">
        <div data-dc-tpl="253" style="font-weight: 600; font-size: 12px; letter-spacing: 0.18em; text-transform: uppercase; color: rgb(212, 175, 55);">Follow</div>
        <div data-dc-tpl="254" style="margin-top: 14px; display: flex; gap: 10px;">
          <a data-dc-tpl="255" href="https://www.instagram.com/yeshivasmekorchaim/" target="_blank" rel="noopener" aria-label="Instagram" class="scp0" style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; border: 1px solid rgba(212, 175, 55, 0.45); color: rgb(212, 175, 55);"><i data-dc-tpl="256" class="fab fa-instagram"></i></a>
          <a data-dc-tpl="257" href="https://www.facebook.com/yeshivasmekorchaim/" target="_blank" rel="noopener" aria-label="Facebook" class="scp0" style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; border: 1px solid rgba(212, 175, 55, 0.45); color: rgb(212, 175, 55);"><i data-dc-tpl="258" class="fab fa-facebook-f"></i></a>
          <a data-dc-tpl="259" href="https://www.youtube.com/channel/UC-J_SekzkHMwaLJikacRXyQ" target="_blank" rel="noopener" aria-label="YouTube" class="scp0" style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; border: 1px solid rgba(212, 175, 55, 0.45); color: rgb(212, 175, 55);"><i data-dc-tpl="260" class="fab fa-youtube"></i></a>
        </div>
      </div>
    </div>
    <div data-dc-tpl="261" style="max-width: 1240px; margin: 0px auto; padding: 18px clamp(20px, 4vw, 40px) 28px; border-top: 1px solid rgba(255, 255, 255, 0.08);">
      <p data-dc-tpl="262" style="margin: 0px; font-size: 12px; color: rgb(138, 141, 147);">© 2026 Yeshivas Mekor Chaim · 7401 Park Heights Ave, Baltimore, MD 21208</p>
    </div>
  </footer>
<!-- /wp:html -->

<?php
echo ymcatmtb_section_markup('Footer and floating badge', ob_get_clean());
