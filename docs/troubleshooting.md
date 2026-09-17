# Troubleshooting

- **Language URL returns 404:** open Settings → Permalinks and save once, or run `wp rewrite flush`. The plugin requests one flush after activation/default-language change and never flushes on visitor requests.
- **No translation in the switcher:** the current object has no published member for that language. This is intentional and prevents invalid hreflang equivalents.
- **Health reports missing tables:** take a database backup, then run `wp multilingual-core migrate`.
- **Persistent cache is unavailable:** lookups fall back to indexed SQL. This affects performance, not correctness.
- **Uninstall retained data:** this is the safe default. Define `MLC_REMOVE_DATA_ON_UNINSTALL` as boolean `true` only after export/backup and then uninstall again.

