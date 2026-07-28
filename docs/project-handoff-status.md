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
- Added Setup Wizard connection diagnostics:
  - `Test Connection` action for API reachability checks before registration
  - actionable notices for invalid credentials, unreachable endpoints, and API-base misconfiguration
- Expanded Setup Wizard scan visibility:
  - displays `scan_tier` from scan-status responses
  - shows executed scan layers and premium-locked layers when returned by backend
- Added versioned ZIP packaging conventions and script support:
  - release artifact format `icap-seo-vX.Y.Z.zip`
  - latest plugin version on `main`: `0.1.13`
  - latest distributed ZIP line for testing: `icap-seo-v0.1.13.zip`
- Live smoke flow validated:
  - register (including expected token-required failure path)
  - trigger scan
  - poll scan status
  - fetch content scores
- Edge endpoint migration validated:
  - customer/admin API traffic now fronts through CloudFront (`api_frontdoor_base_url`) instead of direct API Gateway URL in plugin settings.
  - plugin default API base URL constant now points to CloudFront front door.
- Added Content Scores detail drill-down UX with:
  - category-score breakdown
  - prioritized recommendations
  - score trend/history visibility
- Added Phase 5 remediation CTA wiring in customer plugin:
  - `Refresh remediation preview`
  - `Yes, make SEO changes`
  - admin handler/notice flows for remediation preview/apply outcomes

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
- Added tier-aware scan policy and layered metadata path:
  - `POST /v1/sites/{site_id}/scans` now resolves scan access policy (`basic` vs `premium`) from entitlement state, while still blocking suspended accounts
  - scan runs/snapshots now persist tier/layer metadata (`scan_tier`, executed layers, premium-locked layers)
  - scan/status and content-score responses now expose tier/layer metadata for plugin UX
- Added durable scan persistence for API consumers:
  - scan runs table (`icap-seo-production-scan-runs`)
  - content score snapshots table (`icap-seo-production-content-score-snapshots`, GSI `by_site_scan`)
  - history-capable content score detail responses
- Applied and verified these infrastructure changes via GitHub workflow path:
  - merged workflow artifact fix PR `infrastructure#46`
  - successful workflow apply run: `Terraform Plan / Approval / Apply` (`workflow_dispatch`)
- Endpoint hardening pass shipped and validated:
  - stricter Lambda route matching and path/input validation in `environments/icap-seo-production/lambda_src/handler.py`
  - API Gateway route throttling enabled on default/admin/webhook routes
  - CloudFront front-door distribution added for iCap SEO API with CloudFront-scoped WAF managed rules + per-IP rate limiting
  - edge smoke checks validated expected auth behavior via CloudFront endpoint
- Added Phase 5 remediation backend MVP routes in Lambda:
  - `POST /v1/sites/{site_id}/content-scores/{content_key}/remediation-preview`
  - `POST /v1/sites/{site_id}/content-scores/{content_key}/apply-remediation`
- Added missing API Gateway route resources for remediation endpoints and merged/deployed the fix (`infrastructure#70`) to remove live 404 route misses.

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
- Release ZIP automation is active and latest release line is `v0.2.8`.
- CloudFront endpoint migration checkpoint validated in live WP CC smoke actions:
  - Tenants view, Billing summary view, and Billing Resync action successful after endpoint change.

## Where we are left off
### Current technical state
- Plugin path is functional for registration + scan + score retrieval.
- Setup Wizard now includes connection-test diagnostics for pre-registration validation and credential troubleshooting.
- AWS backend path is provisioned and reachable.
- Terraform workflow pipeline is operational again after parser regression fixes.
- Claude summary integration is restored with parser-safe syntax.
- Control-center private repo is active with baseline admin operations shipped.
- Plugin entitlement UX and backend entitlement enforcement are now aligned and merged.
- Stripe checkout/portal session APIs and webhook-driven entitlement transitions are implemented.
- Stripe webhook endpoint/signing-secret flow is configured and validated for current environment.
- EventBridge → SNS activity notifications are active with readable multiline email formatting.
- Scan API routes are profile-driven and persist run/history data in DynamoDB-backed tables.
- Tiered scan-layer rollout is implemented, merged, and validated in live scan execution records.
- Infrastructure is live-synced to AWS for iCap SEO and production environments.
- Terraform no-op workflow behavior now skips manual approval/apply correctly when plans are true no-op.
- Endpoint hardening rollout is complete; route/auth/edge controls plus focused regression checks are now in place.
- Workflow-based infrastructure apply has been validated for this deployment line.
- End-to-end architecture/workflow documentation is now captured in `docs/architecture.md`.
- Live endpoint verification confirms remediation routes now resolve through CloudFront and return authenticated API responses (401 when token is missing/invalid), replacing prior edge-layer 404 behavior.

