# iCap SEO next steps and progress
## How to use this tracker
- Keep each item marked with one of: `Planned`, `In Progress`, `Done`, `Blocked`.
- Add links to PRs/issues beside each item as work ships.

## Current phase status
- Plugin scaffold and initial dashboard tabs: `Done`
- Baseline CI (PHP lint): `Done`
- Node runtime warning mitigation in CI: `Done`

## Next execution priorities
1. Plugin settings + connection screen
   - Status: `Planned`
   - Scope:
     - Add settings for API base URL, site ID, auth token placeholder, and connection status.
     - Add Setup Wizard "Test Connection" action (stub first, then real endpoint).
2. First cloud API contract
   - Status: `Planned`
   - Scope:
     - `POST /sites/register`
     - `GET /sites/{siteId}/score-summary`
3. AWS service skeleton
   - Status: `Planned`
   - Scope:
     - API Gateway + Lambda entrypoints
     - DynamoDB table(s) for site profile and score snapshot
     - IAM + secrets baseline
4. End-to-end happy path
   - Status: `Planned`
   - Scope:
     - Register site from plugin
     - Persist identifiers/options in WordPress
     - Fetch and display score summary on Home tab
5. Plugin hardening pass
   - Status: `Planned`
   - Scope:
     - Nonce checks
     - Capability checks
     - Input sanitization / output escaping audit
     - Activation/deactivation/uninstall behavior
6. CI expansion
   - Status: `Planned`
   - Scope:
     - WordPress coding standards (PHPCS) checks
     - Optional plugin smoke tests

## Multi-site alpha rollout checklist
- Select first two non-production WordPress sites for alpha.
- Define release packaging approach (zip + changelog).
- Create rollback checklist for failed installs.
- Capture issue log and iterate weekly.
