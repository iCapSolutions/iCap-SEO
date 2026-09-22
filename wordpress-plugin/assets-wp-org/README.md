# WordPress.org directory assets

Screenshots shown on the plugin's WordPress.org directory listing page. These are **not** part of
the plugin itself - deliberately kept as a sibling to `wordpress-plugin/icap-seo/`, not inside it,
so the release build script never bundles them into the shipped zip.

This mirrors how WordPress.org's SVN repository is actually structured once the plugin is
approved: `trunk/` (the plugin code, from `wordpress-plugin/icap-seo/`) and `assets/` (screenshots,
icon, banner) are siblings, and only `assets/screenshot-N.<ext>` numbering needs to match the
`== Screenshots ==` section of `readme.txt`.

- `screenshot-1.png` - Setup Wizard: connect and register a site in a few steps.
- `screenshot-2.png` - Overview dashboard: full-site health score across all 6 categories.
- `screenshot-3.png` - Content Scores: per-page recommendations with one-click and
  preview-before-publish fixes.
