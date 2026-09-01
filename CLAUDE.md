# iCap-SEO

Customer-facing WordPress plugin. **This repo is public** — `README.md` and `docs/*` must stay product-only:
what it does, how to install (GitHub Release ZIP only), a checks catalog, a getting-started guide. No internal
architecture, AWS/infra detail, real client/customer names, roadmap docs, or admissions of past bugs/gaps. Internal
engineering notes belong in the private `iCap-SEO-control-center` repo instead.

## Security is a standing priority, not follow-up work

This plugin registers real customer sites and holds credentials (registration token, site token) against a
billing-connected backend. When building or reviewing code here:

- Don't describe internal implementation details in customer-facing docs (`README.md`, `docs/*`), but do keep
  the customer-facing behavior accurate — e.g. registration tokens are single-use and site-specific, so
  onboarding docs should say so plainly rather than implying a token can be reused or shared.
- Check `docs/security-hardening-notes.md` in `iCap-SEO-control-center` (private) before security-adjacent work
  — it's the cross-repo running log of hardening layers and incidents, including on the backend this plugin
  talks to.
- Nonce checks, capability checks, and input sanitization/output escaping are expected on every admin action
  this plugin adds, not just the ones that look risky at a glance.
