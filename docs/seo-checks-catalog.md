# iCap SEO: full check catalog

Reference for every SEO check iCap SEO runs, what it means, why it matters, and how it gets fixed. This
mirrors what you'll see in the plugin's **Content Scores** tab.

## How to read this

- **Plan** — **Free** checks run in every scan. **Premium** checks require an active subscription; on a free-tier scan they still appear in the catalog but are marked as requiring an upgrade rather than showing a pass/fail result.
- **Fix** — how the issue gets resolved once found:
  - **Automatic fix** — iCap SEO writes the fix directly (title, meta description, alt text, schema markup, etc.) the moment you approve it. No manual editing required.
  - **Preview & publish** — iCap SEO drafts the fix and shows it to you first; nothing is saved or published until you explicitly approve it. Used only where auto-writing risky content (like new paragraphs) without review isn't safe.
  - **Guidance only** — iCap SEO tells you exactly what's wrong and how to fix it, but the fix itself happens outside WordPress content (server config, DNS, a manual editorial judgment call) or isn't safe to automate (e.g. fabricating a citation or a stock image).
- A check can be **"Not evaluated"** on a given page when a more fundamental issue is blocking it — e.g. structured-data property checks can't run until a structured-data block exists in the first place. This isn't a failure, it just means a prerequisite issue needs fixing first.

---

## Baseline on-page audit — Free

The foundational checks every scan runs, free or paid.

| Check | Why it matters | Fix |
|---|---|---|
| Page has a title tag | Your page's `<title>` is the headline search engines and browsers use to identify it — pages without one are effectively unlabeled. | Automatic fix |
| Title length is optimized (20–65 characters) | Titles that are too short waste the space search engines give you; titles that are too long get cut off with "…" in search results. | Automatic fix |
| Page has a meta description | This is the summary text shown under your title in search results. Without one, search engines write their own — usually a rough excerpt that doesn't sell the page. | Automatic fix |
| Meta description length is optimized (120–170 characters) | Same trade-off as title length: too short leaves value on the table, too long gets truncated. | Automatic fix |
| Page has an H1 heading | The H1 tells readers and search engines the main topic of the page at a glance. | Automatic fix |
| Page has enough visible content (250+ words) | Very thin pages give search engines little to understand or rank the page for. | Preview & publish |

## Robots and crawler policy / security headers — Premium

Technical trust and crawlability signals — whether search engines and AI crawlers can reach your pages, and whether your server sends the security signals modern browsers expect.

| Check | Why it matters | Fix |
|---|---|---|
| Page is served over HTTPS | Non-HTTPS pages are flagged "Not secure" by browsers and are disadvantaged in search rankings. | Guidance only |
| Page is reachable for scanning | If iCap SEO can't fetch the page, search engines most likely can't either. | Guidance only |
| Page has a canonical URL | Canonical tags tell search engines which URL is the "official" version of a page, avoiding duplicate-content confusion when the same content is reachable multiple ways. | Automatic fix |
| Page is not accidentally excluded from search (noindex) | A stray `noindex` tag hides a page from search results entirely — often without anyone noticing until traffic drops. | Guidance only |
| Server sends recommended security headers | Headers like HSTS and X-Frame-Options protect visitors from common attacks and are a baseline trust signal. | Guidance only |
| Site has a robots.txt file | robots.txt tells search engines — and AI crawlers — which parts of your site they're allowed to visit. Without one, crawler behavior is left to whatever each crawler assumes by default. | Guidance only |
| robots.txt does not block this page | A robots.txt rule can accidentally hide an otherwise-fine page from search engines entirely. | Guidance only |

## Content quality and readability — Premium

Whether a page has enough substance, structure, and clarity to genuinely serve a reader (and rank on the strength of that).

| Check | Why it matters | Fix |
|---|---|---|
| Page has visible content | A page that renders empty offers search engines nothing to evaluate or rank. | Preview & publish |
| Content depth meets baseline (300+ words) | Pages with very little substance rarely compete well against more thorough pages on the same topic. | Preview & publish |
| Content depth is competitive (600+ words) | For competitive topics, deeper and more complete pages tend to outperform shallow ones. | Preview & publish |
| Page has enough secondary headings (H2/H3) | Subheadings break content into scannable sections for readers and give search engines a clear outline of what the page covers. | Automatic fix |
| Page has enough paragraph structure | Long, unbroken blocks of text are harder to read and more likely to make visitors leave before finishing. | Automatic fix |
| Content is written in plain, easy-to-read language | Dense, jargon-heavy writing is harder for visitors to act on — readability affects how usable a page feels to real readers. | Preview & publish |

## Structured data schema — Premium

Whether search engines can understand exactly what a page represents — which unlocks richer search results (star ratings, article previews, and similar).

| Check | Why it matters | Fix |
|---|---|---|
| Page has JSON-LD structured data | Structured data explicitly tells search engines what kind of content a page is, instead of leaving them to guess. | Automatic fix |
| Structured data includes a valid @type | A schema block with no recognized type gives search engines nothing they can actually use. | Automatic fix |
| Structured data includes required properties for its type | An incomplete schema block — missing a headline, author, or date, for example — may be ignored by search engines entirely rather than partially credited. | Automatic fix |

## Image optimization — Premium

Whether images help or hurt page experience, accessibility, and search visibility.

| Check | Why it matters | Fix |
|---|---|---|
| Page includes relevant images | Pages with no supporting imagery tend to engage visitors less and miss out on image-search traffic entirely. | Guidance only |
| Images have descriptive alt text | Alt text is what search engines — and screen readers — use to understand an image. Without it, the image is effectively invisible to both. | Automatic fix |
| Images have explicit width/height attributes | Missing dimensions cause the page to visibly shift as images load in, hurting the page-experience metrics search engines use to judge quality. | Automatic fix |
| Below-the-fold images use lazy loading | Loading every image immediately slows down the initial page load, which affects both visitor experience and search ranking. | Automatic fix |

## Internal and broken links — Premium

Whether a page's links help visitors and search engines navigate your site — and whether any of them are dead ends.

| Check | Why it matters | Fix |
|---|---|---|
| Page includes crawlable links | Links are how search engines discover the rest of your site. A page with none is a dead end for crawlers. | Automatic fix |
| Page links to enough related content | Internal links help visitors — and search engines — find more of your relevant content instead of leaving after one page. | Automatic fix |
| Page cites at least one external source | Linking to credible outside sources is a trust signal that supports the page's claims. | Guidance only |
| Internal links resolve without errors | A broken link to your own content wastes the click and signals a poorly maintained site to visitors and search engines alike. | Guidance only |
| External references resolve without errors | A dead external link undermines the credibility of whatever you were citing and creates a poor visitor experience. | Guidance only |

---

## Free vs. Premium at a glance

- **Free tier**: the 6 baseline on-page checks above — title, meta description, H1, and basic content-depth.
- **Premium tier**: everything else — 25 additional checks across technical crawlability, content quality, structured data, images, and links.

## Fix-type summary

- **16 checks** get an automatic fix on approval.
- **5 checks** (the content-depth family, plus readability) use preview & publish — a draft is generated and shown before anything is saved.
- **10 checks** are guidance-only — either because the fix lives outside WordPress content entirely (server headers, DNS, robots.txt), or because automating it safely isn't possible (fabricating a citation, sourcing a stock image, removing a noindex tag without human confirmation).
