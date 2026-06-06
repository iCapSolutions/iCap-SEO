# Service boundaries
## Purpose
Define ownership between `iCap-SEO` and shared tooling in `seo-tools` to avoid duplication and keep architecture maintainable.

## `iCap-SEO` owns
- WordPress plugin product code (`wordpress-plugin/icap-seo`).
- Plugin UI/UX, setup wizard, and admin workflows.
- Cloud API and async worker services for plugin-backed features.
- Product-level data model, scoring contracts, and tenant/site lifecycle.

## `seo-tools` owns
- Shared CLI scripts for SEO audits, WordPress operations, and reporting.
- Generic workflow automation reusable by multiple repositories.
- Script-level documentation and operational runbooks.

## Integration pattern
- `iCap-SEO` may call into shared patterns from `seo-tools` conceptually, but should not copy scripts directly into plugin/runtime code.
- Any reusable cross-project script belongs in `seo-tools`.
- Product-specific API/worker logic belongs in `iCap-SEO`.
