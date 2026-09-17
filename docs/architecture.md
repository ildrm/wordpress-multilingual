# Architecture

```text
WordPress hooks / REST / WP-CLI / PHP API
                  |
       Presentation orchestration
                  |
 Language · Translation · String services
                  |
 Repository and cache contracts
                  |
      wpdb tables + WP object cache
                  |
         Native WordPress objects
```

## Boundaries

- `Domain` contains WordPress-independent language-tag, status, fingerprint, placeholder, and memory-matching logic.
- `Application` owns use cases and invariants. Controllers do not write tables directly.
- `Contracts` isolates persistence and cache behavior.
- `Infrastructure` contains `wpdb`, object-cache, schema, and lifecycle adapters.
- `Presentation` contains task-focused admin screens, REST orchestration, routing, hreflang, and switcher rendering.
- `CLI` exposes non-interactive operations.

The front end does not load admin code or scan strings. External services are absent from the boot path. Optional future integrations must register behind interfaces and must not prevent core boot on failure.

## Language resolution lifecycle

1. WordPress rewrite rules expose `mlc_lang` for a recognized directory prefix.
2. `LanguageContext` checks the query variable, then the request path, then the enabled default language.
3. The resolved language is memoized per request.
4. Consumers use the PHP API rather than reading request variables.

## Translation resolution lifecycle

1. Resolve the native WordPress object ID and type.
2. Fetch its group and every enabled language member in one joined query.
3. Cache the map by site/type/object.
4. Validate publication state at the presentation boundary before exposing public URLs or hreflang.

## Cache model

Language lists, relationship maps, and dictionary batches have separate cache groups. Mutations invalidate the narrow affected keys. Correctness uses the database as authority; loss of Redis or another persistent cache only causes cache misses.

## Queue/provider model

The schema reserves idempotent, version-aware jobs with retry metadata. No worker or provider is claimed in 0.1.0. A provider module must keep secrets server-side, declare capabilities/cost limits, validate structured results, and compare source fingerprint and target version before commit.

