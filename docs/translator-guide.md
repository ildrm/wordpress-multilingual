# Translator guide

Release 0.1.0 installs a least-privilege **Translator** role (`mlc_translate`) and **Translation Reviewer** role (`mlc_review_translations`). Relationship inspection is available through the REST/PHP APIs. A segment editor, assignment inbox, comments, bulk approval, previews, and per-language account restrictions are not implemented in this release and must not be inferred from the roles.

Translation statuses stored by the platform are untranslated, draft, machine translated, translated, needs review, approved, stale, failed, and intentionally excluded. Fuzzy memory primitives never auto-approve a translation.

