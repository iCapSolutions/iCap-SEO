# iCap SEO customer onboarding guide
## Purpose
Single clean guide for onboarding a new customer from plugin install through first successful scan.
This guide covers the customer-facing `icap-seo` plugin only; internal provider/admin workflows are tracked separately in the private `iCap-SEO-control-center` repository.
## Product status snapshot (today)
### Available now
- Plugin install and activation through GitHub release ZIP.
- Setup Wizard registration flow that requests site credentials from the API.
- Manual full-site scan trigger and scan status polling.
- Site Health and Content Scores dashboard views.
### Not available yet
- In-plugin paid-plan checkout/signup.
- Self-serve customer billing portal.
- Subscription-management UI in the plugin.
Paid services and entitlement automation are planned for Phase 1 implementation.
## Prerequisites
- WordPress admin access with plugin install privileges.
- Ability to activate plugins and access the iCap SEO admin menu.
- API base URL provided by iCap SEO operations.
- Registration token provided by iCap SEO operations.
- Outbound HTTPS connectivity from WordPress to the API.
## Step-by-step onboarding
### 1) Install the plugin
Use one of the install options in `README.md`, then activate **iCap SEO**.
### 2) Configure API connection
1. Open **iCap SEO → Settings**.
2. Enter the **API Base URL**.
3. Save settings.
4. Provide a registration token:
   - Preferred: set `ICAP_SEO_REGISTRATION_TOKEN` in `wp-config.php`:
     - `define('ICAP_SEO_REGISTRATION_TOKEN', 'your-registration-token');`
   - Or save **Registration Token** in **iCap SEO → Settings**.
Expected result:
- API base URL is stored in plugin settings.
### 3) Register the site
1. Open **iCap SEO → Setup Wizard**.
2. Click **Request Credentials & Register Site**.
The plugin sends site metadata including:
- `site_url`
- `wp_version`
- `plugin_version`
- `site_name`
- `admin_email`
- `timezone`
Expected result:
- API returns `site_id` and `site_token`.
- Plugin stores credentials in WordPress options.
### 4) Trigger first scan
1. In **Setup Wizard**, click **Trigger Full Scan**.
2. Confirm a `scan_id` appears and scan status transitions to `queued/running/completed`.
Expected result:
- First scan request is accepted and visible in status.
### 5) Validate dashboard output
1. Open **Site Health** and confirm score/snapshot data renders.
2. Open **Content Scores** and confirm rows appear for pages/posts.
Expected result:
- API-backed score data appears when available.
- Placeholder-safe fallback appears if live API data is temporarily unavailable.
## Troubleshooting quick checks
- **Registration failed**: verify API base URL format, registration token value (constant or saved setting), and API reachability.
- **Scan failed**: confirm `site_id` and `site_token` are present in Settings.
- **No score rows yet**: wait for scan completion, then refresh Content Scores.
- **Intermittent API errors**: plugin should stay usable in fallback mode; retry once API recovers.
## Internal onboarding handoff checklist
After onboarding a customer, record:
1. Customer site URL and registration timestamp.
2. Issued `site_id` (do not record raw token in plain text docs).
3. First scan ID and completion status.
4. Any onboarding blockers or required follow-up.
## Related docs
- `README.md`
- `docs/next-steps.md`
- `docs/project-handoff-status.md`
- `docs/hybrid-scoring-api-design.md`
