# iCap SEO
iCap SEO is a WordPress plugin + cloud service foundation for multi-site SEO scoring, setup automation, and AI-assisted recommendations.
Repository: https://github.com/iCapSolutions/iCap-SEO (public)

## Repository structure
- `wordpress-plugin/icap-seo`: WordPress plugin source.
- `services/api`: Cloud API service scaffold (planned).
- `services/workers`: Async workers scaffold (planned).
- `infra`: Infrastructure as code scaffold (planned).
- `docs`: Architecture, boundaries, and implementation notes.
- `icap-seo-control-center`: maintained in a separate private repository (not included in this public repo).
## Current scope (v0.1 foundation)
- WordPress admin plugin named `iCap SEO`.
- Admin dashboard tabs:
  - Home
  - Setup Wizard
  - Site Health
  - Content Scores
  - Settings
- Self-serve site registration from the plugin (`site_id` + `site_token` persisted in WordPress options).
- Manual full-site scan trigger and scan-status polling.
- Content score retrieval with safe placeholder fallback when API data is unavailable.
## Installation
### Official method: Install from GitHub Release ZIP
1. Open Releases:
   - https://github.com/iCapSolutions/iCap-SEO/releases
2. Download `icap-seo-vX.Y.Z.zip` from the latest release assets.
3. In WordPress admin, go to **Plugins → Add New → Upload Plugin**.
4. Upload the downloaded `icap-seo-vX.Y.Z.zip`, install, and activate.

### Method 2: Git clone + manual copy
```sh
git clone https://github.com/iCapSolutions/iCap-SEO.git
cp -R iCap-SEO/wordpress-plugin/icap-seo /path/to/wordpress/wp-content/plugins/
```
Then activate **iCap SEO** in WordPress admin.

### Method 3: Direct server install from repository
```sh
git clone https://github.com/iCapSolutions/iCap-SEO.git
cp -R /path/to/iCap-SEO/wordpress-plugin/icap-seo /var/www/html/wp-content/plugins/
```
Then activate **iCap SEO** in the WordPress Plugins page.

## Maintainer release packaging
Build release zip (maintainers):
```sh
scripts/build-plugin-zip.sh
```
Output:
- `dist/icap-seo-vX.Y.Z.zip` (derived from `ICAP_SEO_VERSION` in `wordpress-plugin/icap-seo/icap-seo.php`)

## Quick start after install
1. Activate **iCap SEO** from WordPress admin.
2. Open **iCap SEO → Settings** and set **API Base URL**.
3. Provide a registration token (either approach):
   - Preferred: set a constant in `wp-config.php`:
     - `define('ICAP_SEO_REGISTRATION_TOKEN', 'your-registration-token');`
   - Or save **Registration Token** in **iCap SEO → Settings**.
4. Go to **Setup Wizard** and click **Request Credentials & Register Site**.
5. In **Settings**, click **Start Billing Checkout** to create/activate subscription.
6. Use **Check Billing Status** and confirm entitlement is active/trialing.
7. Click **Trigger Full Scan** and confirm `scan_id` + status are returned.
8. Review **Site Health** and **Content Scores** tabs.
## Registration token requirement
- The Setup Wizard action **Request Credentials & Register Site** requires a registration token.
- Token source precedence:
  1. `ICAP_SEO_REGISTRATION_TOKEN` constant in `wp-config.php` (highest precedence)
  2. **Registration Token** saved in plugin settings
- If no registration token is available, registration is expected to fail with a token-required error.
- This requirement applies to registration requests; existing configured `site_id` + `site_token` flows can still be used for scan/status requests.
## How to request a registration token
- Registration tokens are currently issued manually by iCap SEO operations/admin.
- When requesting a token, provide:
  - target site URL
  - API base URL you will register against
  - admin/contact email for the site
- Store the token using one of the supported methods:
  - `ICAP_SEO_REGISTRATION_TOKEN` in `wp-config.php` (preferred)
  - **Registration Token** field in **iCap SEO → Settings**
- Keep the token private and do not commit it to git or include it in shared docs/screenshots.
## Registration flow testing checklist
Use this checklist after installing a new plugin ZIP:
1. Confirm **Registration Token** field is visible in **iCap SEO → Settings**.
2. Leave API Base URL empty and click **Request Credentials & Register Site**.
   - Expected: API Base URL required error.
3. Set API Base URL, leave registration token empty, and click register again.
   - Expected: registration token required error.
4. Set valid API Base URL + valid registration token, then click register.
   - Expected: registration success and saved `site_id` / `site_token`.
5. Trigger a full scan and confirm status updates normally.
## Billing entitlement checks
- In **iCap SEO → Settings**, use **Check Billing Status** to request current entitlement state from:
  - `GET /v1/billing/subscription-status`
- Plugin stores the latest check metadata:
  - `last_billing_state`
  - `last_billing_checked_at`
- Scan trigger behavior:
  - `payment_required`: scan is blocked until billing is resolved.
  - `subscription_required`: scan is blocked until an active subscription exists.
  - `account_suspended`: scan is blocked until account access is restored.
- Recovery path:
  - Resolve billing/subscription state in the customer billing system.
  - Re-run **Check Billing Status**.
  - Retry **Trigger Full Scan**.

## Customer onboarding and support
- Canonical onboarding doc for new customers: `docs/customer-onboarding.md`
- Current product note: plugin settings include actions for checkout-session and billing-portal session launch; webhook-driven entitlement updates complete the activation flow.

## Validation
- GitHub Actions runs PHP lint checks for plugin files on pull requests and pushes to `main`.
- Local equivalent command (if PHP is installed):
  - `find wordpress-plugin/icap-seo -name "*.php" -exec php -l {} \;`

## Roadmap (high-level)
1. Finalize onboarding UX and connection diagnostics.
2. Productionize scan and content-score API behavior.
3. Harden paid onboarding, entitlement automation, and customer portal/control-plane APIs used by the private control-center repository.
4. Expand recommendation engine and remediation workflows.
5. Multi-site management and WordPress.org hardening.

## Progress tracking
- Active implementation tracker: `docs/next-steps.md`
- Session handoff + cross-repo status: `docs/project-handoff-status.md`
- Private control-center bootstrap checklist: `docs/control-center-private-repo-bootstrap.md`
- Update this file as each phase moves from planned → in progress → complete.

## Relationship to seo-tools
Generic SEO scripts remain in the shared `seo-tools` repository. This repository owns product-specific plugin and service code.
