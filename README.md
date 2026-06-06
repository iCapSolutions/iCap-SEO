# iCap SEO
iCap SEO is a WordPress plugin + cloud service foundation for multi-site SEO scoring, setup automation, and AI-assisted recommendations.

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

## Local development
1. Copy `wordpress-plugin/icap-seo` into your WordPress install:
   - `wp-content/plugins/icap-seo`
2. Activate **iCap SEO** from WordPress admin.
3. Open **iCap SEO** in the dashboard menu.

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

## Relationship to seo-tools
Generic SEO scripts remain in the shared `seo-tools` repository. This repository owns product-specific plugin and service code.
