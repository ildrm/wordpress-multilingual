# AI and provider guide

No machine-translation or AI provider ships in 0.1.0. Consequently, the release stores no provider credential, transmits no content, performs no paid call, and makes no AI quality claim.

The data model reserves idempotency keys, source fingerprints, target versions, retry state, origins, and quality/approval state so a later provider module can safely prevent duplicate charges and stale overwrites. Such a module requires its own specification covering capability discovery, structured payloads, protected tokens, credential sourcing, cost budgets, privacy disclosure, validation, backoff, and prompt-injection handling.

