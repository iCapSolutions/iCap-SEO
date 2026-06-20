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
- Self-serve paid signup and billing automation (Stripe-driven): `In Progress`

## Next execution priorities
1. Phase-1 paid onboarding + entitlement enforcement
   - Status: `In Progress`
   - Scope:
     - Remaining: implement billing API surface for checkout/portal session creation and Stripe webhook ingestion.
     - Remaining: move tenant entitlement transitions to billing-system-driven updates (instead of scaffold/manual state only).
     - Done: customer plugin billing-state checks and scan-blocking notice handling for `payment_required`, `subscription_required`, and `account_suspended`.
     - Done: backend support for site-token `GET /v1/billing/subscription-status` and entitlement-gated `POST /v1/sites/{site_id}/scans`.
2. Provider control-center plugin (private repository)
   - Status: `In Progress`
   - Scope:
     - Maintain separate private repository `iCap-SEO-control-center` for provider/admin operations.
     - Keep control-center and customer plugin separate while sharing common endpoint contracts/client logic.
     - Expand beyond current baseline (read-only tenant/billing views + guarded billing resync + audit logging) with deeper support workflows.
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
     - Cross-repo status synchronized in `docs/project-handoff-status.md` after merged infrastructure PR `#36`.
     - Production DNS optional Google verification-token support validated and merged.
10. Website productization and guides (icapsolutions repo)
   - Status: `Planned`
   - Scope:
     - Expand marketing pages for iCap SEO product positioning.
     - Add how-to documentation and onboarding guides.
     - Add FAQ/troubleshooting content and CTA pathways.

## Multi-site alpha rollout checklist
- Select first two non-production WordPress sites for alpha.
- Define release packaging approach (zip + changelog).
- Create rollback checklist for failed installs.
- Capture issue log and iterate weekly.
