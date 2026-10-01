<?php
/**
 * Title: header
 * Slug: ymcatmtb/header
 * Categories: featured
 * Description: Matches the supplied YMC Event Registration reference.
 */
ob_start();
?>
<!-- wp:html -->
<header data-dc-tpl="12" data-ymc-section="0" style="position: sticky; top: 0px; z-index: 40; background: rgba(36, 16, 70, 0.86); backdrop-filter: blur(14px); border-bottom: 1px solid rgba(212, 175, 55, 0.3);">
    <div data-dc-tpl="13" class="ymc-ref-header-inner" style="max-width: 1440px; margin: 0px auto; padding: 0px clamp(16px, 3vw, 30px); height: 84px; display: flex; align-items: center; gap: 20px;">
      <a data-dc-tpl="14" href="#top" style="display: flex; align-items: center; flex-shrink: 0;">
        <img data-dc-tpl="15" src="<?php echo esc_url( get_theme_file_uri( 'assets/reference/d3ac31484267fd2f.png' ) ); ?>" alt="YMC at M&amp;T" class="ymc-ref-header-logo" style="height: 68px; width: auto; display: block;">
      </a>
      <div data-dc-tpl="16" style="display: flex; align-items: center; gap: 10px; margin-left: auto;">
        <a data-dc-tpl="17" href="#campaign" class="scp0" style="display: flex; align-items: center; min-height: 46px; padding: 0px 20px; border-radius: 4px; border: 1px solid rgb(212, 175, 55); color: rgb(212, 175, 55); font-weight: 700; font-size: 12px; letter-spacing: 0.16em; text-transform: uppercase; transition: all 200ms var(--ease-out);">Donate</a>
        <a data-dc-tpl="18" href="#tournaments" class="scp1" style="display: flex; align-items: center; gap: 10px; min-height: 46px; padding: 0px 8px 0px 20px; border-radius: 4px; background: rgb(212, 175, 55); color: rgb(36, 16, 70); font-weight: 700; font-size: 12px; letter-spacing: 0.16em; text-transform: uppercase; transition: all 200ms var(--ease-out);">
          Register
          <span data-dc-tpl="19" style="display: flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 3px; background: rgb(36, 16, 70); color: rgb(212, 175, 55);"><i data-dc-tpl="20" class="fas fa-arrow-right" style="font-size: 11px;"></i></span>
        </a>
      </div>
    </div>
  </header>
<!-- /wp:html -->

<?php
echo ymcatmtb_section_markup('Header', ob_get_clean());
