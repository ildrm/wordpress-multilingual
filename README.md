# Multilingual Core

Multilingual Core is a performance-first multilingual foundation for WordPress 6.9+ and PHP 8.1+. Native content remains native WordPress content; language relationships, strings, translation memory, terminology, routing metadata, jobs, and audit events use indexed relational tables.

## Implemented in 0.1.0

- Unlimited custom and regional languages with BCP-47-style tags, WordPress locales, direction, fallback metadata, visibility, and ordering.
- Multisite-aware language and translation relationship schema with database uniqueness constraints.
- Bulk object-language, relationship, and registered-string repositories with selective WordPress object caching.
- Directory URL generation and rewrite integration, accessible shortcode/block/PHP language switcher, and published-target hreflang output.
- Translation states, source fingerprints, placeholder validation, exact/fuzzy memory primitives, glossary schema, idempotent job schema, and redacted audit events.
- Capability-separated administrator, translator, and reviewer access; guarded admin language management and health screens.
- Versioned REST language/object/URL endpoints and non-interactive WP-CLI language, health, and migration commands.
- Explicit data-retention uninstall policy and allowlist-based release packaging.

Provider adapters, translation workspaces, visual editing, importers, WooCommerce/multicurrency, and third-party compatibility modules are not represented as complete in this foundation release. See [requirement traceability](docs/traceability.md).

## Development

```bash
composer install
composer check
composer package
```

The release artifact is written to `build/multilingual-core-0.1.0.zip`. The plugin has no runtime Composer dependency and includes a constrained PSR-4 fallback autoloader.

## Documentation

- [Architecture](docs/architecture.md)
- [Database](docs/database.md)
- [Administrator guide](docs/administrator-guide.md)
- [Developer APIs](docs/developer-api.md)
- [Security](docs/security.md)
- [Privacy and data flow](docs/privacy.md)
- [Performance](docs/performance.md)
- [Troubleshooting](docs/troubleshooting.md)
- [Requirement traceability](docs/traceability.md)

Licensed under GPL-2.0-or-later.
