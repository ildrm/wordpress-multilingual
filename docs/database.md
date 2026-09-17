# Database schema

All tables use the WordPress network base prefix and carry `blog_id` where site ownership matters. This permits normal single-site/multisite operation without duplicating global relationship infrastructure. Foreign keys are intentionally avoided because WordPress installations and `dbDelta()` cannot rely on uniform foreign-key support; services validate ownership and health checks detect orphans.

| Table suffix | Purpose | Critical indexes |
| --- | --- | --- |
| `mlc_languages` | Language configuration | unique `(blog_id, tag)`, enabled/public/order |
| `mlc_translation_groups` | Source identity | unique site/type/source identity |
| `mlc_translation_objects` | Group membership/status/version | unique object identity and group/language |
| `mlc_strings` | Stable string catalogue | unique stable hash, domain/activity, source hash |
| `mlc_string_translations` | Versioned string values | unique string/language, language/status |
| `mlc_translation_memory` | Reusable segments | exact hash/language/approval, language pair |
| `mlc_glossary` | Terminology constraints | site/language pair, source prefix |
| `mlc_translated_slugs` | Route-scoped slugs | unique object/language and route/language/slug |
| `mlc_jobs` | Idempotent async work records | unique request key, runnable state/time |
| `mlc_audit_log` | Redacted important events | site/event/time and object identity |

Schema version is stored as network option `mlc_schema_version`. Activation and `wp multilingual-core migrate` execute idempotent `dbDelta()` statements. Future changes must be additive/incremental and update `MLC_SCHEMA_VERSION`; large backfills belong in resumable jobs rather than activation.

Hot-path queries select named columns, use composite indexes, and batch `IN` lookups. Dynamic values use `$wpdb->prepare()`; table names derive only from the trusted WordPress base prefix plus fixed plugin suffixes.

