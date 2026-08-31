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
4. **Connect & register** — the customer enters the API base URL and registration token in **Settings**, then
   completes registration from **Setup Wizard**. Site credentials are provisioned automatically at this step —
   no manual key management.
5. **First scan** — the customer triggers a scan from **Overview** and reviews results in **Content Scores**.
6. **Grow into premium** — the customer can start billing checkout at any time from **Settings** to unlock the
   full check catalog.

## What works before you register

Activating the plugin without registering gives you a limited, local-only preview:

- Basic on-page output (meta description, canonical URL, JSON-LD schema) generated from your existing content.
- Placeholder content scores in **Content Scores**, so you can see the UI before connecting a real account.

## What requires registration

Everything that reflects your actual site — real content scoring, remediation suggestions, AI-assisted content
drafts, scan triggers, and billing status — requires a completed registration (a valid site ID and site token).
Until registration completes, these features return a "site not configured" state rather than real data.

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
