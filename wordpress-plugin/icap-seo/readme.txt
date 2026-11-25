=== iCap SEO – SEO Audit & One-Click SEO Fixes, Schema & AI SEO ===
Contributors: icapsolutions, ikirko
Tags: seo, seo audit, schema, google search console, ai seo
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.1.160
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

SEO audit for every page with one-click fixes for titles, meta descriptions, schema, alt text and links. Free Search Console reports. Optional AI SEO.

== Description ==

iCap SEO checks your pages for common SEO problems, explains what it finds in plain language, and fixes most of it for you, either with one click or with a preview you approve first.

**Runs on your site, no account needed**

* **35 on-page checks** for every post and page: title tag and length, meta description and length, H1, HTTPS, robots meta rules (noindex, nofollow, noimageindex, nosnippet), robots.txt, security headers, content depth, readability (reading ease, long sentences, transition words, passive voice), heading and paragraph structure, image alt text, width/height and lazy loading, canonical URL, structured data (JSON-LD presence, @type, and required properties), internal links, external references, and internal nofollow links. They read the page from your own site; nothing is sent anywhere.
* **Check all pages**: run those checks across your published pages at once and see a site-wide score, with results and fixes for each page.
* **Link check**: find broken links on any page when you click Check links.
* **One-click fixes** for those checks: titles, meta descriptions, H1, alt text, image dimensions, lazy loading, canonical URL, JSON-LD structured data, headings, paragraphs, and internal links. Checks that need a judgment call, such as readability or robots rules, explain what to change. They are applied directly on your site. With AI Improvements, some of these fixes use AI-drafted text instead of the built-in templates.
* **Editor panel**: the page's Content Scores score with a Check now button, live checks as you type, a search result preview, and a social preview in the block editor, plus a classic editor meta box.
* **Social sharing tags, a redirect manager with a 404 log, an llms.txt file, Local SEO, and optional IndexNow pings**: Open Graph and X Card tags, old-URL-to-new-URL redirects, a log of pages visitors hit that don't exist, a generated `/llms.txt`, LocalBusiness structured data (address, phone, hours), and IndexNow notifications on publish, update, or delete (off by default).

**Free with registration**

Registering the site (free) connects it to the iCap SEO service, which runs on our servers and adds:

* **Google Search Console and Google Analytics reports**: search performance, traffic, and keywords, from your own Google account.

**Optional Premium service**

A Premium subscription adds, on top of that:

* **Scans from outside your site**: up to 100 pages per scan, requested from our servers.
* **3 checks that need an outside view of your site**: whether each page is reachable by a crawler, and broken links across your pages, requested from our servers, within a limit per scan.
* **AI Improvements**: AI-drafted titles, descriptions, headings, alt text, internal links, expanded content, rewrites, and grammar fixes. Premium is billed yearly or monthly; the yearly plan includes 25 AI credits a year, and anyone on Premium can buy more.

Everything in the "Runs on your site" list above works the same with or without Premium, and without registering.

== Installation ==

1. Install the plugin from **Plugins → Add New**, or upload the ZIP under **Plugins → Add New → Upload Plugin**.
2. Activate it.
3. Open **iCap SEO → Content Scores**, click **Check all pages**, then pick a page to see its results and apply fixes.
4. Optional: to add the free Google reports, open **iCap SEO → Account**, click **Get started**, enter your email to request a token, follow the link in the email, paste the token on the same page, and click **Connect this site**. Then connect Google and, for Premium, upgrade on the same tab.

== Frequently Asked Questions ==

= Do I need an account to use this plugin? =

No. The 35 on-page checks, their fixes, the editor panel, redirects, social tags, llms.txt, and Local SEO all work without an account. An account (free) is only needed for the Google reports and for Premium: AI Improvements, and outside scans and checks.

= Will this plugin change my content without asking? =

No. Nothing changes until you click Apply. Larger content changes (expanded content, reworded paragraphs) are shown as a draft you approve before anything is written to your site.

= What is sent to the iCap SEO service? =

Nothing, until you start registration on the Account tab. Registering sends basic site details, scans read your public pages, and AI drafts send the relevant page content. See "External services" below for details.

