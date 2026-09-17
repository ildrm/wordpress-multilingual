# Migration guide

Release 0.1.0 has an idempotent internal schema installer but no WPML, Polylang, or TranslatePress importer. It never reads or alters those plugins’ private tables.

A future importer must implement detect → analyze → report → dry run → backup warning → import → validate → reconcile, persist checkpoints, remain resumable, and report lossy mappings. Until that exists, do not remove a source multilingual plugin based on this release.

