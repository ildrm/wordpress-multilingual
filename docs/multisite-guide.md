# Multisite guide

The plugin uses network-base tables and includes `blog_id` in site-owned records. Network activation installs the schema once and seeds each existing site while switched into that site. `wp_initialize_site` seeds new sites. Language/cache keys and object relationships remain site-scoped.

Mapped-domain routing and federation—where separate sites form language editions—are not implemented. Standard multisite does not implicitly create cross-site translation relationships.

