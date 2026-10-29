=== iCap SEO ===
Contributors: ikirko
Tags: seo, schema, meta description, structured data, ai
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.1.143
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

SEO checks and one-click fixes that run on your own site, plus an optional cloud service for site-wide scans, link crawling, and AI drafts.

== Description ==

iCap SEO checks your pages for common SEO problems, explains what it finds in plain language, and fixes most of it for you, either with one click or with a preview you approve first.

**Runs on your site, no account needed**

* **34 on-page checks** for every post and page: title tag and length, meta description and length, H1, HTTPS, robots meta rules (noindex, nofollow, noimageindex, nosnippet), robots.txt, content depth, readability (reading ease, long sentences, transition words, passive voice), heading and paragraph structure, image alt text, width/height and lazy loading, canonical URL, structured data (JSON-LD presence, @type, and required properties), internal links, external references, and internal nofollow links. They read the page from your own site; nothing is sent anywhere.
* **One-click fixes** for those checks: titles, meta descriptions, H1, alt text, image dimensions, lazy loading, canonical URL, JSON-LD structured data, headings, paragraphs, and internal links. Checks that need a judgment call, such as readability or robots rules, explain what to change. They are applied directly on your site. On a registered site with AI credits, some of these fixes use AI-drafted text instead of the built-in templates.
* **Editor panel**: a quick-check score, search result preview, and social preview in the block editor (updated as you type) and in a classic editor meta box.
* **Social sharing tags, a redirect manager with a 404 log, an llms.txt file, Local SEO, and optional IndexNow pings**: Open Graph and X Card tags, old-URL-to-new-URL redirects, a log of pages visitors hit that don't exist, a generated `/llms.txt`, LocalBusiness structured data (address, phone, hours), and IndexNow notifications on publish, update, or delete (off by default).

**Optional iCap SEO service**

Registering the site with the iCap SEO service (free, no card required) adds site-wide scans and scores run from our servers. A Premium subscription adds 4 checks that need an outside view of your site: the security headers visitors actually receive (often set by a CDN or host, not by WordPress), and broken internal and external links, found by requesting every linked URL from our servers. The service also provides AI-assisted drafts (meta descriptions, titles, alt text, expanded content, readability rewrites, spelling and grammar), paid for with AI credits, and Google Search Console and Google Analytics reports.

Everything in the "Runs on your site" list above works the same whether or not the site is registered.

Built and maintained by [iCapSolutions](https://www.icapsolutions.com).

== Installation ==

1. Install the plugin from **Plugins → Add New**, or upload the ZIP under **Plugins → Add New → Upload Plugin**.
2. Activate it.
3. Open **iCap SEO → Content Scores** and pick a page to see its checks and apply fixes.
4. Optional: to use the iCap SEO service, open **iCap SEO → Setup Wizard**, enter your email to request a registration token, follow the link in the email, then click **Trigger Full Scan**.

== Frequently Asked Questions ==

= Do I need an account to use this plugin? =

No. The 34 on-page checks, their fixes, the editor panel, redirects, social tags, llms.txt, and Local SEO all work without an account. An account is only needed for the iCap SEO service: site-wide scans, Premium checks, AI-assisted drafts, and Google reports.

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

* Service host: api.icapsolutions.com (API only, no web page)
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

= 0.1.143 =
* Setup and settings messages now refer to the fixed iCap SEO service address instead of an editable API Base URL field, matching the read-only Connection settings.

= 0.1.142 =
* Password-protected posts keep their content private: no description is generated from their excerpt or content, generated structured data isn't printed on them, and they're left out of llms.txt.
* The 404 log records at most 30 new hits per minute, so a burst of bad URLs can't turn into a database write per request.

= 0.1.141 =
* HTTPS, robots meta, robots.txt, content depth, readability, structured data @type and required properties, external references, and internal nofollow checks now run on your own site, free. 34 of the 38 checks run on your site.
* Structured data fixes cover the @type and required-property checks too.
* Structured data output is validated and re-encoded before it is printed.

= 0.1.140 =
* Every free check now runs on your own site, including title, meta description, H1, content depth, and paragraph structure, and every one-click fix applies on your site with or without registering.
* Paragraph structure is now a free check.
* The editor panel's checks all run on your site.
* Pages that haven't been scanned show "Not scanned" instead of a score.
* The service address is fixed and shown in Settings instead of being an editable field.
* Uninstall also removes the IndexNow setting and cached analytics data.

= 0.1.1 =
* Initial release.
