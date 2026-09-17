# Privacy and data flow

This release performs no third-party telemetry and makes no translation-provider network calls. Language configuration, relationships, registered strings, and audit metadata remain in the WordPress database. Cached copies remain within the configured WordPress object-cache backend.

Audit events record operation type, actor ID, site ID, safe object identity, and small redacted context. They do not intentionally record credentials or translated content. Site owners are responsible for normal WordPress user/account retention obligations.

Future external-provider modules require a separate explicit enablement flow describing provider identity, content scope, purpose, credential source, and exclusions before any content is sent.

