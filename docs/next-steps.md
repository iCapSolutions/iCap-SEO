# iCap SEO next steps and progress
## How to use this tracker
- Keep each item marked with one of: `Planned`, `In Progress`, `Done`, `Blocked`.
- Add links to PRs/issues beside each item as work ships.
- Keep `docs/project-handoff-status.md` synchronized with any major milestone changes.

## Current phase status
- Plugin scaffold and admin stabilization: `Done`
- Self-serve registration and credential save flow: `Done`
- End-to-end smoke path (register → scan → status → scores): `Done`
- Production iCap SEO backend provisioning in AWS: `Done`
- Terraform plan/approval/apply workflow with parser-safe Claude summaries: `Done`

## Next execution priorities
1. Plugin settings + connection screen
   - Status: `In Progress`
   - Scope:
     - Validate and polish current settings UX (API base URL, site credentials, status messaging).
     - Add/finish Setup Wizard "Test Connection" action and error-state guidance.
2. First cloud API contract
   - Status: `In Progress`
   - Scope:
     - `POST /sites/register`
     - Scan and score retrieval contract hardening (`/scans`, `/content-scores`) with production-ready responses.
3. AWS service skeleton
   - Status: `Done`
   - Scope:
     - API Gateway + Lambda entrypoints
     - Supporting infra environment at `infrastructure/environments/icap-seo-production`
     - Terraform workflow guardrails and manual apply path
4. End-to-end happy path
   - Status: `Done`
   - Scope:
     - Register site from plugin
     - Persist identifiers/options in WordPress
     - Trigger scan, poll status, and fetch score data
5. Plugin hardening pass
   - Status: `Planned`
   - Scope:
     - Nonce checks
     - Capability checks
     - Input sanitization / output escaping audit
     - Activation/deactivation/uninstall behavior
6. CI expansion
   - Status: `In Progress`
   - Scope:
     - Keep Terraform workflow stable and monitor parser/dispatch reliability.
     - Add/expand plugin checks (PHPCS and smoke tests) where practical.
7. Website productization and guides (icapsolutions repo)
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
