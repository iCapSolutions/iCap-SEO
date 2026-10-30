# Customer onboarding

This is the canonical description of the customer journey, from discovering iCap SEO to running scans on an
active, registered site. For step-by-step technical instructions, see [Getting started](getting-started.md).

## The journey

1. **Discover** — a prospective customer finds iCap SEO (via the plugin repo, iCapSolutions.com, or a referral)
   and downloads the plugin ZIP.
2. **Install** — the plugin is installed and activated in WordPress like any other plugin. No account or
   credentials are required to activate it.
3. **Request access** — from the plugin's **Setup Wizard**, the customer requests a registration token by
   entering an email (see [Requesting a token](#requesting-a-registration-token) below).
4. **Connect & register** — the customer enters the registration token in **Settings**, then
   completes registration from **Setup Wizard**. Site credentials are provisioned automatically at this step —
   no manual key management.
5. **Connect Google** — any registered site can connect Google Search Console and Google Analytics in
   **Settings**, free, to fill the report tabs.
6. **Upgrade to Premium** — the customer starts billing checkout from **Settings → Billing**.
7. **First scan** — with Premium active, the customer triggers a scan from **Overview** and reviews results
   in **Content Scores** and the report tabs.

## What works before you register

Everything that runs on your own site works without an account:

- The 35 free checks across your site with **Check all pages** in **Content Scores**, with a site-wide score
  and one-click fixes.
- On-page output (meta description, canonical URL, JSON-LD schema, social sharing tags) generated from your
  existing content.
- The editor panel's quick checks and search and social previews.
- The redirect manager and 404 log, `/llms.txt`, Local SEO, and optional IndexNow pings (off by default).

## What registration adds, free

Registering the site (free) connects it to the iCap SEO service and adds the Google Search Console and
Google Analytics reports, connected with your own Google account. Nothing is sent to the service before you
register.

## What requires Premium

The rest of the service-side features need a registered site with an active Premium subscription:
scans run from outside your site, the 3 checks that need that outside view, and AI Improvements, with 25 AI credits included each year. Until the site
has Premium, these show an upgrade panel.

## Requesting a registration token

Request one directly from the plugin: open **iCap SEO → Setup Wizard**, click **Continue to registration**, enter your email,
and click **Request Registration**. Verify your email via the link sent to you, and your registration token
follows by email right away. Then follow [Getting started](getting-started.md) to connect and register
your site.

You can still reach out at [icapsolutions.com](https://www.icapsolutions.com/services/wordpress-seo-plugin.html)
or through your account contact if you'd rather request a token that way.

## Related docs

- [Getting started](getting-started.md) — detailed setup steps and troubleshooting.
- [SEO checks catalog](seo-checks-catalog.md) — what each scan checks for.
