# Customer onboarding

This is the canonical description of the customer journey, from discovering iCap SEO to running scans on an
active, registered site. For step-by-step technical instructions, see [Getting started](getting-started.md).

## The journey

1. **Discover** — a prospective customer finds iCap SEO (via the plugin repo, iCapSolutions.com, or a referral)
   and downloads the plugin ZIP.
2. **Install** — the plugin is installed and activated in WordPress like any other plugin. No account or
   credentials are required to activate it.
3. **Request access** — from the plugin's **Setup Wizard**, the customer requests a registration token by
   entering an email and choosing a plan (see [Requesting a token](#requesting-a-registration-token) below).
4. **Connect & register** — the customer enters the registration token in **Settings**, then
   completes registration from **Setup Wizard**. Site credentials are provisioned automatically at this step —
   no manual key management.
5. **First scan** — the customer triggers a scan from **Overview** and reviews results in **Content Scores**.
6. **Grow into Premium** — the customer can start billing checkout at any time from **Settings** to add the
   Premium checks run by the scan service.

## What works before you register

Everything that runs on your own site works without an account:

- The 34 free checks on every page in **Content Scores**, with their one-click fixes.
- On-page output (meta description, canonical URL, JSON-LD schema, social sharing tags) generated from your
  existing content.
- The editor panel's quick checks and search and social previews.
- The redirect manager and 404 log, `/llms.txt`, Local SEO, and optional IndexNow pings (off by default).

## What requires registration

Features provided by the iCap SEO service need a completed registration (a valid site ID and site token):
site-wide scans and scores, Premium checks, AI-assisted drafts, Google Search Console and Analytics reports, and
billing. Until registration completes, these show a "site not configured" state, and nothing is sent to the
service.

## Requesting a registration token

Request one directly from the plugin: open **iCap SEO → Setup Wizard**, enter your email, choose a plan
(Baseline, Premium, or AI Scanning), and click **Request Registration**. Verify your email via the link sent
to you, and your registration token follows by email — instantly for Baseline, after a quick iCapSolutions
review for Premium and AI Scanning. Then follow [Getting started](getting-started.md) to connect and register
your site.

You can still reach out at [icapsolutions.com](https://www.icapsolutions.com/services/wordpress-seo-plugin.html)
or through your account contact if you'd rather request a token that way.

## Related docs

- [Getting started](getting-started.md) — detailed setup steps and troubleshooting.
- [SEO checks catalog](seo-checks-catalog.md) — what each scan checks for.
