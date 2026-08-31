# Getting started with iCap SEO

This guide walks through installing iCap SEO, connecting it to your account, and running your first scan.

## Prerequisites

- WordPress admin access with plugin install privileges.
- Outbound HTTPS access from your WordPress server (standard on nearly all hosts).

## 1. Install the plugin

Download the latest release ZIP from [Releases](https://github.com/iCapSolutions/iCap-SEO/releases), then in
your WordPress admin go to **Plugins → Add New → Upload Plugin** and activate **iCap SEO**.

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

Already have a registration token from a previous request or your account contact? Skip ahead to step 3.

## 3. Connect your site

1. Open **iCap SEO → Settings**.
2. Confirm the **API Base URL** — pre-filled by default; only change it if instructed by iCapSolutions.
3. Enter your **Registration Token** (from the email in step 2), either in **Settings** or as a
   `wp-config.php` constant:
   ```php
   define('ICAP_SEO_REGISTRATION_TOKEN', 'your-registration-token');
   ```
4. Save settings.

## 4. Verify the connection

Back in **iCap SEO → Setup Wizard**, click **Test Connection**. This confirms the plugin can reach the API
before you register.

## 5. Register your site

In **Setup Wizard**, click **Request Credentials & Register Site**. This provisions your site's connection
credentials automatically — no manual key management needed.

## 6. Run your first scan

In **Overview**, click **Trigger Full Scan**. Non-premium accounts run the free baseline audit; active
premium subscriptions run the full 31-check catalog. Scan status updates in place.

## 7. Review your results

- **Overview** shows your site-wide score, scan summary, and what's included in your plan.
- **Content Scores** shows a per-page checklist — what's passing, what needs attention, and one-click fixes.

## Activating premium

From **iCap SEO → Settings**, click **Start Billing Checkout** to subscribe. Once active, **Check Billing
Status** confirms your entitlement, and your next scan automatically includes the full premium check set
(crawlability, security headers, content quality, structured data, image optimization, and link health).

## Troubleshooting

| Symptom | Fix |
|---|---|
| No verification or token email arrives | Check spam/junk. Confirm the email address you entered is correct. Premium/AI Scanning requests wait on a review step before the token email sends. |
| Registration fails | Double-check the API Base URL and registration token. |
| Scan won't start | Confirm registration completed and credentials are saved in **Settings**. |
| Only baseline checks appear | Run **Check Billing Status** to confirm your premium subscription is active. |
| No scores yet | Wait for the scan to finish, then refresh **Content Scores**. |

For further help, contact [iCapSolutions](https://www.icapsolutions.com).
