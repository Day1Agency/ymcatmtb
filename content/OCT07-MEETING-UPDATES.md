# October 7 meeting follow-up

Source: Tournament website review and updates, October 7, 2026, 3:01–3:26 PM Eastern. Meeting note mtg_note_400e474cf5f4819194cbd05e24e3cfd1.

## Homepage changes

- Remove 300 general-admission / 30 VIP inventory labels and meal claims from the ticket cards and introductory paragraph.
- Annual campaign has one Donate now CTA, retaining the existing donate destination.
- Intro counts calendar days until March 28, 2027 in America/New_York. It does not imply a kickoff time. Hide after event day; safe hidden fallback without JavaScript.
- Keep six visible sponsorship cards and approved benefits unchanged. Participants corrected their initial concern about benefits during the call.
- No emoji or decorative arrows. Keep approved stadium intro and current school logo size.

WordPress page 6 is a separate database edit: content/homepage-oct07.html is the updated serialized page. Apply only the above scoped edits if live content changes concurrently. Never restore the whole database to deploy this work. Exact pre-edit backup is in the working task's outputs/ymc-pre-stadium-2026-10-07/homepage-before-meeting-updates.html. Earlier stadium rollback remains in content/rollback/hero-before-stadium.html.

## Registration implementation / verification needed

- Adults must create a team as captain or join their captain's team. No solo adult registration. Verify new teams appear in the join dropdown. Existing homepage adult links still point directly to native Givebutter registration, so website-only gating cannot be described as enforcing restrictions against direct Givebutter access.
- Captains select from approximately 12 colors, first come first served. Chosen colors unavailable to other teams. Implement persistent, atomic reservations and release behavior for abandoned/failed registration; final palette has not been supplied.
- Collect jersey size and preferred number. Duplicate jersey numbers are allowed. Do not require unique numbers or backup choices.
- Waiver and liability-release text awaits the lawyer. Do not publish invented legal terms.
- Rebbeim and administrators need fundraiser pages without the player fee. Confirm roster and Givebutter setup before creation. Show them on the event site.
- Ticket and VIP purchases referred through staff pages should credit that staff fundraiser. Ticket purchases must not count toward player fundraising commitments. Verify actual attribution through checkout/reporting before claiming this works.
- Remove unconfirmed event times. Read-only check October 7 found 9 AM EDT still displayed on adult Givebutter campaign and native registration, contradicting its time-TBA description.
- Complete end-to-end registration tests, including captain creation, joining, payment success/failure, webhook reconciliation, jersey fields, capacity and color conflicts, before inviting the school's child-registration test.

## Decisions still open

- Admission proposal: $45 early bird, $54 afterward, $72 game day. Call explicitly said deadlines are not yet known. March 27 was discussed approximately; do not encode a cutoff until confirmed. Existing 2/4/6 bundles were not resolved. Leave public and checkout prices consistent in the meantime.
- Staff fundraiser roster and final 12-color palette were not named.
- Teen fundraising milestones, withdrawal and loss-of-spot/refund rules require final decisions and acknowledgment wording. '$500 by January 15' was an example, not approved policy. Existing $100 registration toward $850 commitment remains.
- Malky Rosenblum to review hospitality/event copy. Halftime sponsorship/dedication details require clarification; transcript does not establish a publishable name or offer.
