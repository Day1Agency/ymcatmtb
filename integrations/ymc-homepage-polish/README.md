# Homepage presentation, October 28

Version 1.0.1 is deployed as a separate WordPress plugin on ymcatmtb.com.
It filters the front-page stadium content without editing the saved homepage or theme.

- One-line PLAY FOR YMC above the tunnel entrance.
- Three post-scroll action cards: register, watch, corporate sponsor.
- Removes opening CTA dock and redundant reveal tagline.
- Campaign presentation: $750,000 goal, combined progress bar, Donate now.

Depends on the existing Combined Campaign Progress plugin for live totals; preserves its data-dc-tpl hook. The deployed bar was verified at 30,999 / 750,000. PHP syntax parsed; public mobile hero/cards/campaign checked. No payment submitted.

Rollback: deactivate YMC Homepage Polish, then purge SiteGround cache from a normal admin GET page. Saved homepage content is unchanged. Preserve this separate plugin during theme deployments.

Registration is outside this plugin. User selected staff-created teams; Nikka has the implementation handoff. Public team creation remains enabled until the replacement captain request/setup flow is ready and tested.
