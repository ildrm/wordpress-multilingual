# Developer APIs

## PHP

- `mlc_get_current_language(): ?array`
- `mlc_get_default_language(): ?array`
- `mlc_get_languages(bool $publicOnly = true): array`
- `mlc_get_translations(string $objectType, int $objectId): array`
- `mlc_get_translated_url(string $url, string $languageTag): string`
- `mlc_register_string(string $domain, string $context, string $source, array $metadata = []): int`
- `mlc_language_switcher(array $attributes = []): void`

Registered string metadata accepts `origin`, `source_location`, `string_type`, `plural`, and `translatable`. Registration updates `last_seen_at`; it does not scan front-end output.

## REST

Base namespace: `/wp-json/multilingual-core/v1`.

| Method/path | Access | Behavior |
| --- | --- | --- |
| `GET /languages` | Public | Enabled, public languages only |
| `POST /languages` | `mlc_manage_languages` | Validate and create language |
| `POST /languages/{id}/default` | `mlc_manage_languages` | Requires impact acknowledgement |
| `GET /objects/{type}/{id}/translations` | `mlc_translate` | Relationship status map |
| `GET /url?url=&language=` | Public | Same-origin translated directory URL |

## WP-CLI

```bash
wp multilingual-core language list
wp multilingual-core health
wp multilingual-core migrate
```

Commands are non-interactive and return non-zero status through `WP_CLI::error()` when health fails.