= How are AI credits charged? =

Each AI action uses credits based on the AI work it actually takes, so short drafts like a meta description cost a small fraction of a credit and longer rewrites cost more. Your balance can show decimals, the Overview tab shows about how many actions it still covers, and the AI Improvements tab lists the usual cost of each action. If an AI draft fails, no credits are used.

= How do I report a bug, request a feature, or report a security issue? =

Email support@icapsolutions.com. Report security vulnerabilities to security@icapsolutions.com, not publicly.

== External services ==

The plugin works without contacting any external service. It connects to the services below only after you start registration or test the connection on the Account tab, connect a Google account, start a checkout, request an AI draft, click Check links, or turn on IndexNow pings.

= iCap SEO service (iCapSolutions) =

Used for site registration, subscription status, the free Google reports, and the Premium features: scans and checks from outside your site, AI Improvements, and AI credit balance.

* Service host: api.icapsolutions.com (API only, no web page)
* Registration data: the site URL and name, the site's admin email address, the WordPress and plugin versions, and the site's time zone, plus the email address you enter when requesting a token.
* Requests after registration: the site ID and access token issued at registration and the plugin version, with the post IDs or page keys involved. Scans fetch your public pages from our servers. Fix previews send the issue codes you select.
* AI draft requests: the page's title and text, the site name, the post type, the target length, the titles of related posts offered as links, and the page's image URLs.
* Billing and Google connections: the address of your WordPress dashboard, so you can be sent back to it. Starting a checkout also sends the plan you chose (yearly or monthly).
* When: when you click Get started, request a token, register, test the connection, run a scan, preview a fix, request an AI draft, or check billing status. Once the site is registered, opening the plugin's pages also refreshes billing status and loads saved results. Nothing is sent until you take one of these actions.
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

Optional, free for registered sites. Connected from the Account tab with your own Google account, using read-only access. Your browser is sent to Google to approve access, and the data is fetched by the iCap SEO service and shown in your WordPress dashboard.

* Data used: Search Console search performance and indexing data for properties you have verified, and Google Analytics (GA4) traffic data for the property you choose. Your Google account name and email are used to show which account is connected.
* When: when you connect the account, and when the Overview, SEO Performance, Site Analytics, Keywords, or Content Scores tabs request this data.
* Revoke at any time from the plugin's Account tab or from your Google Account's connected apps page.
* Google privacy policy: https://policies.google.com/privacy
* Google terms of service: https://policies.google.com/terms
* Google API Services User Data Policy: https://developers.google.com/terms/api-services-user-data-policy

= IndexNow =

Optional, off by default. Only if you tick "IndexNow pings" under Advanced on the Account tab does the plugin send a post or page's URL and a site-specific verification key to the IndexNow endpoint when you publish, update, or delete it. IndexNow shares the URL with participating search engines (including Bing and Yandex). Untick the setting to turn it off.

* Endpoint: https://api.indexnow.org/indexnow
* Data sent: the URL of the changed page and the site's IndexNow key. No personal data is sent.
* About IndexNow: https://www.indexnow.org/
* Privacy: https://privacy.microsoft.com/privacystatement and https://yandex.com/legal/privacy/

== Screenshots ==

1. Overview — site-wide SEO score, Google connections and AI credits at a glance.
2. Content Scores — every page scored, with Search Console and Analytics numbers alongside.
3. Page detail — what to fix on each page, with one-click and preview-before-publish fixes.
4. SEO Performance — clicks, impressions, CTR and position from Google Search Console.
5. Site Analytics — users, sessions and traffic channels from Google Analytics.
6. Account — your plan, Google connection and billing in one place.

== Changelog ==

