# REST API reference

The namespace is `multilingual-core/v1`; response bodies use WordPress JSON conventions. See `developer-api.md` for the endpoint table.

Language creation requires `tag`, `wp_locale`, `native_name`, and `admin_name`. Optional `direction` is `ltr` or `rtl`; `enabled` and `is_public` default to true. Duplicate site/tag returns conflict. Default-language changes require boolean `impact_acknowledged`; omitting acknowledgement when changing an established default returns conflict.

Object translation maps require `mlc_translate` because groups may describe unpublished work. The public URL endpoint accepts only same-origin WordPress URLs and enabled public language tags.

