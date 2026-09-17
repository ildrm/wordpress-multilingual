# Requirement traceability

This matrix evaluates the supplied mission against release 0.1.0. “Partial” means a safe, tested foundation exists but the complete product workflow does not. “Missing” is explicit; no documentation or UI claims otherwise.

| Major requirement | Status | Evidence / next boundary |
| --- | --- | --- |
| Native object translation groups | Partial | Indexed groups/objects schema, repository and lookup API exist; authoring UI and per-entity adapters remain |
| String catalogue | Partial | Full catalogue/translation schema, stable registration and bulk lookup exist; discovery/cleanup UI remains |
| Translation memory | Partial | Schema plus exact/fuzzy confidence and no-auto-approve invariant; indexed search/service UI remains |
| Source change detection | Partial | Fingerprints, source revisions, versions and stale status exist; block/segment diff pipeline remains |
| Language management | Partial | Custom/regional tags, locale, direction, visibility, ordering, fallback persistence; locale format editing UI remains |
| Default-language safety | Implemented | Explicit acknowledgement, enabled/site validation, transaction, one-time rewrite flush |
| Directory routing | Partial | Directory rewrite/URL resolution; subdomain, mapped-domain and translated bases remain |
| Missing-translation policies | Partial | Switcher hides missing and hreflang omits missing; configurable policy engine remains |
| Hreflang | Partial | Published group equivalents and x-default; archive/pagination diagnostics remain |
| Technical SEO/adapters | Missing | Requires separate Yoast/Rank Math/SEOPress/AIOSEO module specs and fixtures |
| Language switcher | Partial | Dynamic block, shortcode and PHP API; menu/widget/floating visual designer remains |
| Visitor detection | Missing | Requires privacy/cookie UX and redirect-loop test matrix |
| Content/custom-field coverage | Partial | Generic native relationship model; policy registry and authoring synchronization remain |
| Gutenberg/Site Editor | Missing | Requires block segmentation/round-trip validity module |
| Page builders | Missing | Requires vendor-specific public schema adapters and fixtures |
| Forms | Missing | Requires vendor-specific adapters and submission-language rules |
| Media | Partial | Native attachment model is representable; inheritance/replacement workflows remain |
| Translation editor / visual editor | Missing | Requires React workspace and secured preview protocol |
| Glossary | Partial | Indexed schema exists; enforcement service/editor remain |
| MT/AI provider system and credentials | Missing | No content leaves site; provider interface/module spec is required |
| AI quality/cost control | Partial | Placeholder and fingerprint primitives exist; provider validation/budgets remain |
| Background jobs/concurrency | Partial | Idempotent/version-aware schema; worker, retry/backoff, cancellation UI remain |
| WooCommerce/multicurrency/email | Missing | Requires HPOS/blocks/order-context integration suite |
| User language preferences | Missing | Separate site/communication preference model remains |
| Workflow roles | Partial | Dedicated capabilities and translator/reviewer roles; per-language restrictions remain |
| Dashboard/setup wizard | Partial | Actionable dashboard/language flow exists; guided resumable wizard remains |
| Design/accessibility | Partial | Tokens, focus, semantic forms/tables, RTL/logical CSS, reduced motion; scenario audit remains |
| Performance/cache | Partial | Bounded bulk queries/selective caches/no front scans; reproducible large-site benchmarks remain |
| Database/migrations | Implemented | Ten owned tables, constraints/indexes, schema version and idempotent dbDelta migration |
| REST/PHP/WP-CLI | Partial | Core read/config/health/migrate APIs exist; request/queue/glossary/import/export commands remain |
| GraphQL | Missing | Optional WPGraphQL adapter remains |
| Import/export/competitor migration | Missing | Requires dry-run parsers, checkpointing and fixtures |
| Multisite | Partial | Shared network tables with site identity, network activation/new-site seeding; federation remains |
| Privacy/security/audit | Partial | No external flow, capabilities/nonces/permission callbacks, prepared SQL, escaping, redaction; dynamic penetration suite remains |
| Diagnostics/support package | Partial | Schema/cache/queue health; sanitized support package and broad integration diagnostics remain |
| Search/comments/scheduling/revisions/deletion | Partial | Native object design preserves WordPress behavior; language-aware policies/tests remain |
| Uninstall/data retention | Implemented | Keep-by-default; explicit constant required; native content never deleted |
| Integration registry/compatibility matrix | Missing | Module registry and vendor CI matrix remain |
| Test layers/E2E/visual/performance/security | Partial | Domain PHPUnit suite plus smoke runner; WordPress integration, Playwright, visual, load, security suites remain |
| Release package | Implemented | Deterministic runtime allowlist ZIP; clean WordPress 6.9.4 install/activate/deactivate/reactivate verified |

The largest limitation is not a hidden defect but release scope: the supplied mission describes a mature product comparable to several multi-year commercial platforms. Release 0.1.0 is a coherent foundation, not that final market-complete product.
