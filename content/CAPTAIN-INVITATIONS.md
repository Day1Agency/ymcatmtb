# Automatic captain registration emails

Live read-only verification on October 15: team detail records contain no creator
ID or email. The user subsequently confirmed that a completed ticket labeled
`(Captain Only)` identifies a captain. The integration now recognizes that suffix,
including the test ticket, while independently checking the adult campaign and
matching the ticket email to the fundraiser/team. Keep delivery off until tested.
The API returns single resources as bare objects and collections under `data`;
the client and tests now account for both shapes.

This branch adds Settings → Captain Invitations. Nothing is sent by default and
no public homepage links or page 6 content are changed. No live database import is needed.

## Flow

1. Givebutter sends a signed `ticket.created` webhook after a ticket purchase.
2. The server queues only the configured adult `(Captain Only)` ticket titles.
3. A background job retrieves the ticket and transaction through the authenticated
   public API, verifies campaign RMSQHT and successful payment, then finds a single
   fundraiser with the ticket holder's email and a URL belonging to a team.
4. It prepares an HTML email with the team name and the direct Givebutter
   registration URL, plus instructions to forward it to players.
5. When delivery is enabled, it emails the matched registered captain. Players
   choose Join a team, select the named team, and buy Adult Tournament — Player.
   No team code or website lookup is generated or included. The earlier invitation
   page remains available for local design review only and is not part of this flow.
   Duplicate notifications do not produce duplicate invitation emails; a team-level
   lock also serializes different ticket events for the same team.

The team URL/email relationship was verified against the authenticated API for
Sample Team Registration (team 146378, ticket 47016767A0004). A captain
ticket identifies the intended captain; the API does not expose a team creator role.
The integration does not assume the payer or first team member is the captain.
Missing or ambiguous data waits/retries, then stops for review instead of guessing.

## What this does not solve

Givebutter does not document a registration URL that preselects a team. Its public
API exposes read/delete operations for members and teams, not membership assignment.
The email therefore states the exact team to select in Givebutter.
Automatic invitation delivery is separate from automatic membership assignment.
This implementation automates invitation email delivery, not team selection or
locked rosters. It does not replace registration review.

Capacity and registration cutoff must be configured in Givebutter. Direct links
do not enforce website restrictions. Keep the Givebutter limit at 10 including
the captain, and verify it during registration testing.

## Setup and acceptance checks

- Enter API key privately in WordPress Settings → Captain Invitations; never commit it.
- Keep automatic email disabled for the first test. Credentials are stored as
  non-autoloaded WordPress options and are never rendered back into the settings form.
- Deploy the feature only after review. Givebutter cannot deliver to localhost.
- Create a Givebutter webhook for `ticket.created`, pointing to the URL on the settings
  page. Copy its signing secret into WordPress. The documented `Signature` header
  must match that secret. Both configuration steps are required.
- Arrange reliable server cron to run WordPress scheduled events at least every minute
  and a working authenticated email transport. WP-Cron alone depends on site visits.
- Test a real authorized captain registration and confirm the correct ticket, team,
  registered email and unique member match. Preview status should become `ready`.
- Enable delivery only after that check. Existing ready invitations will be scheduled.
- Verify the actual email inbox, direct registration link, duplicate event handling,
  unrelated campaigns, failed/refunded transactions, and missing/deleted teams.
- A completed ticket created before webhook activation is not automatically imported.
  Use Givebutter webhook replay for testing if available; do not bulk-mail old captains.
- Emails are one per team. A second captain ticket for another member is held for review.
- Mail transport acceptance is not inbox delivery. On a process crash during delivery,
  a persistent lock/sending status intentionally prevents an automatic duplicate;
  inspect the mail provider log before any manual recovery.
- The API/queue retries missing data every five minutes up to 24 attempts. Investigate
  `review` statuses instead of continuing forever or assigning the wrong team.

## Local verification

Run `wp eval-file tests/captain-invitations.php` with the theme active on localhost.
The fixture mocks every HTTP request and email, checks webhook authentication,
matching, rejection paths, preview and enabled delivery, and replay idempotency.
It removes its own test options/jobs and restores previous configuration afterward.

Twenty automated checks passed after simplifying the flow. A separate local-only
SMTP test accepted the HTML email at 127.0.0.1:10019, addressed to an .invalid test
recipient. This is a Local mail catcher test, not proof of external inbox delivery.
No real captain email has been sent. API key and webhook signing secret must be
configured separately on the deployed WordPress site; local options are not deployed
by GitHub. Never restore the entire local database to enable this feature.

## Sources

- https://help.givebutter.com/en/articles/8828428-how-to-automate-workflows-and-data-using-webhooks
- https://docs.givebutter.com/api-reference/campaign-teams/list-all-campaign-teams
- https://givebutter.com/docs/api.json
