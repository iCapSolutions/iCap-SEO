=== iCap SEO ===
Contributors: ikirko
Tags: seo, schema, meta description, structured data, ai
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.1.150
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Site-wide SEO checks and one-click fixes that run on your site, free Google reports, and an optional service for AI improvements and outside scans.

== Description ==

iCap SEO checks your pages for common SEO problems, explains what it finds in plain language, and fixes most of it for you, either with one click or with a preview you approve first.

**Runs on your site, no account needed**

* **35 on-page checks** for every post and page: title tag and length, meta description and length, H1, HTTPS, robots meta rules (noindex, nofollow, noimageindex, nosnippet), robots.txt, security headers, content depth, readability (reading ease, long sentences, transition words, passive voice), heading and paragraph structure, image alt text, width/height and lazy loading, canonical URL, structured data (JSON-LD presence, @type, and required properties), internal links, external references, and internal nofollow links. They read the page from your own site; nothing is sent anywhere.
* **Check all pages**: run those checks across your published pages at once and see a site-wide score, with results and fixes for each page.
* **Link check**: find broken links on any page when you click Check links.
* **One-click fixes** for those checks: titles, meta descriptions, H1, alt text, image dimensions, lazy loading, canonical URL, JSON-LD structured data, headings, paragraphs, and internal links. Checks that need a judgment call, such as readability or robots rules, explain what to change. They are applied directly on your site. With AI Improvements, some of these fixes use AI-drafted text instead of the built-in templates.
* **Editor panel**: a quick-check score, search result preview, and social preview in the block editor (updated as you type) and in a classic editor meta box.
* **Social sharing tags, a redirect manager with a 404 log, an llms.txt file, Local SEO, and optional IndexNow pings**: Open Graph and X Card tags, old-URL-to-new-URL redirects, a log of pages visitors hit that don't exist, a generated `/llms.txt`, LocalBusiness structured data (address, phone, hours), and IndexNow notifications on publish, update, or delete (off by default).

**Free with registration**

Registering the site (free) connects it to the iCap SEO service, which runs on our servers and adds:

* **Google Search Console and Google Analytics reports**: search performance, traffic, and keywords, from your own Google account.

**Optional Premium service**

A Premium subscription adds, on top of that:

* **Scans from outside your site**: up to 100 pages per scan, requested from our servers.
* **3 checks that need an outside view of your site**: whether each page is reachable by a crawler, and broken links across your pages, requested from our servers, within a limit per scan.
* **AI Improvements**: AI-drafted titles, descriptions, headings, alt text, internal links, expanded content, rewrites, and grammar fixes. 25 AI credits are included each Premium year, and you can buy more.

Everything in the "Runs on your site" list above works the same with or without Premium, and without registering.

== Installation ==

1. Install the plugin from **Plugins → Add New**, or upload the ZIP under **Plugins → Add New → Upload Plugin**.
2. Activate it.
3. Open **iCap SEO → Content Scores**, click **Check all pages**, then pick a page to see its results and apply fixes.
4. Optional: to add the free Google reports, open **iCap SEO → Setup Wizard**, click **Continue to registration**, enter your email to request a registration token, follow the link in the email to register the site, then connect Google under **Settings**. For Premium, start checkout under **Settings → Billing**.

== Frequently Asked Questions ==

= Do I need an account to use this plugin? =

No. The 35 on-page checks, their fixes, the editor panel, redirects, social tags, llms.txt, and Local SEO all work without an account. An account (free) is only needed for the Google reports and for Premium: AI Improvements, and outside scans and checks.

= Will this plugin change my content without asking? =

No. Nothing changes until you click Apply. Larger content changes (expanded content, reworded paragraphs) are shown as a draft you approve before anything is written to your site.

= What is sent to the iCap SEO service? =

Nothing, until you start registration in the Setup Wizard. Registering sends basic site details, scans read your public pages, and AI drafts send the relevant page content. See "External services" below for details.

= How do I report a bug, request a feature, or report a security issue? =

Email support@icapsolutions.com. Report security vulnerabilities to security@icapsolutions.com, not publicly.

== External services ==

The plugin works without contacting any external service. It connects to the services below only after you start registration or test the connection in the Setup Wizard, connect a Google account, start a checkout, request an AI draft, click Check links, or turn on IndexNow pings.

= iCap SEO service (iCapSolutions) =

Used for site registration, subscription status, the free Google reports, and the Premium features: scans and checks from outside your site, AI Improvements, and AI credit balance.

* Service host: api.icapsolutions.com (API only, no web page)
* Registration data: the site URL and name, the site's admin email address, the WordPress and plugin versions, and the site's time zone, plus the email address you enter when requesting a token.
* Requests after registration: the site ID and access token issued at registration and the plugin version, with the post IDs or page keys involved. Scans fetch your public pages from our servers. Fix previews send the issue codes you select.
* AI draft requests: the page's title and text, the site name, the post type, the target length, the titles of related posts offered as links, and the page's image URLs.
* Billing and Google connections: the address of your WordPress dashboard, so you can be sent back to it.
* When: when you click Continue to registration, request a token, register, test the connection, run a scan, preview a fix, request an AI draft, or check billing status. Once the site is registered, opening the plugin's pages also refreshes billing status and loads saved results. Nothing is sent until you take one of these actions.
* Privacy policy: https://www.icapsolutions.com/about/privacy-policy.html
* Terms of service: https://www.icapsolutions.com/about/terms-of-service.html

= Anthropic (AI Improvements) =

AI Improvements are generated by Anthropic's Claude models. The iCap SEO service sends the page content needed for the draft to Anthropic; your site does not contact Anthropic directly.

* When: only when you request an AI draft.
* Privacy policy: https://www.anthropic.com/legal/privacy
* Terms: https://www.anthropic.com/legal/commercial-terms

= Link check =

When you click Check links on a page, your server requests up to 25 URLs that page links to. Those websites receive a normal web request from your server. Nothing else is sent, and nothing goes to us.

= Stripe (payments) =

Subscriptions and AI credit purchases are handled by Stripe Checkout and the Stripe billing portal. When you click a checkout or billing button, the iCap SEO service creates a Stripe session and your browser is sent to Stripe. The service gives Stripe the site's ID and, to pre-fill checkout, the admin email address from registration. Card details go to Stripe only.

* When: only when you start a checkout or open the billing portal.
* Privacy policy: https://stripe.com/privacy
* Terms: https://stripe.com/legal/ssa

= Google Search Console and Google Analytics =

Optional, free for registered sites. Connected from Settings with your own Google account, using read-only access. Your browser is sent to Google to approve access, and the data is fetched by the iCap SEO service and shown in your WordPress dashboard.

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
* Privacy: https://privacy.microsoft.com/privacystatement and https://yandex.com/legal/privacy/

== Screenshots ==

1. Setup Wizard — connect and register a site in a few steps.
2. Overview dashboard — full-site health score across all 6 categories.
3. Content Scores — per-page recommendations with one-click and preview-before-publish fixes.

== Changelog ==

= 0.1.150 =
* Check all pages works on Premium sites too, scoring pages a scan didn't reach.
