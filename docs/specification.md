# Multilingual Core — Foundation Specification

**Status:** Approved (the user-supplied mission is the authoritative product specification)  
**Version:** 0.1.0  
**Date:** 2026-09-17  
**Target:** WordPress 6.9+, PHP 8.1+

## Context

Multilingual Core is a vendor-neutral multilingual platform for WordPress. Addressable translations remain native WordPress objects; language, relationship, string, memory, glossary, slug, audit, and job metadata use purpose-built relational tables. The design prioritizes bounded front-end overhead, data integrity, accessibility, security, and extension through stable APIs.

This foundation release establishes the invariants on which editors, provider adapters, migrations, commerce, and compatibility modules depend. It is deliberately honest about release coverage: capabilities not listed in this contract are not represented as complete in the UI or documentation.

## Functional requirements

- **FR-1** The plugin MUST install versioned, multisite-safe tables with unique constraints for languages, translation objects, string translations, translated slugs, and idempotent jobs.
- **FR-2** Administrators MUST be able to create, update, enable, order, and select languages with BCP-47-style tags, locale, script, direction, and optional fallback.
- **FR-3** Exactly one enabled language per site MUST be the default; default-language changes MUST require an explicit impact acknowledgement.
- **FR-4** Public WordPress objects MUST be assignable to one translation group without duplicate language membership.
- **FR-5** Object-language and translation lookups MUST be bulk-capable and cacheable without requiring persistent object cache.
- **FR-6** Directory routing MUST resolve configured language prefixes and generate translated URLs without flushing rules during requests.
- **FR-7** The front end MUST emit only valid, published reciprocal alternates and an optional `x-default` link.
- **FR-8** Registered strings MUST have stable hashes and bulk translation lookup; visitor requests MUST NOT scan arbitrary rendered output.
- **FR-9** Translation memory MUST distinguish exact from fuzzy suggestions; fuzzy suggestions MUST NOT be treated as approved automatically.
- **FR-10** REST mutations MUST enforce dedicated capabilities, validation, and explicit permission callbacks.
- **FR-11** Stable PHP APIs and WP-CLI read/diagnostic commands MUST avoid requiring consumers to query internal tables.
- **FR-12** Deactivation MUST preserve data; uninstall MUST preserve data unless `MLC_REMOVE_DATA_ON_UNINSTALL` is explicitly true.
- **FR-13** Important mutations MUST create redacted audit records.
- **FR-14** The admin UI MUST be task-oriented, keyboard accessible, internationalized, and RTL compatible.
- **FR-15** The production package MUST exclude tests, development configuration, and temporary artifacts.

## Non-functional requirements

- **NFR-1 Performance:** a warm object-language lookup SHOULD require zero SQL queries; cold bulk lookups MUST use a bounded query count independent of object count.
- **NFR-2 Security:** all mutations MUST combine capability authorization with CSRF protection appropriate to their transport; SQL MUST be prepared; output MUST be escaped.
- **NFR-3 Integrity:** uniqueness MUST be enforced in the database, not only in UI code; concurrent writes MUST fail deterministically.
- **NFR-4 Accessibility:** primary admin controls MUST satisfy WCAG 2.2 AA keyboard, labeling, focus, contrast, and reflow expectations.
- **NFR-5 Privacy:** no content or credentials MAY leave WordPress unless a separately enabled provider module is invoked with explicit content scope.
- **NFR-6 Resilience:** cache loss MUST affect performance only, never correctness.

## Acceptance criteria

