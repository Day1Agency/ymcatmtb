<?php
/**
 * Title: intro
 * Slug: ymcatmtb/intro
 * Categories: featured
 * Description: Matches the supplied YMC Event Registration reference.
 */
ob_start();
?>
<!-- wp:html -->
<section data-dc-tpl="65" data-ymc-section="4" style="position: relative; background: rgb(13, 13, 13);">
    <div data-dc-tpl="66" style="max-width: 1240px; margin: 0px auto; padding: clamp(72px, 9vw, 120px) clamp(20px, 4vw, 40px); display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 440px), 1fr)); gap: 48px 64px; align-items: center;">
      <div data-dc-tpl="67" style="position: relative;">
        <div data-dc-tpl="68" style="position: absolute; top: -14px; left: -14px; width: 40%; height: 40%; border-top: 2px solid rgb(212, 175, 55); border-left: 2px solid rgb(212, 175, 55);"></div>
        <div data-dc-tpl="69" style="position: absolute; bottom: -14px; right: -14px; width: 40%; height: 40%; border-bottom: 2px solid rgb(212, 175, 55); border-right: 2px solid rgb(212, 175, 55);"></div>
        <img data-dc-tpl="70" src="<?php echo esc_url( get_theme_file_uri( 'assets/reference/405b3ceb9ac4a23b.png' ) ); ?>" alt="YMC talmidim at M&amp;T Bank Stadium" style="display: block; width: 100%; aspect-ratio: 16 / 10; object-fit: cover; border-radius: 4px; filter: saturate(1.05) contrast(1.05);">
        <div data-dc-tpl="71" style="position: absolute; left: 18px; bottom: 18px; padding: 8px 14px; background: rgba(13, 13, 13, 0.82); border: 1px solid rgba(212, 175, 55, 0.5); border-radius: 2px; font-weight: 600; font-size: 11px; letter-spacing: 0.18em; text-transform: uppercase; color: rgb(212, 175, 55);">On the field at M&amp;T</div>
      </div>
      <div data-dc-tpl="72">
        <h2 data-dc-tpl="73" style="margin: 0px; font-family: var(--font-display); font-weight: 400; font-size: clamp(56px, 7vw, 96px); line-height: 0.86; text-transform: uppercase; color: rgb(255, 255, 255);">Play where<br data-dc-tpl="74"><span data-dc-tpl="75" style="color: rgb(212, 175, 55);">the Ravens play</span></h2>
        <p data-dc-tpl="76" style="margin: 26px 0px 0px; max-width: 480px; font-size: 18px; line-height: 1.65; color: rgba(255, 255, 255, 0.86); text-wrap: pretty;">Join us for a day of football and community at M&amp;T Bank Stadium, supporting the learning and growth of YMC's talmidim.</p>
        <a data-dc-tpl="77" href="#tournaments" class="scp7" style="margin-top: 28px; display: inline-flex; align-items: center; gap: 12px; font-weight: 700; font-size: 13px; letter-spacing: 0.2em; text-transform: uppercase; color: rgb(212, 175, 55); border-bottom: 2px solid rgb(212, 175, 55); padding-bottom: 6px;">Choose your tournament to get started </a>
      </div>
    </div>
  </section>
<!-- /wp:html -->

<?php
echo ymcatmtb_section_markup('Photo and introduction', ob_get_clean());
