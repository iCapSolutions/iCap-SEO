# Hybrid scoring API and data flow design
## Objective
Define the API contract and runtime data flow between the `icap-seo` WordPress plugin and an AWS backend for hybrid SEO scoring:
- Lightweight checks and UX in plugin.
- Heavy scoring pipeline in AWS.
- Asynchronous scan model with fast read APIs for WP admin views.

## Scope
This design covers:
- Plugin-to-backend API contract (v1).
- Authentication and tenancy model.
- Stripe-based subscription billing and entitlements.
- Async scan orchestration and score retrieval.
- Data model for site/page/post score snapshots.
- AWS services used for implementation.

This design does not cover:
- Full WordPress.org distribution requirements.
- Multi-region disaster recovery design.

## High-level architecture
- WordPress plugin (`icap-seo`)
  - Setup Wizard registers site and stores site token/id.
  - Triggers scans manually or on schedule.
  - Reads score summaries for:
    - Plugin `Content Scores` tab.
    - Posts/Pages list columns.
- AWS API layer
  - Validates token and site ownership.
  - Accepts scan requests and exposes scan/score read endpoints.
- Async scoring pipeline
  - Crawl and content extraction.
  - Technical checks and scoring.
  - Optional external signals (PageSpeed, GSC, Rank Math comparison inputs).
  - Persists score snapshots and issue details.

## Core design choices
1. Async-first scoring
   - Scans are queued and processed in background.
   - Plugin never blocks admin UI waiting on full analysis.
2. Snapshot reads
   - Plugin reads latest completed score snapshot.
   - Scan status endpoint provides progress for UI polling.
3. Stable content keys
   - Each post/page is tracked by `content_key`:
     - `site_id + post_id + post_type`.
4. Category-weighted scoring
   - Backend computes normalized `overall_score` (0-100).
   - Category scores (on-page, technical, content, performance, visibility) returned with issue list.

## API contract (v1)
Base path: `/v1`
Response envelope pattern:
- `success`: boolean
- `data`: object (on success)
- `error`: object with `code`, `message` (on failure)

### 1) Register site
Endpoint:
- `POST /v1/sites/register`

Request fields:
- `site_url` (required)
- `wp_version` (optional)
- `plugin_version` (required)
- `site_name` (optional)
- `timezone` (optional)

Success response:
- `site_id`
- `site_token` (write once; plugin stores encrypted in WP option)
- `api_base_url`
- `capabilities` (feature flags, enabled scoring categories)

Notes:
- If site already exists, backend may return existing `site_id` with rotated token depending on policy.

### 2) Trigger scan
Endpoint:
- `POST /v1/sites/{site_id}/scans`

Request fields:
- `scan_type` (`full_site` | `content_subset` | `single_content`)
- `content_ids` (optional list of WP IDs for subset mode)
- `force_refresh` (optional boolean)
- `requested_by` (`manual` | `scheduled` | `auto`)

Success response:
- `scan_id`
- `status` (`queued`)
- `queued_at`
- `estimated_completion_seconds` (optional)

### 3) Get scan status
Endpoint:
- `GET /v1/sites/{site_id}/scans/{scan_id}`

Success response:
- `scan_id`
- `status` (`queued` | `running` | `completed` | `failed`)
- `progress_percent`
- `started_at`
- `completed_at`
- `summary`:
  - `content_processed`
  - `issues_found`
  - `average_score`

### 4) Get content score list
Endpoint:
- `GET /v1/sites/{site_id}/content-scores`

Query params:
- `post_type` (`post` | `page` | `all`)
- `status` (optional WP status filter)
- `updated_since` (optional timestamp)
- `limit` / `cursor` for pagination

Success response:
- `items` list, each item includes:
  - `content_key`
  - `wp_post_id`
  - `post_type`
  - `title`
  - `permalink`
  - `overall_score`
  - `rank_math_score` (optional when available)
  - `delta_vs_rank_math` (optional numeric)
  - `last_scored_at`
  - `scan_id`
- `next_cursor`

### 5) Get single content score details
Endpoint:
- `GET /v1/sites/{site_id}/content-scores/{content_key}`

