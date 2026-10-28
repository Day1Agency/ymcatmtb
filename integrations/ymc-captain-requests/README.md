# YMC Captain Team Requests

Captains ask for a team here instead of creating one in Givebutter. The colour
teams already exist in Givebutter, so in practice the captain's link goes out
within seconds of them submitting.

Page: `/register-a-team/`, holding the `[ymc_captain_request]` shortcode.
Styling and the three-step wizard come from the theme
(`assets/css/team-request.css`, `assets/js/captain-steps.js`), shared with the
Need a Team page.

## The flow

1. Captain picks an available colour and submits.
2. The colour is held immediately. Staff get a notification, the captain gets a
   receipt confirming the hold.
3. The matching Givebutter team is looked up and the captain is emailed their
   team link, telling them to register as a player under it.
4. If YMC later renames the team to a sponsor, the captain is emailed again with
   the new name so they can tell their players what to look for.

The receipt and the team link are two separate emails on purpose. When a colour
has no team yet the receipt still goes out at once and the link follows later.

## Colour exclusivity

Exclusivity used to come from eleven Givebutter "Captain Only" tickets selling
out. With team requests moving here, the hold happens at request time instead.

A colour is claimed through `add_option()`, a single INSERT against a unique
index, so two captains submitting in the same instant cannot both win it. A
check-then-write would hand both of them Blue.

Black is held for Chaim Finkelstein, whose ticket is
`Adult Tournament — Black (Captain Only)` and whose team is named after him
rather than the colour.

## Matching, and why colour beats name

Colour is the only identifier that is unique by construction, so a colour in the
team name settles who the team belongs to: "Blue", "Team Blue" and "Blue
Thunder" are all Blue's captain. Whole words only, or "Bluebird" would match. A
name carrying two colours identifies nobody and is left for staff.

Only when the team name has no colour at all does it fall back to the captain's
name, loosely: the whole name or their first name as whole words, so "Nikka
Cutie Team" reaches Nikka. First names under three letters never match alone.
Where that is ambiguous - two pending captains called Nikka, or one captain
matching two teams - nothing is sent and staff send it by hand, because a wrong
match emails a stranger someone else's team link.

## Renames and deletions

Once the link has gone out the request is tracked by Givebutter **team id**,
which is the only thing a rename does not change.

- Renamed: the captain gets one update naming the new team. Running again does
  not re-send.
- Deleted: the colour is released and the request says so. Judged on the team id
  for sent requests, so a team renamed to a sponsor keeps its hold.
- An empty team list is treated as a failed read, never as "everything deleted".

Trashing or deleting the request in wp-admin releases its colour too, so a test
entry can simply be deleted. Restoring takes the colour back only if it is still
free.

## Campaign id, not campaign code

Givebutter's campaign sub-resources answer only to the numeric id:
`campaigns/729740/teams` works where `campaigns/RMSQHT/teams` returns 404. The
code is still correct for identifying the campaign, which is what
`inc/captain-invitations.php` uses it for.

## Notifications

Goes to the address in *Captain Requests → Settings*, falling back to
`ykohen@yeshivasmekorchaim.org`. Every send is recorded against the request, so
a silent delivery failure shows in the list rather than going unnoticed.

The team-link email points at the registration page, not the team page. Team
URLs carry a generated slug (`teal-3`, `blue-27`) that changes on rename, which
would break links already sent.
