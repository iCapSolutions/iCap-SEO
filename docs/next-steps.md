# iCap SEO next steps and progress
## How to use this tracker
- Keep each item marked with one of: `Planned`, `In Progress`, `Done`, `Blocked`.
- Add links to PRs/issues beside each item as work ships.
- Keep `docs/project-handoff-status.md` synchronized with any major milestone changes.
- Keep `docs/customer-onboarding.md` as the canonical customer onboarding flow.
- Use `docs/control-center-private-repo-bootstrap.md` as the source checklist for private control-center repository setup.

## Current phase status
- Plugin scaffold and admin stabilization: `Done`
- Self-serve registration and credential save flow: `Done`
- End-to-end smoke path (register → scan → status → scores): `Done`
- Plugin billing-status checks and entitlement-aware scan messaging: `Done`
- Production iCap SEO backend provisioning in AWS: `Done`
- Terraform plan/approval/apply workflow with parser-safe Claude summaries: `Done`
- Backend entitlement alignment for site-token billing status + scan gating: `Done`
- Canonical customer onboarding documentation: `Done`
- Dual-plugin architecture decision (public customer plugin + separate private control-center repository): `Done`
- Private control-center repository bootstrap and phase-2 baseline releases: `Done`
- EventBridge + SNS activity notifications with readable multiline email formatting: `Done`
- Stripe webhook endpoint/signing-secret flow validated in active environment: `Done`
- Self-serve paid signup and billing automation (Stripe-driven): `In Progress`

## Next execution priorities
1. Phase-1 paid onboarding + entitlement enforcement
   - Status: `In Progress`
   - Scope:
     - Done: billing API surface for checkout/portal session creation and Stripe webhook ingestion is implemented.
     - Done: webhook idempotency + audit persistence by Stripe event ID is implemented.
     - Done: tenant entitlement transitions are driven by Stripe billing events.
     - Remaining: complete customer-portal human auth + tenant role enforcement before production rollout.
     - Remaining: run live end-to-end subscription transition validation across representative tenant states.
     - Done: customer plugin billing-state checks and scan-blocking notice handling for `payment_required`, `subscription_required`, and `account_suspended`.
     - Done: backend support for site-token `GET /v1/billing/subscription-status` and entitlement-gated `POST /v1/sites/{site_id}/scans`.
2. Provider control-center plugin (private repository)
   - Status: `In Progress`
   - Scope:
     - Maintain separate private repository `iCap-SEO-control-center` for provider/admin operations.
     - Keep control-center and customer plugin separate while sharing common endpoint contracts/client logic.
     - Done: billing session actions now use one site selector with explicit checkout vs portal actions (clarity update shipped in release line `v0.2.7`).
     - Expand beyond current baseline with deeper support workflows and operations tooling.
     - Keep bootstrap checklist and operational docs current in `docs/control-center-private-repo-bootstrap.md`.
3. Plugin settings + connection screen
   - Status: `In Progress`
   - Scope:
     - Done: registration token settings UX and precedence docs (`wp-config.php` constant over saved setting).
     - Done: billing status action and persisted billing-check metadata.
     - Remaining: add/finish Setup Wizard "Test Connection" action and tighten error-state guidance.
4. First cloud API contract
   - Status: `In Progress`
   - Scope:
     - `POST /sites/register`
     - Done: dual-mode `GET /v1/billing/subscription-status` behavior (site-token and admin-token contexts).
     - Done: entitlement-aware scan gating on `POST /sites/{site_id}/scans`.
     - Remaining: scan and score retrieval contract hardening (`/scans`, `/content-scores`) with production-ready responses.
5. AWS service skeleton
   - Status: `Done`
   - Scope:
     - API Gateway + Lambda entrypoints
     - Supporting infra environment at `infrastructure/environments/icap-seo-production`
     - Terraform workflow guardrails and manual apply path
6. End-to-end happy path
   - Status: `Done`
   - Scope:
     - Register site from plugin
     - Persist identifiers/options in WordPress
     - Trigger scan, poll status, and fetch score data
7. Plugin hardening pass
   - Status: `Planned`
   - Scope:
     - Nonce checks
     - Capability checks
     - Input sanitization / output escaping audit
     - Activation/deactivation/uninstall behavior
8. CI expansion
   - Status: `In Progress`
   - Scope:
     - Keep Terraform workflow stable and monitor parser/dispatch reliability.
     - Add/expand plugin checks (PHPCS and smoke tests) where practical.
9. Progress checkpoint update (current)
   - Status: `Done`
   - Scope:
     - Cross-repo status synchronized in `docs/project-handoff-status.md` after merged infrastructure notification/template PR sequence (`#39`–`#43`) and control-center clarity release.
     - Production DNS optional Google verification-token support validated and merged.
10. Website productization and guides (icapsolutions repo)
   - Status: `In Progress`
   - Scope:
     - Expand marketing pages for iCap SEO product positioning.
     - Add how-to documentation and onboarding guides.
     - Add FAQ/troubleshooting content and CTA pathways.
     - Near-term priority: restructure site information architecture so iCap SEO has one clear service path with complete documentation in one place.

## Multi-site alpha rollout checklist
- Select first two non-production WordPress sites for alpha.
- Define release packaging approach (zip + changelog).
- Create rollback checklist for failed installs.
- Capture issue log and iterate weekly.
