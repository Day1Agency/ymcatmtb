<?php
/**
 * Title: sponsor
 * Slug: ymcatmtb/sponsor
 * Categories: featured
 * Description: Matches the supplied YMC Event Registration reference.
 */
ob_start();
?>
<!-- wp:html -->
<section data-dc-tpl="174" id="sponsor" data-ymc-section="8" style="position: relative; overflow: hidden; background: rgb(36, 16, 70);">
    
    <div data-dc-tpl="175" style="position: absolute; top: 0px; right: 0px; width: 45%; height: 100%; background: linear-gradient(115deg, transparent 0px, transparent 30%, rgba(212, 175, 55, 0.12) 30%, rgba(212, 175, 55, 0.12) 34%, transparent 34%, transparent 40%, rgba(212, 175, 55, 0.08) 40%, rgba(212, 175, 55, 0.08) 43%, transparent 43%); pointer-events: none;"></div>
    <div data-dc-tpl="176" style="position: relative; max-width: 1240px; margin: 0px auto; padding: clamp(64px, 8vw, 104px) clamp(20px, 4vw, 40px); display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 380px), 1fr)); gap: 40px 64px; align-items: center;">
      <div data-dc-tpl="177">
        <div data-dc-tpl="178" style="display: flex; align-items: center; gap: 14px;">
          <span data-dc-tpl="179" style="width: 36px; height: 2px; background: rgb(212, 175, 55);"></span>
          <span data-dc-tpl="180" style="font-weight: 600; font-size: 12px; letter-spacing: 0.22em; text-transform: uppercase; color: rgb(212, 175, 55);">Not playing?</span>
        </div>
        <h2 data-dc-tpl="181" style="margin: 14px 0px 0px; font-family: var(--font-display); font-weight: 400; font-size: clamp(52px, 7vw, 88px); line-height: 0.88; text-transform: uppercase; color: rgb(255, 255, 255);">Sponsor<br data-dc-tpl="182"><span data-dc-tpl="183" style="color: transparent; -webkit-text-stroke: 2px rgb(212, 175, 55);">the game</span></h2>
        <p data-dc-tpl="184" style="margin: 22px 0px 0px; max-width: 460px; font-size: 16px; line-height: 1.65; color: rgba(255, 255, 255, 0.84);">Support a player, a team, or the event itself. Every gift goes toward YMC's $750,000 Annual Campaign.</p>
      </div>
      <div data-dc-tpl="185" style="display: grid; gap: 12px;">
        <a data-dc-tpl="186" href="https://givebutter.com/ymc-annual-campaign" target="_blank" rel="noopener" class="scpc" style="display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 26px 28px; border-radius: 4px; background: rgb(212, 175, 55); color: rgb(36, 16, 70); transition: all 200ms var(--ease-out);">
          <span data-dc-tpl="187" style="font-family: var(--font-display); font-size: 34px; line-height: 1; text-transform: uppercase;">Become a Sponsor</span>
          <i data-dc-tpl="188" class="fas fa-arrow-right" style="font-size: 16px;"></i>
        </a>
        <a data-dc-tpl="189" href="https://givebutter.com/ymc-annual-campaign" target="_blank" rel="noopener" class="scpd" style="display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 24px 28px; border-radius: 4px; border: 1px solid rgba(212, 175, 55, 0.5); background: rgb(13, 13, 13); color: rgb(255, 255, 255); transition: all 200ms var(--ease-out);">
          <span data-dc-tpl="190" style="font-family: var(--font-display); font-size: 30px; line-height: 1; text-transform: uppercase;">Support a Player</span>
          <i data-dc-tpl="191" class="fas fa-arrow-right" style="font-size: 14px;"></i>
        </a>
        <a data-dc-tpl="192" href="https://givebutter.com/ymc-annual-campaign" target="_blank" rel="noopener" class="scpd" style="display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 24px 28px; border-radius: 4px; border: 1px solid rgba(212, 175, 55, 0.5); background: rgb(13, 13, 13); color: rgb(255, 255, 255); transition: all 200ms var(--ease-out);">
          <span data-dc-tpl="193" style="font-family: var(--font-display); font-size: 30px; line-height: 1; text-transform: uppercase;">Support a Team</span>
          <i data-dc-tpl="194" class="fas fa-arrow-right" style="font-size: 14px;"></i>
        </a>
      </div>
    </div>
  </section>
<!-- /wp:html -->

<?php
echo ymcatmtb_section_markup('Sponsorship', ob_get_clean());
