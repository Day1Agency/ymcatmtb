# Shared footer maintenance

The footer source is `patterns/footer.php`. All page templates reference the
`footer` template part, whose content references `ymcatmtb/footer`. Do not paste
an expanded footer into the homepage or template part: it becomes an independent
copy and will no longer follow source changes.

After importing an older live database, inspect the saved Footer template part.
If it contains an expanded “Footer and floating badge” block, run:

```sh
wp eval-file wp-content/themes/ymcatmtb/tools/repair-shared-footer.php
```

This idempotent repair preserves the original content in the non-autoloaded
`ymc_footer_before_shared_reference_<post ID>` option and replaces only the
Footer template part's content with the pattern reference. It does not modify
headers, pages, posts, uploads, or any other template part. It refuses unknown
customizations. Review any intended footer edits before running on another site.

The theme invalidates its WordPress pattern cache when the footer source hash
changes. Hosting/full-page caches must still be purged when deploying.

A Git push does not repair an existing database override. Run the repair on the
destination separately after reviewing/backing up its current Footer content.
