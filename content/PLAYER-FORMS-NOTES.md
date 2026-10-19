# Player Forms design

Local page 31 (`player-forms`) remains a draft. Its body uses editable native
WordPress blocks from `content/player-forms.html`. Styling is loaded in both the
frontend and block editor; the slug-specific template adds the event header.

To sync after reviewing this branch, update only the destination Player Forms
page content from that HTML file and preserve the `player-forms` slug. Confirm
the three existing PDF files exist on the destination site. Do not restore a
database or overwrite homepage page 6. Git deployment alone does not update the
page content or upload the PDFs.

The original signature instructions, PDF targets and contact address were
preserved. This design does not introduce or approve legal terms. Verified the
draft at 1440px and 390px with no horizontal overflow; all PDF links returned 200.
