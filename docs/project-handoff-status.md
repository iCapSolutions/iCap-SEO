# iCap SEO project handoff and current status
## Purpose
Single source of truth for:
- what has already been completed
- where work is currently left off
- what to do next in a new session

Use this file when restarting work and when asking: "Where are we on iCap SEO and what should we do next?"

## Repositories in scope
- Product/plugin repo: `iCap-SEO`
- AWS/backend infrastructure repo: `infrastructure`
- Marketing/site/docs repo: `icapsolutions`
- Provider/admin plugin repo: `iCap-SEO-control-center` (private)

## Completed so far
### 1) WordPress plugin (`iCap-SEO`)
- Built and stabilized plugin admin experience and dashboard paths.
- Fixed recursion/memory issue that caused admin hangs/blank pages/timeouts.
- Implemented self-serve registration flow:
  - plugin can register from API base URL
  - stores returned `site_id` and `site_token`
  - includes `admin_email` in register payload
- Added registration-token support with clear source precedence:
  - `ICAP_SEO_REGISTRATION_TOKEN` constant in `wp-config.php`
  - saved plugin setting fallback
- Added settings actions and UX for billing/entitlement visibility:
  - `Check Billing Status` action wired to `GET /v1/billing/subscription-status`
  - `Start Billing Checkout` action wired to `POST /v1/billing/checkout-session`
  - `Open Billing Portal` action wired to `POST /v1/billing/portal-session`
  - entitlement-aware scan-blocking notices for `payment_required`, `subscription_required`, and `account_suspended`
- Added versioned ZIP packaging conventions and script support:
  - release artifact format `icap-seo-vX.Y.Z.zip`
  - latest plugin version on `main`: `0.1.9`
  - latest distributed ZIP line for testing: `icap-seo-v0.1.9.zip`
- Live smoke flow validated:
  - register (including expected token-required failure path)
  - trigger scan
  - poll scan status
  - fetch content scores

### 2) Backend/infrastructure (`infrastructure`)
- Provisioned iCap SEO API/backend infrastructure in AWS.
- Standardized production-focused environment naming at:
  - `environments/icap-seo-production`
- Applied Terraform locally for provisioning (not auto-applied by CI).
- Added Terraform plan/approval/apply GitHub Actions workflow with guardrails.
- Investigated and resolved a workflow parse/dispatch regression:
  - parse failures showed as instant 0s failures and 422 dispatch errors
  - confirmed regression tied to heredoc-in-command-substitution patterns in workflow scripts
  - restored stable workflow, then reintroduced Claude summary generation with heredoc-free syntax
- Current workflow state (latest): stable and dispatchable, with manual plan/apply path and Claude summary step execution.
- Merged entitlement-alignment backend updates (PR `#36`):
  - `GET /v1/billing/subscription-status` now supports site-token + `X-ICAP-Site-Id` mode for customer plugin calls, while preserving admin-token summary mode for control-center/admin use
  - `POST /v1/sites/{site_id}/scans` now enforces entitlement gating and returns expected error codes for blocked states
- Added Stripe onboarding flow hardening:
  - checkout-session and portal-session creation backed by Stripe API requests
  - webhook signature validation + event-id idempotency via dedicated DynamoDB event table
  - webhook processing audit persistence and billing-policy enforcement (`US` country + `USD` currency for activation)
- Added and validated activity-notification path for registration/billing lifecycle events:
  - EventBridge bus/rule + SNS email delivery path is deployed
  - Stripe completion events now flow through webhook ingestion to entitlement updates and activity notifications
  - current email template is configured for readable multiline label/value formatting
- Added optional production DNS support for Numbercrate Google Search Console verification:
  - `numbercrate_google_site_verification_tokens` variable in `environments/production/variables.tf`
  - conditional TXT record creation in `environments/production/dns.tf`
- Terraform validation completed for both updated environments before merge:
  - `environments/icap-seo-production`
  - `environments/production`
- Replaced placeholder scan outputs with profile-driven backend execution using exported service definitions from `seo-tools`.
- Added durable scan persistence for API consumers:
  - scan runs table (`icap-seo-production-scan-runs`)
  - content score snapshots table (`icap-seo-production-content-score-snapshots`, GSI `by_site_scan`)
  - history-capable content score detail responses
- Applied and verified these infrastructure changes via GitHub workflow path:
  - merged workflow artifact fix PR `infrastructure#46`
  - successful workflow apply run: `Terraform Plan / Approval / Apply` (`workflow_dispatch`)

### 3) Website/marketing/docs (`icapsolutions`)
- iCap SEO public page created and published:
  - URL: `https://www.icapsolutions.com/services/wordpress-seo-plugin.html`
- Core plugin page sections and SEO metadata implemented.
- Internal links from existing SEO pages to plugin page added.
- Planning docs for plugin positioning and page strategy are in place.
- Follow-up work to consolidate information architecture and publish complete service/onboarding docs remains pending.

