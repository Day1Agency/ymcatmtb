# Team placement requests

Installed and activated separately as a WordPress plugin on October 20, 2026. The theme deployment does not install plugins.

Published page `/need-a-team/` contains `[ymc_team_request]`. Homepage page 6 has placement links in the adult registration section and FAQ, plus approved GA boxed dinner and VIP buffet copy. Matching theme patterns are included here. Database content must be preserved during deployments.

Requests are saved as private Team Requests in WordPress. Two emails are attempted: a notification to the recipient set under Team Requests > Settings, which falls back to ykohen@yeshivasmekorchaim.org when blank, and a confirmation to the person who submitted. The Team Requests list shows a Sent/Failed column for each, plus the address the notification went to, so a silent failure is visible. Delivery to a real inbox is still unconfirmed. This is placement help, not registration or a reserved spot. Required-field validation checked in browser.

Rollback: remove the two homepage placement links, unpublish the placement page, and deactivate this plugin. Existing request records remain in the database.