Success response:
- Score summary:
  - `overall_score`
  - `rank_math_score` (optional)
  - `delta_vs_rank_math` (optional)
- Category scores:
  - `on_page`
  - `technical`
  - `content_quality`
  - `performance`
  - `visibility`
- `issues` list:
  - `issue_code`
  - `severity` (`critical` | `high` | `medium` | `low`)
  - `description`
  - `recommended_fix`
  - `estimated_effort`
- `history` (optional compact timeseries)

### 6) Get subscription status
Endpoint:
- `GET /v1/billing/subscription-status`

Success response:
- `entitlement_state` (`trialing` | `active` | `past_due` | `grace_period` | `canceled` | `suspended`)
- `plan_code`
- `currency`
- `billing_country`
- `grace_expires_at` (optional)
- `updated_at`

## Authentication and authorization
Header model:
- `Authorization: Bearer <site_token>`
- `X-ICAP-Site-Id: <site_id>`
- `X-ICAP-Plugin-Version: <version>`

Controls:
- Token scoped to one `site_id`.
- Tokens stored in AWS Secrets Manager or encrypted DynamoDB attribute.
- Rotation endpoint can be added in v1.1 (`POST /v1/sites/{site_id}/token/rotate`).
- API Gateway usage plans and WAF rate limiting per site token.

## Customer auth and billing (Stripe-first)
### Human auth vs machine auth
Keep machine auth separate from customer admin auth:
- **Machine auth**: `site_token` used by WordPress plugin.
- **Human auth**: customer portal login (recommended: Cognito) with MFA for tenant admins.

Plugin token permissions:
- Trigger scans for its `site_id`.
- Read scores for its `site_id`.
- No access to billing/customer profile APIs.

Customer portal permissions:
- Start/stop subscription.
- View payment status.
- Rotate plugin tokens.
- Manage connected sites under one tenant.

### Stripe billing decision
Billing provider: **Stripe Billing**.

Why:
- Fastest recurring billing implementation.
- Strong webhook model for entitlement control.
- Low fixed startup cost model (no mandatory monthly platform fee; transaction fees apply).

### US-only and USD-only policy
Business policy:
- US customers only.
- USD currency only.

Enforcement:
1. Only create Stripe prices in USD.
2. On checkout session creation, require billing country `US`.
3. Persist `billing_country` and `currency` in tenant profile.
4. Reject entitlement activation if policy checks fail.

### Entitlement state model
Canonical tenant states:
- `trialing`
- `active`
- `past_due`
- `grace_period`
- `canceled`
- `suspended`

Recommended API behavior by state:
- `trialing`/`active`: full read + scan trigger access.
- `past_due`: read allowed, scan trigger blocked.
- `grace_period`: read allowed, limited trigger policy (optional).
- `canceled`/`suspended`: read optional, scan trigger blocked.

### Stripe webhook integration
Webhook endpoint:
- `POST /v1/billing/webhooks/stripe`

Handle events:
- `checkout.session.completed`
- `customer.subscription.created`
- `customer.subscription.updated`
- `customer.subscription.deleted`
- `invoice.paid`
- `invoice.payment_failed`

Requirements:
- Verify Stripe signature.
- Idempotent processing by event ID.
- Persist webhook audit trail.
- Update tenant entitlement state atomically.

### Portal-side billing API (not plugin API)
Suggested endpoints:
- `POST /v1/billing/checkout-session`
- `POST /v1/billing/portal-session`
- `GET /v1/billing/subscription-status`

## Data model (logical)
### Site profile
- `site_id` (PK)
- `tenant_id`
- `site_url`
- `token_hash`
- `plugin_version`
- `created_at`, `updated_at`
- `last_seen_at`

### Scan job
- `scan_id` (PK)
- `site_id` (GSI)
- `status`
- `scan_type`
- `requested_by`
- `queued_at`, `started_at`, `completed_at`
- `summary`

### Content score snapshot
- `site_id` + `content_key` (composite key)
- `wp_post_id`
- `post_type`
- `title`
- `permalink`
- `overall_score`
- `rank_math_score` (nullable)
- `delta_vs_rank_math` (nullable)
- `category_scores` object
- `issues` array (or pointer to detail table if large)
- `scan_id`
- `last_scored_at`