### 4) Provider/admin plugin (`iCap-SEO-control-center`, private)
- Private repository created and isolated from customer-distributed plugin code.
- Phase-1 read-only tenant and billing views shipped.
- Phase-2 baseline shipped with pinned contract version, guarded billing resync action, and audit logging.
- Billing session UX clarity improvements shipped:
  - single site selector with explicit checkout vs portal actions
  - unified action handler + clearer invalid-action notice behavior
- Release ZIP automation is active and latest release line is `v0.2.7`.

## Where we are left off
### Current technical state
- Plugin path is functional for registration + scan + score retrieval.
- AWS backend path is provisioned and reachable.
- Terraform workflow pipeline is operational again after parser regression fixes.
- Claude summary integration is restored with parser-safe syntax.
- Control-center private repo is active with baseline admin operations shipped.
- Plugin entitlement UX and backend entitlement enforcement are now aligned and merged.
- Stripe checkout/portal session APIs and webhook-driven entitlement transitions are implemented.
- Stripe webhook endpoint/signing-secret flow is configured and validated for current environment.
- EventBridge → SNS activity notifications are active with readable multiline email formatting.
- Scan API routes are profile-driven and persist run/history data in DynamoDB-backed tables.
- Workflow-based infrastructure apply has been validated for this deployment line.
- End-to-end architecture/workflow documentation is now captured in `docs/architecture.md`.

### Current product state
- Public landing page exists, but broader product marketing/documentation expansion is still pending.
- No single "launch checklist" has been completed yet across plugin + backend + website content.
- Canonical onboarding flow now lives in `iCap-SEO/docs/customer-onboarding.md`.
- Self-serve paid signup/subscription-management API flow is shipped; customer portal auth/role hardening and live transition validation remain pending.
- Architecture decision: keep customer plugin in public `iCap-SEO` and move iCapSolutions admin/control-center tooling to separate private `iCap-SEO-control-center`, sharing common backend endpoint contracts.

## Highest-priority next actions
1. **Website productization and docs IA cleanup (soon)**
   - Expand and reorganize `icapsolutions` content so the iCap SEO service has one clear user path.
   - Publish complete customer-facing documentation for setup, billing flow, onboarding, and support.
   - Ensure CTA path is explicit (contact/demo/trial) and linked from existing SEO/service pages.
2. **Complete paid onboarding automation (Stripe + control-plane)**
   - Complete customer-portal human auth + tenant-role enforcement for production.
   - Execute live end-to-end validation of Stripe-driven entitlement transitions.
   - Capture rollback/runbook guidance for webhook failures and billing-policy blocks.
3. **Integrated validation across plugin + backend**
   - Re-run end-to-end checks against a live test site for key entitlement transitions:
     - active/trialing (scan allowed)
     - past_due/grace_period (payment-required block)
     - canceled/suspended (scan blocked)
   - Capture runbook notes for support and troubleshooting.
4. **Provider control-center plugin track**
   - Continue expanding the separate private internal admin plugin for iCapSolutions operations (not bundled into customer plugin).
   - Keep shared/common API contracts synchronized while preserving strict deployment and permissions separation from client sites.
5. **Plugin release discipline**
   - Prepare next plugin release when plugin code changes warrant a version bump beyond `0.1.9`.
   - Publish release notes and align README install/test steps with current behavior.
6. **Backend operations hardening**
   - Add explicit monitoring/alarming and rollback runbook notes for backend/plugin deploys.
   - Add recurring smoke-test automation for scan trigger/status/content-history endpoints after deploys.
7. **Cross-repo roadmap sync**
   - Keep this handoff file updated when major milestones ship in `iCap-SEO`, `iCap-SEO-control-center`, `infrastructure`, and `icapsolutions`.

## Open backlog themes
- Plugin hardening/security review and UX improvements.
- API/business-logic depth for scoring/recommendations.
- Tenant/account/billing model maturation.
- Customer-facing documentation (setup guide, troubleshooting, FAQ, release notes).

## Fast restart instructions for next session
Start with:
1. Read:
   - `iCap-SEO/docs/project-handoff-status.md` (this file)
   - `iCap-SEO/docs/architecture.md`
   - `iCap-SEO/docs/customer-onboarding.md`
   - `iCap-SEO/docs/control-center-private-repo-bootstrap.md`
   - `iCap-SEO/docs/next-steps.md`
   - `infrastructure/README.md` (iCap SEO sections)
   - `icapsolutions/docs/wordpress-seo-plugin-page-content.md`
2. Ask for:
   - current git status in all 4 repos (`iCap-SEO`, `iCap-SEO-control-center`, `infrastructure`, `icapsolutions`)
   - open PRs/issues for `iCap-SEO`, `iCap-SEO-control-center`, and `infrastructure`
   - latest successful workflow runs for `infrastructure/.github/workflows/terraform.yml`
3. Execute the top unfinished item from **Highest-priority next actions**.

## Suggested prompt for future sessions
"Give me the current iCap SEO project status from the handoff docs, list unfinished high-priority items, and propose the next implementation step across iCap-SEO, iCap-SEO-control-center, infrastructure, and icapsolutions."
