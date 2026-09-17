=== Multilingual Core ===
Contributors: multilingual-core
Tags: multilingual, translation, i18n, localization, rtl
Requires at least: 6.9
Tested up to: 6.9
Requires PHP: 8.1
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A performance-first multilingual foundation with native WordPress content objects.

== Description ==

Multilingual Core provides language management, indexed translation relationships, string registration, language-aware URLs, hreflang output, accessible switchers, REST/PHP/WP-CLI APIs, and health diagnostics.

No content is sent to external services. Provider adapters are not included in this release.

== Installation ==

1. Upload and activate the plugin (network activation is supported).
2. Open Multilingual > Languages.
3. Confirm the detected default locale, then add target languages.
4. Save permalinks if the health screen reports stale rewrite rules.

== Frequently Asked Questions ==

= Does deactivation delete data? =

No. Uninstall also keeps all data unless `MLC_REMOVE_DATA_ON_UNINSTALL` is explicitly set to `true` before uninstalling.

= Does this release send content to AI providers? =

No. External provider adapters are not part of 0.1.0.

== Changelog ==

= 0.1.0 =
* Initial foundation release.
