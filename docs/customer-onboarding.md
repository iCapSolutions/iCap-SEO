# iCap SEO customer onboarding guide
## Purpose
Single clean guide for onboarding a new customer from plugin install through first successful scan.
This guide covers the customer-facing `icap-seo` plugin only; internal provider/admin workflows are tracked separately in the private `iCap-SEO-control-center` repository.
## Product status snapshot (today)
### Available now
- Plugin install and activation through GitHub release ZIP.
- Setup Wizard connection testing for API reachability diagnostics.
- Setup Wizard registration flow that requests site credentials from the API.
- Manual full-site scan trigger and scan status polling.
- Baseline scan tier (`basic`) available without premium entitlement.
- Setup Wizard visibility for scan tier and executed/premium-locked scan layers.
- Billing actions in plugin Settings:
  - `Check Billing Status`
  - `Start Billing Checkout`
  - `Open Billing Portal`
- Site Health and Content Scores dashboard views.
### Not available yet
- Fully production-hardened customer-portal auth/role enforcement for all tenant billing transitions.
- Finalized live-transition runbooks for all billing edge cases.
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
### 3) Test API reachability
1. Open **iCap SEO → Setup Wizard**.
2. Click **Test Connection**.
Expected result:
- Plugin confirms API reachability or reports actionable failure state (base URL missing, endpoint mismatch, unreachable host, or invalid saved credentials).
### 4) Register the site
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
### 5) Trigger first scan
1. In **Setup Wizard**, click **Trigger Full Scan**.
2. Confirm a `scan_id` appears and scan status transitions to `queued/running/completed`.
Expected result:
- First scan request is accepted and visible in status.
- Non-premium states execute the `basic` baseline layer set.
- Setup Wizard shows `scan_tier` and layer visibility.
### 6) Optional premium activation + premium-layer scan
1. Open **iCap SEO → Settings** and click **Start Billing Checkout**.
2. Complete checkout, then return and click **Check Billing Status**.
3. Trigger another scan from **Setup Wizard**.
Expected result:
- Active/trialing entitlement enables `premium` scan tier.
- Scan status shows full layered checks and no premium-locked layer list for entitled scans.
### 7) Validate dashboard output
1. Open **Site Health** and confirm score/snapshot data renders.
2. Open **Content Scores** and confirm rows appear for pages/posts.
Expected result:
- API-backed score data appears when available.
- Placeholder-safe fallback appears if live API data is temporarily unavailable.
## Troubleshooting quick checks
- **Registration failed**: verify API base URL format, registration token value (constant or saved setting), and API reachability.
- **Scan failed**: confirm `site_id` and `site_token` are present in Settings.
- **Only baseline checks visible**: run **Check Billing Status** and confirm entitlement is active/trialing for premium layers.
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
