# Release 0.1.0 final report

## Architecture

The release uses native WordPress objects plus indexed relational metadata. Domain logic is WordPress-independent; application services enforce invariants; repository/cache contracts isolate persistence; presentation adapters handle admin, REST, front end, and CLI. The structure avoids provider calls, discovery scans, and admin assets on normal front-end requests.

## Implemented features

Language configuration, safe default changes, multisite-aware translation groups/objects, registered-string catalogue and bulk lookups, translation-memory/glossary/job/audit schemas, directory routing, same-origin URL generation, published-only hreflang, accessible block/shortcode/PHP switcher, capability-separated roles, dashboard/health screens, REST/PHP/WP-CLI APIs, explicit retention, and deterministic packaging.

## Database

Ten tables are installed with primary keys, composite lookup indexes, and uniqueness for site/tag, source identity, object identity, group/language, string/language, route slug, and idempotency key. `dbDelta()` migrations are versioned by `mlc_schema_version`. Large future backfills must be resumable jobs.

## UI/UX

The PHP-rendered admin is intentionally small and task-focused. Logical CSS supports RTL, responsive tables, visible focus, reduced motion, and status indicators that do not rely only on color. The dynamic switcher block uses WordPress editor components and server-side front-end rendering.

## Performance

Cold object/string lookups are batched; warm maps use selective WordPress object-cache groups. Admin code and assets are conditional, and simple switcher CSS is loaded only when rendered. No large-dataset benchmark claim is made because the 100k-object/million-string fixture is not part of this release.

## Security

Mutations use capabilities plus nonces or REST permission callbacks. Inputs are validated, SQL values are prepared, public relationship exposure is restricted, output is escaped, cross-origin translated URLs are rejected by scheme/host/port, audit context is redacted, and database errors are not exposed. No external content transfer or credential storage exists.

## Compatibility

The clean package was installed on WordPress 6.9.4, PHP 8.3.30, and MariaDB 11.8.5, including an RTL Persian default locale. Generic architecture is prepared for native objects and multisite; WooCommerce, SEO, builder, forms, provider, GraphQL, federation, and competitor-migration adapters are not implemented or claimed.

## Testing

- Composer validation: passed.
- PHPCS/WPCS: 35 runtime PHP files passed.
- PHPStan: level 7 passed with WordPress 6.9.4 and WP-CLI 2.12 stubs.
- PHPUnit: 10 tests, 14 assertions passed on PHP 8.5.8.
- Independent domain smoke suite: 6 assertions passed.
- PHP syntax: development tree and extracted release passed.
- Clean ZIP: install, activation, 10-table schema, CLI health, PHP language API, public REST language API, RTL seed, deactivation retention, and reactivation passed.
- Composer advisory query: not completed because Packagist's advisory endpoint timed out; the production ZIP has no runtime Composer dependencies.

## Requirement traceability

See `traceability.md`. Implemented, partial, and missing product areas are distinguished explicitly; the release is not represented as feature-equivalent to mature commercial suites.

## Remaining limitations

No translation authoring workspace/visual editor, provider adapters, worker, full source segmentation, third-party integration modules, import/export/migration, WooCommerce/multicurrency, GraphQL, guided setup wizard, browser E2E/visual suite, penetration suite, or large-dataset benchmark exists in 0.1.0.

## Release artifact

`build/multilingual-core-0.1.0.zip`