### Score history (optional in v1)
- Time-series table keyed by `site_id + content_key + scored_at`.

### Tenant billing profile
- `tenant_id` (PK)
- `stripe_customer_id`
- `stripe_subscription_id`
- `plan_code`
- `currency` (`USD`)
- `billing_country` (`US`)
- `entitlement_state`
- `grace_expires_at` (nullable)
- `updated_at`

## AWS implementation mapping
- API Gateway
  - REST endpoints under `/v1`.
- Lambda (API handlers)
  - Site registration, scan trigger, scan status, score reads.
- SQS
  - `scan-request-queue` for background jobs.
- Step Functions
  - Orchestrates crawl -> technical checks -> external signals -> aggregate -> persist.
- Lambda or Fargate workers
  - Crawl/parsing and scoring stages.
- DynamoDB
  - `site_profiles`, `scan_jobs`, `content_scores`, optional `score_history`.
- EventBridge Scheduler
  - Periodic rescans per site policy.
- CloudWatch + X-Ray
  - Logs, metrics, traces, error alarms.
- Secrets Manager
  - Optional per-site secret material and provider credentials.

## End-to-end data flow
1. Setup
   - Plugin Setup Wizard calls `POST /sites/register`.
   - Saves `site_id` and `site_token` in WP options.
2. Scan trigger
   - Admin action (or scheduled event) calls `POST /sites/{site_id}/scans`.
   - API writes scan job and enqueues work.
3. Processing
   - Worker pipeline analyzes content and computes scores/issues.
   - Snapshot rows updated per content item.
   - Scan job marked `completed`.
4. UI refresh
   - Plugin polls `GET /scans/{scan_id}` for progress.
   - Plugin retrieves list via `GET /content-scores`.
   - Renders:
     - WP Posts/Pages columns (`iCap Score`, `delta`).
     - `Content Scores` tab table.

## Plugin behavior requirements
1. Fallback strategy
   - If API unreachable, keep previous snapshot values and show stale indicator.
   - If no snapshot exists, show `Pending`.
2. Caching
   - Cache list endpoint results for short TTL (for example 60-120 seconds) using transients.
3. Timeout budget
   - API calls in admin requests should use strict timeout (for example 2-3 seconds).
4. Capability checks
   - Only users with `manage_options` can configure API connection.
5. Safe rendering
   - Always escape API-returned fields before output.
6. Billing status checks
   - Expose a Settings action that calls `GET /v1/billing/subscription-status`.
   - Persist `last_billing_state` and `last_billing_checked_at` in plugin settings.
7. Entitlement-aware scan UX
   - When scan trigger returns `payment_required`, `subscription_required`, or `account_suspended`, show a specific recovery notice and do not queue a scan.

## Error model
Standard error codes:
- `invalid_token`
- `site_not_found`
- `scan_not_found`
- `rate_limited`
- `validation_error`
- `upstream_unavailable`
- `payment_required`
- `subscription_required`
- `account_suspended`

Plugin UX handling:
- `invalid_token`: show reconnect action in Setup Wizard.
- `rate_limited`: show retry-after message.
- `upstream_unavailable`: keep last known values and show warning badge.
- `payment_required`: show billing recovery notice in plugin settings.
- `subscription_required`: show plan activation guidance before retrying scans.
- `account_suspended`: show account restoration/support guidance before retrying scans.

## Scoring approach (v1)
Category weights (initial default):
- On-page: 30
- Technical: 25
- Content quality: 20
- Performance: 15
- Visibility: 10

Output:
- `overall_score = weighted_sum(category_scores)`
- Issue severities drive recommendation priority and ordering.

## Rollout phases
Phase 1:
- Implement registration, scan trigger, scan status, content score list.
- Replace placeholder scores in plugin with API data + fallback.

Phase 2:
- Add content detail endpoint and issue drill-down UI.
- Add scheduled scans and score history trend lines.

Phase 3:
- Add recommendation generation and remediation workflows.
- Add tenant-level analytics across multiple client sites.
