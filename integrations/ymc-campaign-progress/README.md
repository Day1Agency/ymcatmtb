# Combined YMC campaign progress

Deployed October 28, 2026 as a separate WordPress plugin (1.0.2). The existing theme/page content is unchanged. Source IDs: Annual 733202; Adult 729740; Teen 749484; GA/VIP 725223. Legacy sponsorship/test campaign 727094 is excluded.

The API key stays server-side in the existing `ymc_captain_invitations` option. The Givebutter API account identifier is opaque, unlike its numeric dashboard account ID. Each response is checked for the exact campaign, verified API account, USD currency and nonnegative numeric amount. Only a complete four-campaign result replaces the last cached result. Totals use integer cents for addition. No transactions are copied or moved.

Settings > Campaign Progress controls refresh and display. WP Cron refreshes every five minutes when site traffic runs scheduled tasks. The public read-only endpoint `/wp-json/ymc/v1/campaign-progress` only returns cached aggregate figures. Visitors cannot trigger external API calls. The homepage display shows its last-update time; failed loads retain the original goal display. No donor, ticket, contact or payment details are returned.

## Validation
- PHP parsed using php-parser; JavaScript passes node --check.
- Installed/activated and refreshed on WordPress successfully.
- Verified live API figures against dashboard: $30,819 + $180 + $0 + $0 = $30,999.
- Verified logged-in Chrome and logged-out in-app browser homepage render the combined total and breakdown.
- Original registration/payment URLs preserved.

## Rollback
Uncheck the homepage display in Settings > Campaign Progress, save, and purge SG Cache. Or deactivate the plugin and purge cache. The original $750,000 goal panel is still in the saved page. Deactivation unschedules refresh; it does not remove stored data.

## Limits
This combines totals on ymcatmtb.com only; individual Givebutter campaign pages retain their own totals. Support is reviewing native umbrella behavior. If transactions are migrated between campaigns later, reconcile source amounts before continuing aggregation. Campaign membership/goal changes require updating the explicit configuration. No supporter counts are summed because the same person can appear across campaigns.
