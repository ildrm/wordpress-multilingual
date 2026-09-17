# Administrator guide

Open **Multilingual → Languages** after activation. The plugin creates a default language from the current WordPress locale. Add target languages using BCP 47 tags such as `fa`, `en-GB`, or `zh-Hant-TW`; the WordPress locale remains a separate value such as `fa_IR`.

Changing the default language requires checking the impact acknowledgement because directory URLs and hreflang can change. The setting does not silently redirect visitors. Rewrite rules are flushed once after the change, never on ordinary requests.

Use `[mlc_language_switcher]`, the `multilingual-core/language-switcher` dynamic block, or `mlc_language_switcher()` in a theme. The switcher uses language names, not flags alone; missing singular translations are hidden by default.

The dashboard reports real translation status counts. The Health screen checks schema, persistent cache state, and queue scheduling. A non-persistent cache is informational rather than an error.

Deactivation retains all data. To remove plugin-owned relational metadata on uninstall, first back up/export data and define `MLC_REMOVE_DATA_ON_UNINSTALL` as boolean `true`. Native posts, terms, media, and comments are never deleted by the uninstaller.

