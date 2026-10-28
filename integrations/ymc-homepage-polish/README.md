# Homepage presentation, October 28

Version 1.0.4 is deployed as a separate WordPress plugin on ymcatmtb.com.
It filters the front-page stadium content without editing the saved homepage or theme.

- One-line PLAY FOR YMC above the tunnel entrance.
- Three post-scroll action cards: register, watch, Sponsor the event.
- Removes opening CTA dock and redundant reveal tagline.
- Campaign presentation: $750,000 goal, combined progress bar, Donate now.

Depends on the existing Combined Campaign Progress plugin for live totals; preserves its data-dc-tpl hook. The deployed bar was verified at 30,999 / 750,000. PHP syntax parsed; public mobile hero/cards/campaign checked. No payment submitted.

Rollback: deactivate YMC Homepage Polish, then purge SiteGround cache from a normal admin GET page. Saved homepage content is unchanged. Preserve this separate plugin during theme deployments.

Registration is outside this plugin. User selected staff-created teams; Nikka has the implementation handoff. Public team creation remains enabled until the replacement captain request/setup flow is ready and tested.

Mobile refinement: larger headline, description, timer and scroll cue, with stacked countdown labels to avoid overflow. Header uses a compact HTML text wordmark instead of the shield. Verified public 390px mobile view and post-scroll cards.

Latest revision restores the live raised total, uses the user-supplied exact logo crop, and replaces repeated photo-section copy with More than a game and solid gold actions. Public donor feed is not embedded; Givebutter standard widgets do not embed the full campaign page/feed.

## October 28 follow-up (1.0.6)
- Lift headline above stadium beam on desktop and mobile; reduce actual-artwork menu logo to 56px / 52px.
- Add official Givebutter Live Display for main annual campaign on desktop. This display respects public/anonymous settings and has its own main-campaign total, distinct from the combined total above.
- Under 1024px use a donor-page link because Live Display enforces a 900x600 minimum. A native mobile donor list remains a separate integration.
- Verified live desktop and 390x844 mobile. Roll back by deactivating this plugin and purging SG cache.
