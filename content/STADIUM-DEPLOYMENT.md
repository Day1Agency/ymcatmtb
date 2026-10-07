# Stadium intro, October 7, 2026

Theme base: c835c3a. The previous hero pattern is retained in patterns/hero.php. The exact previous saved WordPress hero block is in rollback/hero-before-stadium.html.

The homepage is WordPress page 6. Theme deployment does not update its saved blocks. content/homepage-oct07.html is the updated saved page snapshot. Only the block named "Hero and scrolling banner" was replaced by "Stadium scroll intro". All other blocks and payment URLs remain identical.

## Fast rollback

1. Back up the CURRENT homepage code first so newer edits are preserved.
2. In WordPress page 6 Code editor, replace only the complete "Stadium scroll intro" block with rollback/hero-before-stadium.html. Save and purge SG Cache.
3. The intro CSS and JS are inert without that block, so no theme rollback is required to restore the old appearance. This also preserves sponsorship fixes and subsequent work.
4. If removing intro code is desired, revert the stadium intro PR via a new reviewed PR. Do not reset main or restore an old database.

Local full pre-deploy homepage and theme ZIP are in outputs/ymc-pre-stadium-2026-10-07 in Yomi's Codex workspace. A backup Git tag preserves the theme commit. WordPress revisions provide an additional recovery option.

## Implementation

Scoped CSS and JS, native scroll with requestAnimationFrame updates, reversible on scrolling back up, no forced scroll. Reduced-motion/no-JS fallback shows static introduction and tournament links. Compressed decorative generated tunnel artwork (not a documentary photo of the venue). Header and sticky stage account for the admin toolbar. Floating badge is hidden during the intro so it cannot cover mobile calls to action.

Before database sync, preserve the live homepage page 6 content. Pull main before changing theme files.
