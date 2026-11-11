# WordPress.org directory assets

Images shown on the plugin's WordPress.org directory listing page. These are **not** part of
the plugin itself - deliberately kept as a sibling to `wordpress-plugin/icap-seo/`, not inside it,
so the release build script never bundles them into the shipped zip.

This mirrors how WordPress.org's SVN repository is actually structured: `trunk/` (the plugin
code, from `wordpress-plugin/icap-seo/`) and `assets/` (screenshots, icon, banner) are siblings,
and only `assets/screenshot-N.<ext>` numbering needs to match the `== Screenshots ==` section of
`readme.txt`.

Banner and icon:

- `banner-772x250.png` and `banner-1544x500.png` - listing header (standard and high-DPI).
- `icon-128x128.png` and `icon-256x256.png` - plugin icon (standard and high-DPI).

Screenshots:

- `screenshot-1.png` - Overview: site-wide SEO score, Google connections and AI credits at a glance.
- `screenshot-2.png` - Content Scores: every page scored, with Search Console and Analytics numbers alongside.
- `screenshot-3.png` - Page detail: what to fix on each page, with one-click and preview-before-publish fixes.
- `screenshot-4.png` - SEO Performance: clicks, impressions, CTR and position from Google Search Console.
- `screenshot-5.png` - Site Analytics: users, sessions and traffic channels from Google Analytics.
- `screenshot-6.png` - Account: your plan, Google connection and billing in one place.
