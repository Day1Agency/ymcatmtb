# YMC Homepage Polish — October 28

Version 1.0.10 is deployed as a separate WordPress plugin on ymcatmtb.com. It filters front-page content without changing the saved homepage or theme.

- One-line PLAY FOR YMC above the tunnel beam; compact transparent menu wordmark.
- Post-scroll register/watch/sponsor cards and gold photo-section actions.
- $750,000 annual goal and live combined progress, provided by the existing Combined Campaign Progress plugin.
- Removes Get the Forms banner and footer Player Forms link.
- Replaces the Live Display iframe with responsive donor cards, refreshed every two minutes. Cards show the latest 12 public giving-space records from Annual Campaign 733202 among the most recent 100 account transactions. Public giving-space names, amounts, messages and dates only; no billing/contact fallback. Zero/hidden public amounts remain hidden, and anonymous names remain anonymous. API credential remains server-side in the existing captain-invitations configuration.

Validation: PHP and JavaScript syntax passed; live public donor cards matched the source feed, including an anonymous donation with hidden amount. Donor content uses textContent. Desktop rendering verified. No registration or payment submitted.

Registration cleanup was performed separately in Givebutter: archived Signed liability waiver and Signed media release ticket fields for teen campaign 749484. Agreement fields retained. Archived status confirmed after reopening the saved ticket.

Rollback: deactivate YMC Homepage Polish and purge SiteGround cache from a normal admin GET page. Saved homepage content remains unchanged. Preserve this plugin during theme deployments.

Menu asset: generated transparent cutout based on original logo artwork, with white lettering, thin gold edges and Bank Stadium ribbon; no background, outer shield or glow.