- **AC-1 (FR-1, NFR-3):** Given two writes for the same site/tag or group/language, when both reach the database, then at most one succeeds.
- **AC-2 (FR-2):** Given a valid custom language, when an administrator saves it, then it is returned by PHP and REST APIs with tag, locale, direction, and availability intact.
- **AC-3 (FR-3):** Given content exists, when default language change lacks acknowledgement, then the mutation fails without changing routing.
- **AC-4 (FR-4, FR-5):** Given translated posts in one group, when their IDs are resolved in bulk, then one bounded repository query supplies the complete mapping.
- **AC-5 (FR-6):** Given `/fa/about/`, when Persian is enabled, then current language resolves to `fa` and generated Persian URLs contain one prefix.
- **AC-6 (FR-7):** Given a missing or unpublished target, when alternates render, then no hreflang link is emitted for it.
- **AC-7 (FR-8):** Given registered strings and translations, when bulk lookup runs, then it performs no query per string.
- **AC-8 (FR-9):** Given a near match below exact confidence, when memory suggestions are returned, then the suggestion includes confidence and is not approved.
- **AC-9 (FR-10):** Given an unauthenticated or underprivileged request, when a private REST route is called, then WordPress returns an authorization error.
- **AC-10 (FR-12):** Given normal deactivation/uninstall, when removal opt-in is absent, then native content and plugin tables remain.
- **AC-11 (FR-14, NFR-4):** Given keyboard-only navigation in either LTR or RTL admin, when focus traverses language management, then every action remains operable and visibly focused.
- **AC-12 (FR-15):** Given a release build, when its ZIP is listed, then it contains runtime dependencies and excludes tests, node modules, source maps, and local files.

## Edge cases

- **EC-1:** A disabled or unknown URL prefix falls back to the configured default without redirecting.
- **EC-2:** A fallback cycle is rejected before persistence.
- **EC-3:** A translated slug collision returns a conflict and leaves the existing mapping intact.
- **EC-4:** A source object deleted during a relationship write causes the write to fail safely.
- **EC-5:** Redis/object-cache disappearance causes repository fallback and cache repopulation.
- **EC-6:** Interrupted schema upgrades resume from the recorded schema version.
- **EC-7:** A stale background result cannot overwrite a newer source fingerprint or target version.
- **EC-8:** Invalid domain settings and cross-origin redirects are rejected rather than normalized permissively.

## API contracts

```ts
type Language = {
  id: number; tag: string; locale: string; code: string; nativeName: string;
  adminName: string; region: string | null; script: string | null;
  direction: 'ltr' | 'rtl'; fallbackTag: string | null;
  enabled: boolean; public: boolean; isDefault: boolean; position: number;
};

type TranslationMap = Record<string, { objectId: number; status: TranslationStatus }>;
type TranslationStatus = 'untranslated' | 'draft' | 'machine_translated' |
  'translated' | 'needs_review' | 'approved' | 'stale' | 'failed' |
  'intentionally_excluded';
```

REST base: `/wp-json/multilingual-core/v1`. Public language listing exposes enabled public languages only. All object relationship, language mutation, health, and translation operations use explicit capability callbacks.

## Data models

| Entity | Essential constraints |
| --- | --- |
| Language | Unique `(blog_id, tag)`, one logical default per site, indexed enabled/order |
| Translation group | Object type/subtype and source identity, indexed source |
| Translation object | Unique `(blog_id, object_type, object_id)` and `(group_id, language_id)` |
| String | Unique stable hash; domain/context/origin/type lifecycle metadata |
| String translation | Unique `(string_id, language_id)`, versioned status and reviewer |
| Translation memory | Indexed source hash/language pair/status; confidence returned by query |
| Glossary entry | Indexed language pair; case/word/required/prohibited controls |
| Translated slug | Unique object-language and route-language slug constraints |
| Job | Unique idempotency key; version/fingerprint and retry state |
| Audit event | Bounded structured context without secrets or translated content |

See `docs/database.md` for physical schema and query shapes.

## Out of scope for foundation release

Provider adapters, the visual editor, builder/form/SEO-specific adapters, WooCommerce and multicurrency modules, federation mode, format importers, migrations from competing products, and the React translation workspace require independent approved module specifications and test matrices. Their extension seams are part of this foundation; they are not claimed as implemented.

