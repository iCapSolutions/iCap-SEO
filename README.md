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
2. Download `icap-seo.zip` from the latest release assets.
3. In WordPress admin, go to **Plugins → Add New → Upload Plugin**.
4. Upload `icap-seo.zip`, install, and activate.

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
- `dist/icap-seo.zip`

## Quick start after install
1. Activate **iCap SEO** from WordPress admin.
2. Open **iCap SEO → Settings** and set **API Base URL**.
3. Provide a registration token (either approach):
   - Preferred: set a constant in `wp-config.php`:
     - `define('ICAP_SEO_REGISTRATION_TOKEN', 'your-registration-token');`
   - Or save **Registration Token** in **iCap SEO → Settings**.
4. Go to **Setup Wizard** and click **Request Credentials & Register Site**.
5. Click **Trigger Full Scan** and confirm `scan_id` + status are returned.
6. Review **Site Health** and **Content Scores** tabs.

## Customer onboarding and support
- Canonical onboarding doc for new customers: `docs/customer-onboarding.md`
- Current product note: self-serve paid checkout/subscription management is planned and not yet shipped in-plugin.

## Validation
- GitHub Actions runs PHP lint checks for plugin files on pull requests and pushes to `main`.
- Local equivalent command (if PHP is installed):
  - `find wordpress-plugin/icap-seo -name "*.php" -exec php -l {} \;`

## Roadmap (high-level)
1. Finalize onboarding UX and connection diagnostics.
2. Productionize scan and content-score API behavior.
3. Add paid onboarding, entitlement enforcement, and customer portal/control-plane APIs used by the private control-center repository.
4. Expand recommendation engine and remediation workflows.
5. Multi-site management and WordPress.org hardening.

## Progress tracking
- Active implementation tracker: `docs/next-steps.md`
- Session handoff + cross-repo status: `docs/project-handoff-status.md`
- Private control-center bootstrap checklist: `docs/control-center-private-repo-bootstrap.md`
- Update this file as each phase moves from planned → in progress → complete.

## Relationship to seo-tools
Generic SEO scripts remain in the shared `seo-tools` repository. This repository owns product-specific plugin and service code.