### Current product state
- Public landing page exists, but broader product marketing/documentation expansion is still pending.
- No single "launch checklist" has been completed yet across plugin + backend + website content.
- Canonical onboarding flow now lives in `iCap-SEO/docs/customer-onboarding.md`.
- Self-serve paid signup/subscription-management API flow is shipped; customer portal auth/role hardening and live transition validation remain pending.
- Architecture decision: keep customer plugin in public `iCap-SEO` and move iCapSolutions admin/control-center tooling to separate private `iCap-SEO-control-center`, sharing common backend endpoint contracts.

## Session wrap-up checkpoint (2026-07-24)
- Tiered scan-layer rollout shipped and merged across core SEO repos:
  - `iCap-SEO#22`
  - `infrastructure#48`
  - `seo-tools#14`
- Live AWS-to-Terraform reconciliation shipped and merged:
  - `infrastructure#62`
- Terraform no-op workflow fallthrough fixes shipped and merged:
  - `infrastructure#64`
  - `infrastructure#66`
- Current implementation stop point is backend endpoint hardening; hardening code changes are the next net-new engineering phase.
## Session wrap-up checkpoint (2026-07-26)
- Endpoint hardening rollout merged and deployed in active environment:
  - strict route/path validation and payload-size guard in Lambda handler
  - API Gateway throttling controls enabled
  - CloudFront API front door + CloudFront-scoped WAF deployed
- Plugin release rollout for endpoint migration completed:
  - `iCap SEO` `v0.1.11` ZIP installed on Glamourpuss
  - `iCap SEO Control Center` `v0.2.8` ZIP installed on WP CC
- Live validation status:
  - Glamourpuss full scan trigger successful on CloudFront endpoint
  - WP CC Tenants/Billing/Resync flows successful on CloudFront endpoint
## Session wrap-up checkpoint (2026-07-28)
- Phase 5 remediation wiring shipped in customer plugin and merged:
  - `iCap-SEO#29`
  - plugin release line bumped and packaged as `v0.1.13`
- Root-cause/fix for remediation 404 completed:
  - Lambda already had remediation handlers
  - API Gateway was missing explicit remediation route resources
  - route resources added/merged in `infrastructure#70` and applied
- Live post-fix validation:
  - remediation routes resolve through CloudFront
  - unauthenticated calls return expected auth errors (not route-not-found)
- Current stop point:
  - remediation preview/apply CTA wiring is complete
  - actual WordPress content mutation workflow remains pending (queue/accepted response only today)

## Phased roadmap snapshot
1. **Phase 1 — Foundation + registration flow**: `Done`
   - Customer plugin registration, API connectivity, baseline scan/status/content score flow.
2. **Phase 2 — Billing + entitlement-aware scan tiers**: `Done`
   - Stripe billing/webhook path, entitlement gating, `basic` vs `premium` scan-layer behavior.
3. **Phase 3 — Infra convergence + release safety**: `Done`
   - Live env Terraform sync and CI no-op merge-flow reliability fixes.
4. **Phase 4 — Exposed-endpoint hardening**: `Done`
   - Auth boundaries, strict input validation, abuse controls, and focused security regression coverage are implemented.
5. **Phase 5 — Scan/scoring capability expansion**: `In Progress` (**active phase now**)
   - Expand scan layers, scoring depth, and recommendation quality.
   - Remediation preview/apply contract + plugin CTA wiring are complete.
   - Real content-write execution path remains pending.
6. **Phase 6 — Productization + operations scale-out**: `Planned`
   - Monitoring/runbooks, docs IA cleanup, and rollout/support maturity.

## Highest-priority next actions
1. **Phase 4: Harden exposed endpoints (completed)**
  - Done: tightened auth/authorization boundaries, strict route matching, and input/path validation in `infrastructure/environments/icap-seo-production/lambda_src/handler.py`.
  - Done: enabled API edge throttling and CloudFront-fronted WAF protection.
  - Done: added focused regression/security test coverage and finalized runbook notes for auth/webhook abuse/failure paths.
2. **Phase 5: Expand scanning and scoring capabilities (next)**
   - Define and implement next scan-layer additions beyond the current baseline/premium split.
   - Expand score payload depth (category clarity, deltas/history, recommendation priority metadata).
   - Validate plugin rendering/UX for new scan/scoring response fields.
   - Implement remediation execution lifecycle beyond queue/accepted response:
     - persist remediation jobs
     - add confirm/status APIs
     - execute safe content updates
     - trigger/post rescan and report score deltas
3. **Website productization and docs IA cleanup (soon)**
   - Expand and reorganize `icapsolutions` content so iCap SEO has one clear user path.
   - Publish complete customer-facing setup/billing/onboarding/support documentation.
   - Keep CTA pathways explicit and linked from existing SEO/service pages.
4. **Provider control-center plugin track**
   - Continue expanding the private internal admin plugin for iCapSolutions operations.
   - Keep shared endpoint contracts synchronized while preserving strict customer/provider separation.
5. **Cross-repo roadmap sync**
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