= 0.1.160 =
* A page's Google indexing status now has its own panel on the page's Content Scores screen. It explains what Google's status means and links to the fixes that help, such as expanding the content with AI, adding internal links or adding a redirect, then to the page in Search Console so you can ask Google to index it again.
* The Google status on the Content Scores list now opens that panel.
* The "ask Google to index it" link now opens the page straight in Search Console's URL Inspection, using the link Google provides during scans. Before the next scan it opens the site's Pages report instead.
* Once a page has had a Readability rewrite and its sentences pass, the rewrite is no longer suggested again for the remaining reading-ease score, which comes from long words and names. You can still run another one.
* After you preview an AI improvement or apply a fix, the page now returns to that section instead of the top, with the result message there.
* After you accept and publish an AI improvement, or discard it, you're taken back to the top of the page's details, with a "What changed on the page" summary and a link to view the published page.
* The buttons for each AI improvement, and for fixing everything at once, now sit inside their own box.
* Content depth expansion now reads the whole page before writing, so it adds new material instead of repeating what's further down the page.
* Once content has been expanded since Google's last visit, the indexing panel marks that step done instead of suggesting it again.
* A page's details screen now starts with one row of cards: the page (with a link to view it), its type, status and overall score.
* The Status card shows the page's WordPress status (Published, Private, Draft) on every page, with "Checked on this site" underneath when the on-site checks produced the score.
* Every page's details screen now has the same layout: the Search Console and Google Analytics cards and the Google indexing panel always show, and say why when there's no data yet.
* When a page's sentences already pass but its reading-ease score is still low, the Readability rewrite explains that word choice is what's left.
* Fixed: a Readability rewrite, Spelling & grammar or image alt text draft could put an AI answer on the wrong paragraph or image when the AI left blank lines in its reply. Drafts are now only built when every answer lines up with its paragraph or image, and each rewrite is checked against its original.

= 0.1.159 =
* The iCap SEO panel on the edit screen now shows the page's Content Scores score, with a Check now button to re-check the page.
* The panel's live checks use the same rules and limits as Content Scores, including the site name in the title length, so the two no longer disagree. Headline suggestions are shown as tips and aren't scored.
* When a page has no featured image, social sites are now offered the first image in the page, then the site logo, then the site icon, skipping images smaller than 200 × 200 pixels, which Facebook ignores. The social preview shows that picture and says where it came from.
* On a page's AI Improvements tab, only the improvements the scan suggested are highlighted, so the highlighted cards match the count on the tab. Spelling & grammar stays available on every page.
* The Overview tab shows the Overall SEO Score out of 100, like the rest of the plugin, instead of as a percentage.

= 0.1.158 =
* The page link check now lists links it couldn't check separately from broken ones, with the reason (for example, the other site refused an automated check or your server couldn't verify its security certificate), so a connection problem doesn't look like a broken link.
* AI Improvements are easier to find: Content Scores has an Improve with AI link on every page, the Fixes tab says which fixes AI can write, and the AI Improvements tab shows every AI action as a card with what it does, its usual cost, and whether it applies to the page.
* After two weeks of use, the iCap SEO page asks once whether you'd leave a review on WordPress.org. Choose Maybe later to be asked again in 30 days, or Don't ask again to hide it for good.
* Clearer messages when registration isn't available for a site, and when a suspended account blocks any request, not just scans.

= 0.1.157 =
* AI credit balances can now show decimals, for AI actions priced by the work they take.
* The Overview tab shows about how many AI actions your balance still covers.
* The AI Improvements tab lists the usual credit cost of each AI action.

= 0.1.156 =
* llms.txt and the IndexNow key file now return a normal success status, so crawlers and IndexNow accept them.
* Google report tabs reuse results from the last 30 minutes instead of reloading on every visit.
* The structured data fix now adds a description to page schema when the page has no meta description, so the "required properties" check can be fixed in one click.
* After applying fixes, only the checks a fix actually changed are marked as applied; checks with no on-site fix stay listed.
* Premium can now be billed monthly as well as yearly; the Account tab offers both.
* Plan changes, cancellations, and AI credits show as soon as you return from the billing portal.
* Clearer messages when a fix has nothing to change, shown as information rather than an error.
* Fix notes now point to the AI Improvements tab by its current name.

= 0.1.155 =
* Every tab now shows its content in the same framed tiles.

= 0.1.154 =
* Overview tiles that aren't connected now have a button to connect them, and the page is easier to scan.
* Softer button styling throughout.
