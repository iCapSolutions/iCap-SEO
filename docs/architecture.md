# iCap SEO architecture (foundation)
## Objective
Provide a lightweight customer-facing WordPress plugin plus a separate provider control-center plugin in a private repository, both backed by shared API contracts and cloud services for scoring, onboarding, and billing.
## System components
- **Customer WordPress plugin (`icap-seo`)**
  - Admin UI (Home, Setup Wizard, Site Health, Content Scores, Settings)
  - Local connection settings (`api_base_url`, `registration_token`, `site_id`, `site_token`)
  - API client adapter for registration, scan trigger/status, and content score reads
  - Fallback-safe rendering when API data is unavailable
- **Provider control-center plugin (`icap-seo-control-center`, private repo)**
  - Maintained in a separate private repository from `iCap-SEO`
  - Installed only on the iCapSolutions site
  - Internal/admin operational views for customer lifecycle and billing state
  - Current baseline includes read-only tenant/billing views plus guarded billing resync action with audit logging
  - Uses shared API endpoint contracts and common client logic where possible
- **Cloud API (in-progress scaffold)**
  - Site registration
  - Scan trigger + status retrieval
  - Content score list/detail retrieval
  - Billing and entitlement endpoints (planned)
  - Admin/control-plane endpoints (planned)
- **Async workers (planned)**
  - Crawl/analysis jobs
  - AI enrichment
  - Report generation
- **Data store (planned)**
  - Site profile
  - Scan history
  - Score snapshots
  - Tenant billing profile
  - Recommendation artifacts
## Data flow (current + target)
1. Plugin Settings captures API base URL plus registration token and Setup Wizard requests registration.
2. Cloud API returns site credentials (`site_id`, `site_token`) to plugin settings.
3. Plugin triggers scans; backend accepts jobs and tracks scan status.
4. Plugin polls status and fetches score summaries for dashboard/list views.
5. Control-center plugin uses admin endpoints to monitor tenants, billing, and operational status.
6. Workers (planned expansion) compute deeper recommendations and persisted history.
## AWS-first target stack (planned)
- API Gateway + Lambda for API endpoints.
- SQS/EventBridge for async orchestration.
- DynamoDB or Aurora Serverless for persisted scan/site data.
- Secrets Manager + IAM roles for secure credentials.
- Optional Bedrock/LLM provider abstraction for recommendation engine.
## Security baseline
- WordPress capability checks on all admin routes/actions.
- Nonce protection for form actions.
- Sanitize input and escape output in all admin views.
- Keep secrets out of plugin code and repository history.
## Current known gaps
- Scan and score endpoints are still scaffold-level and need production-grade persistence/business logic.
- Self-serve paid signup/checkout and customer billing portal flows are not yet implemented.
- Control-center/admin API depth still needs expansion beyond current baseline views and billing resync operation.
## Detailed design docs
- Customer onboarding guide: `docs/customer-onboarding.md`
- Private control-center bootstrap checklist: `docs/control-center-private-repo-bootstrap.md`
- Hybrid scoring API/data flow: `docs/hybrid-scoring-api-design.md`
- Infrastructure separation strategy: `docs/infrastructure-separation-design.md`
