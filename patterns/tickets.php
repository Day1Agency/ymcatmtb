<?php
/**
 * Title: tickets
 * Slug: ymcatmtb/tickets
 * Categories: featured
 * Description: Seats for people coming to watch rather than play.
 */

/*
 * Prices come from the school's attendee pricing sheet. The VIP rows carry
 * no description of the experience itself: what it includes is not settled,
 * and nothing about it is to be advertised until the school says so.
 */
$ymcatmtb_tickets_url = 'https://givebutter.com/ymc-at-mtb-tickets';

ob_start();
?>
<!-- wp:html -->
<section data-dc-tpl="300" id="tickets" data-ymc-section="13" style="position: relative; background: rgb(13, 13, 13); overflow: hidden;">
    <div data-dc-tpl="301" style="position: absolute; inset: 0px; background-image: repeating-linear-gradient(90deg, rgba(255, 255, 255, 0.04) 0px, rgba(255, 255, 255, 0.04) 1px, transparent 1px, transparent 80px); pointer-events: none;"></div>
    <div data-dc-tpl="302" style="position: relative; max-width: 1240px; margin: 0px auto; padding: clamp(72px, 9vw, 120px) clamp(20px, 4vw, 40px);">
      <div data-dc-tpl="303" style="max-width: 620px;">
        <div data-dc-tpl="304" style="display: flex; align-items: center; gap: 14px;">
          <span data-dc-tpl="305" style="width: 36px; height: 2px; background: rgb(212, 175, 55);"></span>
          <span data-dc-tpl="306" style="font-weight: 600; font-size: 12px; letter-spacing: 0.22em; text-transform: uppercase; color: rgb(212, 175, 55);">Come watch</span>
        </div>
        <h2 data-dc-tpl="307" style="margin: 14px 0px 0px; font-family: var(--font-display); font-weight: 400; font-size: clamp(52px, 7vw, 88px); line-height: 0.88; text-transform: uppercase; color: rgb(255, 255, 255);">In the stands<br data-dc-tpl="308"><span data-dc-tpl="309" style="color: rgb(212, 175, 55);">on game day</span></h2>
        <p data-dc-tpl="310" style="margin: 22px 0px 0px; font-size: 16px; line-height: 1.65; color: rgba(255, 255, 255, 0.84);">Not playing? Come and watch from the stands at M&amp;T Bank Stadium. A full meal is included with every seat.</p>
      </div>

      <div data-dc-tpl="311" style="margin-top: 48px; display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 340px), 1fr)); gap: 20px;">

        <div data-dc-tpl="312" style="display: flex; flex-direction: column; padding: 32px 30px 30px; border-radius: 8px; border: 1px solid rgba(212, 175, 55, 0.45); background: rgb(36, 16, 70);">
          <h3 data-dc-tpl="313" style="margin: 0px; font-family: var(--font-display); font-weight: 400; font-size: 40px; line-height: 0.95; text-transform: uppercase; color: rgb(255, 255, 255);">General<br data-dc-tpl="314">Admission</h3>
          <p data-dc-tpl="315" style="margin: 14px 0px 0px; font-weight: 600; font-size: 11px; letter-spacing: 0.18em; text-transform: uppercase; color: rgb(212, 175, 55);">Meal included &middot; 300 seats</p>
          <ul data-dc-tpl="316" style="margin: 24px 0px 0px; padding: 0px; list-style: none; display: grid; gap: 1px; background: rgba(255, 255, 255, 0.12); border-radius: 4px; overflow: hidden; flex: 1 1 auto;">
            <li data-dc-tpl="317" style="display: flex; align-items: baseline; justify-content: space-between; gap: 14px; padding: 15px 18px; background: rgb(13, 13, 13);"><span data-dc-tpl="318" style="font-size: 15px; color: rgba(255, 255, 255, 0.84);">One seat</span><span data-dc-tpl="319" style="font-family: var(--font-condensed); font-weight: 700; font-size: 30px; line-height: 1; font-variant-numeric: tabular-nums; color: rgb(212, 175, 55);">$54</span></li>
            <li data-dc-tpl="320" style="display: flex; align-items: baseline; justify-content: space-between; gap: 14px; padding: 15px 18px; background: rgb(13, 13, 13);"><span data-dc-tpl="321" style="font-size: 15px; color: rgba(255, 255, 255, 0.84);">Two seats</span><span data-dc-tpl="322" style="font-family: var(--font-condensed); font-weight: 700; font-size: 30px; line-height: 1; font-variant-numeric: tabular-nums; color: rgb(212, 175, 55);">$100</span></li>
            <li data-dc-tpl="323" style="display: flex; align-items: baseline; justify-content: space-between; gap: 14px; padding: 15px 18px; background: rgb(13, 13, 13);"><span data-dc-tpl="324" style="font-size: 15px; color: rgba(255, 255, 255, 0.84);">Four seats</span><span data-dc-tpl="325" style="font-family: var(--font-condensed); font-weight: 700; font-size: 30px; line-height: 1; font-variant-numeric: tabular-nums; color: rgb(212, 175, 55);">$180</span></li>
            <li data-dc-tpl="326" style="display: flex; align-items: baseline; justify-content: space-between; gap: 14px; padding: 15px 18px; background: rgb(13, 13, 13);"><span data-dc-tpl="327" style="font-size: 15px; color: rgba(255, 255, 255, 0.84);">Six seats</span><span data-dc-tpl="328" style="font-family: var(--font-condensed); font-weight: 700; font-size: 30px; line-height: 1; font-variant-numeric: tabular-nums; color: rgb(212, 175, 55);">$250</span></li>
          </ul>
          <a data-dc-tpl="329" href="<?php echo esc_url( $ymcatmtb_tickets_url ); ?>" target="_blank" rel="noopener" class="scpc" style="margin-top: 24px; display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 17px 22px; border-radius: 4px; background: rgb(212, 175, 55); color: rgb(36, 16, 70); font-weight: 700; font-size: 12px; letter-spacing: 0.16em; text-transform: uppercase; transition: all 200ms var(--ease-out);">Get General Admission <i data-dc-tpl="330" class="fas fa-arrow-right" style="font-size: 12px;"></i></a>
        </div>

        <div data-dc-tpl="331" style="display: flex; flex-direction: column; padding: 32px 30px 30px; border-radius: 8px; border: 1px solid rgba(212, 175, 55, 0.45); background: rgb(13, 13, 13);">
          <h3 data-dc-tpl="332" style="margin: 0px; font-family: var(--font-display); font-weight: 400; font-size: 40px; line-height: 0.95; text-transform: uppercase; color: rgb(255, 255, 255);">VIP<br data-dc-tpl="333"><span data-dc-tpl="334" style="color: transparent; -webkit-text-stroke: 1.5px rgb(212, 175, 55);">Experience</span></h3>
          <p data-dc-tpl="335" style="margin: 14px 0px 0px; font-weight: 600; font-size: 11px; letter-spacing: 0.18em; text-transform: uppercase; color: rgb(212, 175, 55);">Meal included &middot; 30 places</p>
          <ul data-dc-tpl="336" style="margin: 24px 0px 0px; padding: 0px; list-style: none; display: grid; gap: 1px; background: rgba(255, 255, 255, 0.12); border-radius: 4px; overflow: hidden; flex: 1 1 auto;">
            <li data-dc-tpl="337" style="display: flex; align-items: baseline; justify-content: space-between; gap: 14px; padding: 15px 18px; background: rgb(36, 16, 70);"><span data-dc-tpl="338" style="font-size: 15px; color: rgba(255, 255, 255, 0.84);">One place</span><span data-dc-tpl="339" style="font-family: var(--font-condensed); font-weight: 700; font-size: 30px; line-height: 1; font-variant-numeric: tabular-nums; color: rgb(212, 175, 55);">$1,000</span></li>
            <li data-dc-tpl="340" style="display: flex; align-items: baseline; justify-content: space-between; gap: 14px; padding: 15px 18px; background: rgb(36, 16, 70);"><span data-dc-tpl="341" style="font-size: 15px; color: rgba(255, 255, 255, 0.84);">Two places</span><span data-dc-tpl="342" style="font-family: var(--font-condensed); font-weight: 700; font-size: 30px; line-height: 1; font-variant-numeric: tabular-nums; color: rgb(212, 175, 55);">$1,800</span></li>
          </ul>
          <a data-dc-tpl="343" href="<?php echo esc_url( $ymcatmtb_tickets_url ); ?>" target="_blank" rel="noopener" class="scpd" style="margin-top: 24px; display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 22px; border-radius: 4px; border: 1px solid rgba(212, 175, 55, 0.5); background: rgb(36, 16, 70); color: rgb(255, 255, 255); font-weight: 700; font-size: 12px; letter-spacing: 0.16em; text-transform: uppercase; transition: all 200ms var(--ease-out);">Get VIP <i data-dc-tpl="344" class="fas fa-arrow-right" style="font-size: 12px;"></i></a>
        </div>

      </div>
    </div>
  </section>
<!-- /wp:html -->

<?php
echo ymcatmtb_section_markup( 'Spectator tickets', ob_get_clean() );
