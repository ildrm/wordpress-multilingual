# Performance model

Front-end boot performs no provider calls, content scans, memory searches, or migrations. Admin-only classes are instantiated only in wp-admin. Switcher CSS is enqueued only when a switcher renders.

Relationship resolution uses one joined query per cold object map and caches the complete language map. Object-language discovery accepts ID batches and produces one `IN` query rather than one query per object. Registered string translation accepts hash batches and performs one joined lookup, cached by language and batch fingerprint.

Language configuration is a small ordered dataset cached by site and public visibility. Correctness never depends on persistent caching.

The repository includes query shapes and indexes but does not include measured large-dataset benchmarks in 0.1.0. Production claims must wait for the synthetic 100k-object/million-string fixture and baseline comparison described as missing in `traceability.md`.

