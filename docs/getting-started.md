# Getting started with iCap SEO

This guide walks through installing iCap SEO, using the free checks, and optionally connecting the site to the
iCap SEO service for Premium.

## Prerequisites

- WordPress admin access with plugin install privileges.
- Outbound HTTPS access from your WordPress server (standard on nearly all hosts).

## 1. Install the plugin

Download the latest release ZIP from [Releases](https://github.com/iCapSolutions/iCap-SEO/releases), then in
your WordPress admin go to **Plugins → Add New → Upload Plugin** and activate **iCap SEO**.

## Use the free checks (no account needed)

Open **iCap SEO → Content Scores** and pick a page. The 35 free checks run on your own site, and **Apply this
recommendation** (or **Apply all open recommendations**) fixes them there. Nothing is sent to any service.
**Redirects**, **Local SEO**, social sharing tags, `/llms.txt`, and the editor panel also work without an
account. The rest of this guide is only needed for the iCap SEO service.

## 2. Request a registration token

Open **iCap SEO → Setup Wizard**. If your site isn't connected yet, you'll see a **"Don't have a registration
token yet?"** section:

1. Enter your email address.
2. Choose a plan: **Baseline (free)**, **Premium**, or **AI Scanning**.
3. Click **Request Registration**.

You'll get a verification email — click the link to confirm it's you. What happens next depends on the plan:

- **Baseline** verifies and completes instantly. Your registration token arrives by email right away.
- **Premium** and **AI Scanning** requests go to iCapSolutions for a quick review after verification. You'll
  get a second email with your registration token once it's approved.

Already have a registration token from your account contact? Skip ahead to step 3. Note that each registration
token is single-use and tied to the specific site it was issued for — a token from a previous request, or one
already used to connect a different site, won't work.

## 3. Connect your site

1. Open **iCap SEO → Settings**.
2. Enter your **Registration Token** (from the email in step 2), either in **Settings** or as a
   `wp-config.php` constant:
   ```php
   define('ICAP_SEO_REGISTRATION_TOKEN', 'your-registration-token');
   ```
3. Save settings.

## 4. Verify the connection

Back in **iCap SEO → Setup Wizard**, click **Test Connection**. This confirms the plugin can reach the API
before you register.

## 5. Register your site

In **Setup Wizard**, click **Request Credentials & Register Site**. This provisions your site's connection
credentials automatically — no manual key management needed.

## 6. Activate Premium

From **iCap SEO → Settings → Billing**, click **Start Billing Checkout** to subscribe. When you return,
the plugin refreshes your billing status; **Check Billing Status** confirms it any time.

## 7. Connect Google and run your first scan

Connect Google Search Console and Google Analytics in **Settings** to fill the **SEO Performance**,
**Site Analytics**, and **Keywords** tabs — free on any registered site. With Premium active, click
**Trigger Full Scan** in **Overview**. Scans score every page and include the 3 checks run from
outside your site (page reachability, and broken internal and external links). Scan status updates in place.

## 8. Review your results

- **Overview** shows your site-wide score, scan summary, and what's included in your plan.
- **Content Scores** shows a per-page checklist — what's passing, what needs attention, and one-click fixes.

Without Premium, the free checks still run on any page you open in **Content Scores**, and a few more tabs
work immediately without registration: **Redirects** (set up
redirects and see a log of 404s visitors hit) and **Local SEO** (enter your business info once to output
LocalBusiness structured data site-wide). Social sharing previews and `/llms.txt` are on automatically, no
setup needed. IndexNow pings are off until you turn them on in **Settings**.

## Troubleshooting

| Symptom | Fix |
|---|---|
| No verification or token email arrives | Check spam/junk. Confirm the email address you entered is correct. Premium/AI Scanning requests wait on a review step before the token email sends. |
| Registration fails | Double-check the registration token. Each token works once, for one site. |
| Scan won't start, or the report tabs show an upgrade panel | Run **Check Billing Status** in **Settings** to confirm your Premium subscription is active. |
| No scores yet | Wait for the scan to finish, then refresh **Content Scores**. |

For further help, contact [iCapSolutions](https://www.icapsolutions.com).
