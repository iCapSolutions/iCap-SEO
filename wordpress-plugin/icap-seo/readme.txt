=== iCap SEO ===
Contributors: ikirko
Tags: seo, schema, meta description, structured data, ai
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.1.140
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

SEO checks and one-click fixes that run on your own site, plus an optional cloud scan service for deeper crawl, readability, and AI-assisted drafts.

== Description ==

iCap SEO checks your pages for common SEO problems, explains what it finds in plain language, and fixes most of it for you, either with one click or with a preview you approve first.

**Runs on your site, no account needed**

* **16 on-page checks** for every post and page: title tag and length, meta description and length, H1, content depth, heading and paragraph structure, image alt text, width/height and lazy loading, canonical URL, JSON-LD presence, and internal links. They read the page from your own site; nothing is sent anywhere.
* **One-click fixes** for those checks: titles, meta descriptions, H1, alt text, image dimensions, lazy loading, canonical URL, JSON-LD, headings, paragraphs, and internal links. They are applied directly on your site. On a registered site with AI credits, some of these fixes use AI-drafted text instead of the built-in templates.
* **Editor panel**: a quick-check score, search result preview, and social preview in the block editor (updated as you type) and in a classic editor meta box.
* **Social sharing tags, a redirect manager with a 404 log, an llms.txt file, Local SEO, and optional IndexNow pings**: Open Graph and X Card tags, old-URL-to-new-URL redirects, a log of pages visitors hit that don't exist, a generated `/llms.txt`, LocalBusiness structured data (address, phone, hours), and IndexNow notifications on publish, update, or delete (off by default).

**Optional iCap SEO service**

Registering the site with the iCap SEO service (free, no card required) adds site-wide scans and scores run from our servers. A Premium subscription adds 22 more checks that the service runs by crawling your pages from outside your site the way a search engine does: HTTPS and security headers, robots.txt and robots meta rules, readability analysis, structured data validation, and broken or external link checks. The service also provides AI-assisted drafts (meta descriptions, titles, alt text, expanded content, readability rewrites, spelling and grammar), paid for with AI credits, and Google Search Console and Google Analytics reports.

Everything in the "Runs on your site" list above works the same whether or not the site is registered.

