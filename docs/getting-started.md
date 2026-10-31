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

## 2. Connect your site

1. Open **iCap SEO → Account** and click **Get started**.
2. Enter your email and click **Email me a token**.
3. Click the verification link we email you. Your token arrives by email right after. Check your spam folder if
   you don't see it.
4. Paste the token on the same page and click **Connect this site**.

Each token works once, for the site it was issued for. Already have one? Click **Already have a token?** on the
first step. You can also set it as a `wp-config.php` constant:

```php
define('ICAP_SEO_REGISTRATION_TOKEN', 'your-registration-token');
```

## 3. Activate Premium

Once the site is connected, click **Upgrade to Premium** under **Account → Billing**. When you return, the
plugin refreshes your billing status.

## 4. Connect Google and run your first scan

Connect Google Search Console and Google Analytics on the **Account** tab to fill the **SEO Performance**,
**Site Analytics**, and **Keywords** tabs — free on any registered site. With Premium active, click
**Trigger Full Scan** in **Overview**. Scans cover up to 100 pages and include the 3 checks run from
outside your site (page reachability, and broken internal and external links). Scan status updates in place.

## 5. Review your results

- **Overview** shows your site-wide score, scan summary, and what's included in your plan.
- **Content Scores** shows a per-page checklist — what's passing, what needs attention, and one-click fixes.

Without Premium, click **Check all pages** in **Content Scores** to run the free checks across your site, or open
any page to check it on its own, including **Check links** for its broken links. A few more tabs
work immediately without registration: **Redirects** (set up
redirects and see a log of 404s visitors hit) and **Local SEO** (enter your business info once to output
LocalBusiness structured data site-wide). Social sharing previews and `/llms.txt` are on automatically, no
setup needed. IndexNow pings are off until you turn them on under **Advanced** on the **Account** tab.

## Troubleshooting

| Symptom | Fix |
|---|---|
| No verification or token email arrives | Check spam/junk. Confirm the email address you entered is correct. |
| Registration fails | Double-check the registration token. Each token works once, for one site. |
| Scan won't start, or the report tabs show an upgrade panel | Click **Refresh billing status** under **Advanced** on the **Account** tab to confirm your Premium subscription is active. |
| No scores yet | Wait for the scan to finish, then refresh **Content Scores**. |

For further help, contact [iCapSolutions](https://www.icapsolutions.com).
