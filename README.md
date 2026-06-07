# iCap SEO
iCap SEO is a WordPress plugin + cloud service foundation for multi-site SEO scoring, setup automation, and AI-assisted recommendations.
Repository: https://github.com/iCapSolutions/iCap-SEO (public)

## Repository structure
- `wordpress-plugin/icap-seo`: WordPress plugin source.
- `services/api`: Cloud API service scaffold (planned).
- `services/workers`: Async workers scaffold (planned).
- `infra`: Infrastructure as code scaffold (planned).
- `docs`: Architecture, boundaries, and implementation notes.

## Initial scope (v0 scaffold)
- WordPress admin plugin scaffold named `iCap SEO`.
- Admin dashboard with three tabs:
  - Home
  - Setup Wizard
  - Site Health
- Placeholder cards/content for future SEO scoring + recommendations.
- Service client contract stubs (no live cloud calls yet).
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
2. Open **iCap SEO** in the dashboard menu.
3. Validate tabs render: Home, Setup Wizard, Site Health.

## Validation
- GitHub Actions runs PHP lint checks for plugin files on pull requests and pushes to `main`.
- Local equivalent command (if PHP is installed):
  - `find wordpress-plugin/icap-seo -name "*.php" -exec php -l {} \;`

## Roadmap (high-level)
1. Plugin base and onboarding UI.
2. Site registration and cloud API handshake.
3. Asynchronous scans and SEO scoring pipeline.
4. AI recommendation engine and remediation workflows.
5. Multi-site management and WordPress.org hardening.

## Progress tracking
- Active implementation tracker: `docs/next-steps.md`
- Update this file as each phase moves from planned → in progress → complete.

## Relationship to seo-tools
Generic SEO scripts remain in the shared `seo-tools` repository. This repository owns product-specific plugin and service code.
