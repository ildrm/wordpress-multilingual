# Security model

- Admin mutations combine dedicated capabilities with WordPress nonces.
- REST mutations use explicit permission callbacks and registered argument schemas.
- SQL values are prepared; uniqueness and idempotency are database enforced.
- Visible output is escaped at its context. Provider output is not part of this release.
- Public APIs expose enabled/public languages; relationship inspection requires translator capability so unpublished relationships do not leak.
- URL translation is limited to the WordPress home host and does not accept cross-origin redirects.
- Audit context recursively redacts common credential keys and excludes translation bodies by design.
- Uninstall is deny-by-default and preserves native WordPress content even when relational cleanup is explicitly enabled.

No API credentials are stored and no content leaves the site in 0.1.0. Future provider modules must use wp-config/environment secrets where possible, never expose them to JavaScript, never log them, and must treat source content and provider responses as untrusted data.