Built and maintained by [iCapSolutions](https://www.icapsolutions.com).

== Installation ==

1. Install the plugin from **Plugins → Add New**, or upload the ZIP under **Plugins → Add New → Upload Plugin**.
2. Activate it.
3. Open **iCap SEO → Content Scores** and pick a page to see its checks and apply fixes.
4. Optional: to use the iCap SEO service, open **iCap SEO → Setup Wizard**, enter your email to request a registration token, follow the link in the email, then click **Trigger Full Scan**.

== Frequently Asked Questions ==

= Do I need an account to use this plugin? =

No. The 16 on-page checks, their fixes, the editor panel, redirects, social tags, llms.txt, and Local SEO all work without an account. An account is only needed for the iCap SEO service: site-wide scans, Premium checks, AI-assisted drafts, and Google reports.

= Will this plugin change my content without asking? =

No. Nothing changes until you click Apply. Larger content changes (expanded content, reworded paragraphs) are shown as a draft you approve before anything is written to your site.

= What is sent to the iCap SEO service? =

Nothing, until you register the site. After that, scans read your public pages, and AI drafts send the relevant page content. See "External services" below for details.

= Where can I see the full list of checks? =

Open any page under **Content Scores**. The table lists every check, whether it runs on your site or in the service, and its current status.

= How do I report a bug, request a feature, or report a security issue? =

General bugs, feature requests, and support questions: email support@icapsolutions.com. Security
vulnerabilities should go to security@icapsolutions.com instead of a public report — see
https://www.icapsolutions.com/.well-known/security.txt for our disclosure policy.

== External services ==

The plugin works without contacting any external service. It connects to the services below only after you register the site, connect a Google account, start a checkout, request an AI draft, or turn on IndexNow pings.

= iCap SEO service (iCapSolutions) =

Used for site registration, site-wide scans and scores, Premium checks, AI-assisted drafts, AI credit balance, and subscription status.

* Address: https://api.icapsolutions.com
* Data sent: the site URL, the site ID and access token issued at registration, and the email address you enter when requesting registration. Scans fetch your public pages from our servers. AI draft requests send the title and text of the page being drafted.
* When: when you request registration or register, run or refresh a scan, open a page's service results, request an AI draft, or check billing status. Nothing is sent before you register.
* Privacy policy: https://www.icapsolutions.com/about/privacy-policy.html
* Terms of service: https://www.icapsolutions.com/about/terms-of-service.html

= Anthropic (AI drafts) =

AI-assisted drafts are generated by Anthropic's Claude models. The iCap SEO service sends the page content needed for the draft to Anthropic; your site does not contact Anthropic directly.

* When: only when you request an AI draft.
* Privacy policy: https://www.anthropic.com/legal/privacy
* Terms: https://www.anthropic.com/legal/commercial-terms

= Stripe (payments) =

Subscriptions and AI credit purchases are handled by Stripe Checkout and the Stripe billing portal. When you click a checkout or billing button, the iCap SEO service creates a Stripe session and your browser is sent to Stripe. Card details go to Stripe only.

* When: only when you start a checkout or open the billing portal.
* Privacy policy: https://stripe.com/privacy
* Terms: https://stripe.com/legal/ssa

= Google Search Console and Google Analytics =

Optional. Connected from Settings with your own Google account, using read-only access. Your browser is sent to Google to approve access, and the data is fetched by the iCap SEO service and shown in your WordPress dashboard.

* Data used: Search Console search performance and indexing data for properties you have verified, and Google Analytics (GA4) traffic data for the property you choose. Your Google account name and email are used to show which account is connected.
* When: when you connect the account, and when the Overview, SEO Performance, Site Analytics, Keywords, or Content Scores tabs request this data.
* Revoke at any time from the plugin's Settings tab or from your Google Account's connected apps page.
* Google privacy policy: https://policies.google.com/privacy
* Google terms of service: https://policies.google.com/terms
* Google API Services User Data Policy: https://developers.google.com/terms/api-services-user-data-policy

= IndexNow =

Optional, off by default. Only if you tick "IndexNow pings" in Settings does the plugin send a post or page's URL and a site-specific verification key to the IndexNow endpoint when you publish, update, or delete it. IndexNow shares the URL with participating search engines (including Bing and Yandex). Untick the setting to turn it off.

* Endpoint: https://api.indexnow.org/indexnow
* Data sent: the URL of the changed page and the site's IndexNow key. No personal data is sent.
* About IndexNow: https://www.indexnow.org/
* Bing privacy statement: https://privacy.microsoft.com/privacystatement
* Yandex privacy policy: https://yandex.com/legal/privacy/

== Screenshots ==

1. Setup Wizard — connect and register a site in a few steps.
2. Overview dashboard — full-site health score across all 6 categories.
3. Content Scores — per-page recommendations with one-click and preview-before-publish fixes.

== Changelog ==

= 0.1.140 =
* Every free check now runs on your own site, including title, meta description, H1, content depth, and paragraph structure, and every one-click fix applies on your site with or without registering.
* Paragraph structure is now a free check.
* The editor panel's checks all run on your site.
* Pages that haven't been scanned show "Not scanned" instead of a score.
* The service address is fixed and shown in Settings instead of being an editable field.
* Uninstall also removes the IndexNow setting and cached analytics data.

= 0.1.139 =
* Readme: the check count is 38, matching the plugin description.

= 0.1.138 =
* Internal fix: Plugin Check warning on the local page check.

= 0.1.137 =
* Free checks now show for sites that are not registered. They run on your own site without any connection.

= 0.1.136 =
* Structured data type and required-property checks are premium and no longer fixed on your own site; the free JSON-LD presence check is unchanged.

= 0.1.135 =
* The overview now lists which checks are free and which are premium.

= 0.1.134 =
* Canonical URL, JSON-LD presence, and basic internal-link checks now run on your own site, so they are part of the free tier. Page results are cached for 10 minutes; nothing is sent to our servers for these checks.

= 0.1.133 =
* Image alt text, width/height, lazy loading, and heading structure checks now run on your own site, so they are part of the free tier. Results are cached for 10 minutes; nothing is sent to our servers for these checks.
* Checks that couldn't be run on the page now show as "Not yet scanned" instead of "Passing".

= 0.1.132 =
* IndexNow pings are now off by default. Turn them on in Settings if you want them.
* Declared the minimum PHP version (7.4) in the plugin header.
* Moved the inline admin styles and the editor script into enqueued asset files, loaded only on the screens that use them.
* Hardened the JSON-LD output so stored values cannot close the script tag.

= 0.1.131 =
* Plugin header: the Plugin URI now points to the iCap SEO product page, so it differs from the Author URI.

= 0.1.130 =
* Added an External services section to the readme and an IndexNow pings option in Settings (on by default; untick to stop pings). Existing behavior is unchanged.

= 0.1.128 =
* Maintenance: internal link suggestions now exclude the current page without an exclusion query parameter, and read-only admin display state and nonce-protected form fields are documented in code. No visible behavior change.

= 0.1.127 =
* Maintenance: removed a debug log call from the dashboard fallback path, switched redirect handling to wp_safe_redirect() with the configured target host explicitly allowed, and tightened escaping and input sanitization on a few admin screens. No visible behavior change.

= 0.1.126 =
* Rolled WordPress's native sortable-column arrows (used on Keywords) out to every other data table in the plugin: Content Scores, the SEO Performance and Site Analytics Top Pages tables, Active Redirects, and Recently Seen 404s are now all sortable by clicking a column header, using the same native WP markup throughout.

= 0.1.125 =
* Keywords tab: replaced the generic stats row with keyword-specific tiles (Total Keywords, Top 3 Rankings, Top 10 Rankings, Total Clicks, Avg Position), and made every column in the Top Keywords table sortable using WordPress's native sortable-column arrows.

= 0.1.124 =
* Added a new Keywords tab: total keywords, total clicks, total impressions, average CTR, and average position tiles, plus a table of your highest-clicking search queries from Google Search Console over the last 28 days.

= 0.1.123 =
* Replaced WordPress's default admin footer text ("Thank you for creating with WordPress.") with "Thank you for using iCap SEO." (linking to icapsolutions.com) on iCap SEO's own admin page only - every other wp-admin screen is unaffected.

= 0.1.122 =
* Settings: added a "Change property" link next to the connected Search Console / Google Analytics property, so a wrong auto-detected property can be corrected without disconnecting - previously, once any property was saved, the picker never showed again even if it was the wrong one.

= 0.1.121 =
* Site Analytics tab: added a Users tile, a traffic-by-channel breakdown (Organic Search, Direct, Referral, etc.), and a Top Pages table of your highest-traffic pages from Google Analytics.
* SEO Performance tab: added a Top Pages table of your highest-clicking pages from Search Console, under the trend chart.

= 0.1.120 =
* Split the SEO Performance tab in two: it now holds only Search Console stats (clicks, impressions, CTR, position), and a new "Site Analytics" tab (right after it) holds the Google Analytics stats (sessions, page views, engagement rate, session duration) - the first step toward expanding each with its own per-page breakdowns.

= 0.1.119 =
* Fixed the SEO Performance trend chart's card bleeding past the right edge of the page - it was missing `box-sizing: border-box`, so its 16px padding and border were added on top of an already-100%-width box instead of being included in it.

= 0.1.118 =
* SEO Performance tab: trimmed the trend chart's height, added a min/max/current stats panel next to it for each metric, and added a hover crosshair that shows all 4 metrics' values for any day at once.

= 0.1.117 =
* SEO Performance tab: combined the 4 separate trend charts into a single chart with all 4 metrics overlaid (each shown relative to its own 28-day high/low, since clicks/impressions/CTR/position don't share a value scale).

= 0.1.116 =
* Fixed Search Console showing zero clicks/impressions (Content Scores and the new SEO Performance tab) when the site's real property in Search Console doesn't exactly match its registered URL (a domain property, or a www/non-www or http/https mismatch) - Settings now discovers and lets you pick the right property when this happens.

= 0.1.115 =
* Added a new "SEO Performance" tab (after Content Scores): site-wide Search Console totals (clicks, impressions, CTR, average position) with a 28-day trend chart per metric, plus Google Analytics totals (sessions, page views, engagement rate, average session duration).

= 0.1.114 =
* Cleaned up the Content Detail page (opened from Content Scores): removed the grey code-box styling from Type/Status/Score and other fields, restyled the page name to match the Overview tab's status typography, added per-page Search Console/Analytics/score icon cards matching the Overview tab's style (score card colored with the same green/yellow/red palette used site-wide), and reorganized the page's Recommendations/AI Drafts/History sections into white card tiles matching Overview/Content Scores/Setup Wizard.

= 0.1.113 =
* Fixed "Re-register Site" showing a generic, misleading error on an already-connected site. Registration tokens are single-use, so re-registering with the same saved token (the common case) now gets a specific explanation and points at requesting a new one; the "request a registration token" form is also now available even after a site is connected, not just before.

= 0.1.112 =
* Setup Wizard now shows Google Search Console and Google Analytics connection status alongside Site ID, with a link to Settings when either isn't connected yet.

= 0.1.111 =
* Moved the Setup Wizard tab's Register/Test Connection buttons below the white card tile instead of inside it, matching the Overview tab's layout.

= 0.1.110 =
* Wrapped the Setup Wizard tab's content in a white card tile, matching the look already used on the Overview and Content Scores tabs.

= 0.1.109 =
* Setup Wizard tab's Site ID now displays as plain bold text, matching the Latest scan / Tier style used on the Content Scores tab, instead of the grey code-box look.

= 0.1.108 =
* Fixed the "yellow" score band from 0.1.107 reading as brownish/orange: score colors now use a true light yellow (and matching lighter green/red) for rings, bars, and swatches, paired with a separate, slightly deeper same-hue variant for score numbers/text so they stay readable on white.

= 0.1.107 =
* Lightened the green/yellow/red score-band palette introduced in 0.1.106 and unified it across every place a page or site score is colored: the Overview and Content Scores tabs, the WordPress Dashboard Home widget, and the block/classic editor quick-check score panels (previously the editor panels used a separate, unrelated color set).

= 0.1.106 =
* Unified score colors across the Overview and Content Scores tabs into a single green/yellow/red scheme matching the Good (80-100) / Needs attention (50-79) / Poor (below 50) bands, replacing the previous continuous red-to-green gradient.

= 0.1.105 =
* Switched the default API Base URL to api.icapsolutions.com (previously a raw CloudFront domain). Existing connected sites are unaffected — the old endpoint keeps working — this only changes the default new installs see.

= 0.1.104 =
* Added a lower-cost 25-credit AI pack alongside the original 100-credit pack, so you can try AI-assisted fixes without a large upfront purchase.

= 0.1.103 =
* Fixed the "AI credits remaining" count on the Overview tab not updating right away after using a credit.

= 0.1.102 =
* Fixed AI Drafts actions (preview, publish, discard, regenerate) sometimes returning you to the wrong tab in Content Detail.

= 0.1.101 =
* Added a live structured data (schema) check to the in-editor panel, and a new "Check spelling & grammar" action in AI Drafts.

= 0.1.100 =
* Added a passive-voice check to the readability toolset, bringing the total to 38 checks.

= 0.1.99 =
* Added two more readability checks: long sentences and low use of transition words.

= 0.1.98 =
* Fixed a few places in the dashboard still showing an outdated check count.

= 0.1.97 =
* Added checks for robots meta tags and internal nofollow links, expanding the total from 31 to 35 checks.

= 0.1.96 =
* The iCap SEO block editor sidebar now updates live as you type (title, content, meta description): the quick-check score, checklist, and search/social previews refresh about a second after you pause typing, instead of only reflecting the last-saved version of the page. The Classic Editor metabox is unaffected — it stays computed on save, matching how the rest of that screen works.

= 0.1.95 =
* Added a "Headline quality" quick check to the iCap SEO editor panel (block editor sidebar and Classic Editor metabox): flags word-count balance, suggests a power word ("free", "best", "guide", etc.) or a specific number when the title is missing both.

= 0.1.94 =
* Added an iCap SEO widget to the WordPress Dashboard Home screen: overall SEO score, pages scanned, and a callout for any pages scoring below 50, with a link straight to Content Scores.

= 0.1.93 =
* Added a Classic Editor fallback for the "iCap SEO" panel (v0.1.92): the same search preview, social preview, and quick-check score now also appear as a metabox on posts and pages edited in the classic editor, not just the block editor.

= 0.1.92 =
* Added a Phase 1 "iCap SEO" panel to the block editor sidebar: a search-result (SERP) preview, a social share card preview, and a read-only quick-check score for the current page — all computed from the page's saved content, no new scan required to see them.

= 0.1.91 =
* Added a score distribution donut chart to the top of the Content Scores tab, showing the average/median score and a Good/Needs attention/Poor breakdown across all scanned pages at a glance.

= 0.1.90 =
* Added a Notifications tab consolidating Google connection issues, billing problems, and unresolved 404s into one dismissible list, instead of scattered one-time banners.

= 0.1.89 =
* Added a Site Health card to the Overview tab: flags plain (non-SEO-friendly) permalinks and RSS feeds set to publish full post content, both linking directly to the relevant WordPress settings screen to fix.

= 0.1.88 =
* Reorganized the Overview tab's summary tiles into a clearer two-row layout and added small icons to the tab bar and each tab's heading for a more consistent, easier-to-scan admin UI.

= 0.1.87 =
* Added Local SEO: a new tab to enter your business name, address, phone, price range, and weekly hours, which outputs LocalBusiness structured data site-wide once filled in.

= 0.1.86 =
* Fixed `/llms.txt` output being HTML-escaped instead of served as plain text, which broke its own ">" blockquote marker and could double-encode "&"/quotes in real page titles and descriptions.

= 0.1.85 =
* Added a 404 log to the Redirects tab, showing pages visitors and search engines hit that don't exist, with a one-click "Create redirect" action for each.
* Added an automatically-generated `/llms.txt` file listing your site's published pages, for AI assistants and crawlers that support the emerging llms.txt convention.

= 0.1.84 =
* Added social sharing preview tags (Open Graph, X Cards) so links to your pages show a proper title, description, and image when shared.
* Added a redirect manager (iCap SEO → Redirects) for sending an old URL to a new one with a 301 or 302.
* Added automatic IndexNow pings to Bing and Yandex on publish, update, and delete, so search engines pick up changes faster.

= 0.1.83 =
* Fixed the sidebar menu icon overflowing into the menu items below it - WordPress renders that icon as a raw, unscaled image, so it now uses a dedicated 20x20 file instead of the larger dashboard-header image.

= 0.1.82 =
* Replaced the generic search-icon mark (sidebar and dashboard header) with the real iCapSolutions logo mark, so the plugin visually matches the parent brand.

= 0.1.81 =
* Fixed the dashboard header logo rendering squished/illegible - it was using the full wordmark graphic in a square-cropped image slot instead of the icon mark.

= 0.1.80 =
* Fixed a duplicate canonical URL tag when SEOPress is active alongside iCap SEO.
* Added a plain-language notice to Setup Wizard and Settings explaining that running a scan sends the page's content to the iCap SEO service.
* Hardened several admin redirects and fixed a JSON-LD output edge case; added cleanup of plugin settings on uninstall.

= 0.1.79 =
* Raised the Analytics property lookup client timeout to reduce false failures on slower Google API responses.

= 0.1.78 =
* Show a real, specific error when Google Analytics property lookup fails instead of a generic message.

= 0.1.77 =
* Added Google Analytics (GA4) traffic data alongside Search Console in the dashboard.

= 0.1.76 =
* Surfaced real Search Console performance stats in the plugin dashboard.

= 0.1.75 =
* Distinguish revoked Google connections from healthy ones; surface Search Console status more broadly.

= 0.1.74 =
* Raised the scan-trigger client timeout to accommodate Google verification latency.

= 0.1.73 =
* Added Google Search Console connection from Settings, with a real indexing status badge.

= 0.1.70 =
* Fixed content depth drafting not reliably reaching the 300/600-word thresholds.

= 0.1.69 =
* Fixed corrupted JSON-LD schema and readability draft data in post meta.

= 0.1.68 =
* Fixed the heading fix requiring multiple clicks to apply on thin single-paragraph content.

= 0.1.67 =
* Added a warning when a site's Posts page can't verify content-body recommendations.

= 0.1.66 =
* Stopped offering "Apply this recommendation" for guidance-only checks that have no automated fix.

= 0.1.65 =
* Fixed the "Applied — pending rescan" badge getting permanently stuck after a fix was applied.

= 0.1.1 =
* Initial release: full-site and per-page scoring, one-click fixes, preview-before-publish, Setup Wizard.

== Upgrade Notice ==

= 0.1.80 =
Fixes a duplicate canonical tag with SEOPress and adds a data-use notice to the dashboard. No action required.

= 0.1.79 =
Improves reliability of Google Analytics data loading. No action required.
