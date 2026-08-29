# Getting started with iCap SEO

This guide walks through installing iCap SEO, connecting it to your account, and running your first scan.

## Prerequisites

- WordPress admin access with plugin install privileges.
- A registration token from iCapSolutions (request one at
  [icapsolutions.com](https://www.icapsolutions.com/services/wordpress-seo-plugin.html) or from your account
  contact).
- Outbound HTTPS access from your WordPress server (standard on nearly all hosts).

## 1. Install the plugin

Download the latest release ZIP from [Releases](https://github.com/iCapSolutions/iCap-SEO/releases), then in
your WordPress admin go to **Plugins → Add New → Upload Plugin** and activate **iCap SEO**.

## 2. Connect your site

1. Open **iCap SEO → Settings**.
2. Enter the **API Base URL** provided with your account.
3. Enter your **Registration Token**, either in **Settings** or as a `wp-config.php` constant:
   ```php
   define('ICAP_SEO_REGISTRATION_TOKEN', 'your-registration-token');
   ```
4. Save settings.

## 3. Verify the connection

Open **iCap SEO → Setup Wizard** and click **Test Connection**. This confirms the plugin can reach the API
before you register.

## 4. Register your site

In **Setup Wizard**, click **Request Credentials & Register Site**. This provisions your site's connection
credentials automatically — no manual key management needed.

## 5. Run your first scan

In **Overview**, click **Trigger Full Scan**. Non-premium accounts run the free baseline audit; active
premium subscriptions run the full 31-check catalog. Scan status updates in place.

## 6. Review your results

- **Overview** shows your site-wide score, scan summary, and what's included in your plan.
- **Content Scores** shows a per-page checklist — what's passing, what needs attention, and one-click fixes.

## Activating premium

From **iCap SEO → Settings**, click **Start Billing Checkout** to subscribe. Once active, **Check Billing
Status** confirms your entitlement, and your next scan automatically includes the full premium check set
(crawlability, security headers, content quality, structured data, image optimization, and link health).

## Troubleshooting

| Symptom | Fix |
|---|---|
| Registration fails | Double-check the API Base URL and registration token. |
| Scan won't start | Confirm registration completed and credentials are saved in **Settings**. |
| Only baseline checks appear | Run **Check Billing Status** to confirm your premium subscription is active. |
| No scores yet | Wait for the scan to finish, then refresh **Content Scores**. |

For further help, contact [iCapSolutions](https://www.icapsolutions.com).
