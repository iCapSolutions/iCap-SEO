# Infrastructure separation design
## Objective
Run iCap SEO SaaS infrastructure independently from existing personal/production services so operational risk, cost, and access controls remain isolated.

## Current context
The shared `infrastructure` repository currently manages mixed production resources (multiple domains/services) through common environment structure and modules.
For iCap SEO SaaS, introduce a dedicated isolation boundary rather than adding resources directly into existing shared stacks.

## Recommended isolation model
### 1) Account-level isolation (preferred)
Use a dedicated AWS account for iCap SEO SaaS workloads:
- `icap-seo-prod` (required)
- `icap-seo-dev` (recommended)

Benefits:
- Hard blast-radius boundary.
- Clear cost allocation and billing visibility.
- Simpler IAM least-privilege and credential management.

### 2) Terraform state isolation (required even if same account)
Keep iCap SEO Terraform state fully separate:
- Dedicated remote state bucket/prefix.
- Dedicated lock table.
- Separate CI role and state access policy.

Never share backend state keys with existing production stacks.

### 3) Network and runtime isolation
- Dedicated VPC for iCap SEO service components.
- Dedicated API Gateway, Lambda, queues, and data stores.
- No implicit dependency on existing website infrastructure components.

## Repository integration strategy
Keep infrastructure code in the existing `infrastructure` repo, but isolate by environment path and naming:
- `environments/icap-seo-prod/`
- `environments/icap-seo-dev/`

Use existing reusable modules where appropriate, but avoid sharing mutable environment state.

## Baseline stack for iCap SEO
Per environment:
- API Gateway + Lambda handlers.
- SQS queues.
- Step Functions state machine.
- DynamoDB tables (`site_profiles`, `scan_jobs`, `content_scores`, `tenant_billing`).
- EventBridge schedules.
- CloudWatch alarms and dashboards.
- Secrets Manager entries.
- Optional WAF for API Gateway.

## IAM and access boundaries
- Dedicated GitHub OIDC role for iCap SEO deploy pipeline.
- Restrict role scope to iCap SEO environment resources only.
- Separate break-glass admin role from day-to-day CI role.
- Enforce tagging policy: `Project=iCapSEO`, `Environment`, `Owner`.

## Billing and compliance controls
- Stripe webhooks and billing processors in iCap SEO account only.
- Explicit US-only / USD-only policy checks in service code.
- Cost budgets and anomaly alerts specific to iCap SEO account.

## Migration and rollout plan
Phase 1:
- Create `icap-seo-dev` infra environment and validate end-to-end API + queue + scoring flow.

Phase 2:
- Create `icap-seo-prod` environment with identical module topology.
- Enable customer onboarding in production.

Phase 3:
- Add cross-account observability aggregation if needed (optional).

## Non-goals
- Migrating existing production/personal website workloads into iCap SEO account.
- Sharing tenant data stores with existing infrastructure-managed applications.
