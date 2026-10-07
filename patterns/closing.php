<?php
/**
 * Title: closing
 * Slug: ymcatmtb/closing
 * Categories: featured
 * Description: Matches the supplied YMC Event Registration reference.
 */
ob_start();
?>
<!-- wp:html -->
<section data-dc-tpl="215" data-ymc-section="10" style="position: relative; overflow: hidden; background: rgb(36, 16, 70);">
    <div data-dc-tpl="216" style="position: absolute; inset: 0px; background: url(&quot;<?php echo esc_url( get_theme_file_uri( 'assets/reference/8e102645a1acd3f5.jpg' ) ); ?>&quot;) center 60% / cover no-repeat; opacity: 0.35;"></div>
    <div data-dc-tpl="217" style="position: absolute; inset: 0px; background: linear-gradient(rgba(13, 13, 13, 0.9) 0%, rgba(36, 16, 70, 0.7) 50%, rgba(13, 13, 13, 0.95) 100%);"></div>
    <div data-dc-tpl="218" style="position: relative; max-width: 1240px; margin: 0px auto; padding: clamp(80px, 10vw, 140px) clamp(20px, 4vw, 40px); text-align: center;">
      <div data-dc-tpl="219" style="font-weight: 600; font-size: 13px; letter-spacing: 0.22em; text-transform: uppercase; color: rgb(212, 175, 55);">Sunday, March 28, 2027 · M&amp;T Bank Stadium</div>
      <h2 data-dc-tpl="220" style="margin: 18px 0px 0px; font-family: var(--font-display); font-weight: 400; font-size: clamp(64px, 11vw, 160px); line-height: 0.84; text-transform: uppercase; color: rgb(255, 255, 255);">See you<br data-dc-tpl="221"><span data-dc-tpl="222" style="color: rgb(212, 175, 55);">on the field</span></h2>
      <div data-dc-tpl="223" style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap; margin-top: 40px;">
        <a data-dc-tpl="224" href="#tournaments" class="scp2" style="display: inline-flex; align-items: center; justify-content: center; gap: 12px; min-width: 210px; padding: 20px 34px; border-radius: 4px; background: rgb(212, 175, 55); color: rgb(36, 16, 70); font-weight: 700; font-size: 15px; letter-spacing: 0.18em; text-transform: uppercase; transition: all 200ms var(--ease-out);">Register </a>
        <a data-dc-tpl="226" href="#sponsor" class="scp4" style="display: inline-flex; align-items: center; justify-content: center; gap: 12px; min-width: 210px; padding: 19px 33px; border-radius: 4px; border: 1px solid rgb(212, 175, 55); color: rgb(212, 175, 55); font-weight: 700; font-size: 15px; letter-spacing: 0.18em; text-transform: uppercase; transition: all 200ms var(--ease-out);">Sponsor </a>
      </div>
    </div>
  </section>
<!-- /wp:html -->

<?php
echo ymcatmtb_section_markup('Closing call to action', ob_get_clean());
