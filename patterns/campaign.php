<?php
/**
 * Title: campaign
 * Slug: ymcatmtb/campaign
 * Categories: featured
 * Description: Matches the supplied YMC Event Registration reference.
 */
ob_start();
?>
<!-- wp:html -->
<section data-dc-tpl="50" id="campaign" data-ymc-section="3" style="position: relative; background: rgb(36, 16, 70); overflow: hidden;">
    
    <div data-dc-tpl="51" style="position: relative; max-width: 1240px; margin: 0px auto; padding: 56px clamp(20px, 4vw, 40px); display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 300px), 1fr)); gap: 28px 56px; align-items: center;">
      <div data-dc-tpl="52">
        <div data-dc-tpl="53" style="display: flex; align-items: center; gap: 14px;">
          <span data-dc-tpl="54" style="width: 36px; height: 2px; background: rgb(212, 175, 55);"></span>
          <span data-dc-tpl="55" style="font-weight: 600; font-size: 12px; letter-spacing: 0.22em; text-transform: uppercase; color: rgb(212, 175, 55);">YMC Annual Campaign</span>
        </div>
        <h2 data-dc-tpl="56" style="margin: 14px 0px 0px; font-family: var(--font-display); font-weight: 400; font-size: clamp(40px, 5vw, 60px); line-height: 0.92; text-transform: uppercase; color: rgb(255, 255, 255);">Begin the year with tzedakah</h2>
        <p data-dc-tpl="57" style="margin: 14px 0px 0px; max-width: 460px; font-size: 15px; line-height: 1.65; color: rgba(255, 255, 255, 0.82);">Every registration and every gift goes toward our $750,000 Annual Campaign goal.</p>
      </div>
      <div data-dc-tpl="58" style="display: flex; align-items: stretch; gap: 0px; flex-wrap: wrap; border: 1px solid rgba(212, 175, 55, 0.4); border-radius: 4px; overflow: hidden; background: rgb(13, 13, 13);">
        <div data-dc-tpl="59" style="flex: 1 1 0%; min-width: 220px; padding: 26px 28px; border-right: 1px solid rgba(212, 175, 55, 0.3);">
          <div data-dc-tpl="60" style="font-weight: 600; font-size: 11px; letter-spacing: 0.22em; text-transform: uppercase; color: rgb(176, 179, 184);">Campaign goal</div>
          <div data-dc-tpl="61" style="margin-top: 8px; font-family: var(--font-condensed); font-weight: 700; font-size: clamp(48px, 6vw, 64px); line-height: 1; font-variant-numeric: tabular-nums; color: rgb(212, 175, 55);">$750,000</div>
        </div>
        <div data-dc-tpl="62" style="display: flex; flex-direction: column; justify-content: center; gap: 10px; padding: 22px 24px; min-width: 200px;">
          <a data-dc-tpl="63" href="https://givebutter.com/ymc-annual-campaign" target="_blank" rel="noopener" class="scp5" style="display: flex; align-items: center; justify-content: center; padding: 14px 22px; border-radius: 4px; background: rgb(212, 175, 55); color: rgb(36, 16, 70); font-weight: 700; font-size: 13px; letter-spacing: 0.16em; text-transform: uppercase;">Give Now</a>
          <a data-dc-tpl="64" href="https://givebutter.com/ymc-annual-campaign" class="scp6" style="display: flex; align-items: center; justify-content: center; padding: 13px 22px; border-radius: 4px; border: 1px solid rgba(255, 255, 255, 0.3); color: rgb(255, 255, 255); font-weight: 700; font-size: 13px; letter-spacing: 0.16em; text-transform: uppercase;">Learn more</a>
        </div>
      </div>
    </div>
  </section>
<!-- /wp:html -->

<?php
echo ymcatmtb_section_markup('Annual campaign', ob_get_clean());
