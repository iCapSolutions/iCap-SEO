# Control-center private repository bootstrap checklist
## Purpose
Provide a concrete setup checklist for launching `iCap-SEO-control-center` as a private repository while keeping customer plugin code in public `iCap-SEO`.
## Scope
- In scope: private repository setup, security guardrails, plugin bootstrap, shared endpoint contract workflow, release process.
- Out of scope: customer plugin onboarding steps (covered in `docs/customer-onboarding.md`).
## Architecture guardrails (must keep)
1. Public repo `iCap-SEO` contains customer plugin only.
2. Private repo `iCap-SEO-control-center` contains provider/admin plugin only.
3. Both plugins use shared backend API contracts; private/admin code does not move into public repo.
4. Billing and control-plane endpoints remain server-enforced (no trust in UI-only checks).
## Phase 0: repository creation and access
1. Create private GitHub repository: `iCap-SEO-control-center`.
2. Restrict repository access to provider/admin maintainers only.
3. Disable broad forking/cloning policies not required for internal operations.
4. Require branch protection on `main`:
   - pull requests required,
   - status checks required before merge,
   - no direct push to `main`.
5. Configure CODEOWNERS for sensitive paths (`wordpress-plugin/`, `.github/workflows/`, `docs/`).
## Phase 1: initial repository structure
Create initial structure:
- `wordpress-plugin/icap-seo-control-center/`
- `wordpress-plugin/icap-seo-control-center/admin/`
- `wordpress-plugin/icap-seo-control-center/includes/`
- `wordpress-plugin/icap-seo-control-center/assets/`
- `scripts/` (build/package helpers)
- `docs/` (internal operations notes)
- `.github/workflows/` (lint/package checks)
## Phase 2: shared contract strategy
Use backend contracts as the shared integration layer:
1. Treat API contract schema as the source of truth (OpenAPI/spec docs).
2. Version contract changes and annotate breaking vs non-breaking changes.
3. Require both repos to pin/support the same contract version before production rollout.
4. Add a compatibility checklist to PR templates in both repos:
   - required endpoints available,
   - auth model unchanged or explicitly migrated,
   - error codes mapped in both plugin UIs.
## Phase 3: auth and endpoint boundaries
- Customer plugin (`icap-seo`):
  - uses site token auth for site-scoped operations.
  - no provider/admin control-plane access.
- Control-center plugin (`icap-seo-control-center`):
  - uses provider/admin auth flow (human identity and role checks).
  - consumes admin/control-plane endpoints and billing views.
  - no customer-site distribution.
## Phase 4: CI/CD and release workflow
1. Add private-repo CI checks (PHP lint/static checks + packaging sanity checks).
2. Build installable ZIP artifact for `icap-seo-control-center`.
3. Deploy only to iCapSolutions-managed WordPress environment(s).
4. Record release notes with:
   - endpoint contract version,
   - migration steps,
   - rollback instructions.
## Phase 5: operational readiness checks
Before first production use:
1. Verify billing status views render correctly for representative tenant states.
2. Verify support actions are auditable and role-gated.
3. Verify entitlement changes propagate to customer plugin behavior.
4. Verify logs/alerts for webhook failures and entitlement-sync failures.
## Immediate execution order
1. Create private repo + branch protections + CODEOWNERS.
2. Scaffold plugin structure and baseline CI.
3. Define/pin shared contract version and compatibility checklist.
4. Implement initial read-only admin views (tenant/billing state).
5. Add controlled write actions with audit logging.
