<?php
/**
 * Explicit data-removal path for Multilingual Core.
 *
 * Native WordPress content is never removed. Plugin-owned relational data is
 * removed only when an administrator has defined MLC_REMOVE_DATA_ON_UNINSTALL.
 */

declare(strict_types=1);

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( ! defined( 'MLC_REMOVE_DATA_ON_UNINSTALL' ) || true !== MLC_REMOVE_DATA_ON_UNINSTALL ) {
	return;
}

global $wpdb;

$tables = array(
	$wpdb->base_prefix . 'mlc_audit_log',
	$wpdb->base_prefix . 'mlc_jobs',
	$wpdb->base_prefix . 'mlc_translated_slugs',
	$wpdb->base_prefix . 'mlc_glossary',
	$wpdb->base_prefix . 'mlc_translation_memory',
	$wpdb->base_prefix . 'mlc_string_translations',
	$wpdb->base_prefix . 'mlc_strings',
	$wpdb->base_prefix . 'mlc_translation_objects',
	$wpdb->base_prefix . 'mlc_translation_groups',
	$wpdb->base_prefix . 'mlc_languages',
);
foreach ( $tables as $table ) {
	// Table names are composed exclusively from WordPress's trusted base prefix and fixed literals.
	$wpdb->query( "DROP TABLE IF EXISTS `$table`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
}

delete_site_option( 'mlc_schema_version' );
$capabilities = array( 'mlc_manage_languages', 'mlc_translate', 'mlc_review_translations', 'mlc_manage_strings', 'mlc_manage_glossary', 'mlc_manage_settings', 'mlc_view_health' );
foreach ( array( 'administrator', 'mlc_translator', 'mlc_reviewer' ) as $roleName ) {
	$roleObject = get_role( $roleName );
	if ( $roleObject ) {
		foreach ( $capabilities as $capability ) {
			$roleObject->remove_cap( $capability );
		}
	}
}
remove_role( 'mlc_translator' );
remove_role( 'mlc_reviewer' );
