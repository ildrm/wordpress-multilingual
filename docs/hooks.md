# Hooks and extension points

The stable extension surface in 0.1.0 is the documented PHP API and repository contracts. No action/filter catalogue is claimed yet because prematurely freezing hook signatures would create a backward-compatibility burden before the translation authoring lifecycle exists.

WordPress-native hooks used by the plugin include `plugins_loaded`, `init`, `rest_api_init`, `wp_head`, `query_vars`, `rewrite_rules_array`, `wp_initialize_site`, admin menu/post hooks, and activation/deactivation hooks. Future public hooks must be documented here with argument types and lifecycle timing before release.

