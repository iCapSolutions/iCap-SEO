# iCap SEO architecture (foundation)
## Objective
Provide a lightweight WordPress plugin that renders onboarding and site SEO status while delegating heavy analysis and recommendation generation to cloud services.

## System components
- **WordPress plugin (`icap-seo`)**
  - Admin UI
  - Local settings/options
  - API client adapter (stubbed in v0)
- **Cloud API (planned)**
  - Site registration
  - Score retrieval
  - Recommendation retrieval
- **Async workers (planned)**
  - Crawl/analysis jobs
  - AI enrichment
  - Report generation
- **Data store (planned)**
  - Site profile
  - Scan history
  - Score snapshots
  - Recommendation artifacts

## Data flow (target)
1. Plugin setup wizard captures site config and connects to cloud API.
2. Cloud service queues site scans and SEO analysis jobs.
3. Workers compute scores and recommendations.
4. Plugin pulls score summaries and displays results on dashboard tabs.

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

## Detailed design docs
- Hybrid scoring API/data flow: `docs/hybrid-scoring-api-design.md`
- Infrastructure separation strategy: `docs/infrastructure-separation-design.md`
