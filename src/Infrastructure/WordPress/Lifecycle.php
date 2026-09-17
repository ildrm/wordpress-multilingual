<?php

declare(strict_types=1);

namespace MultilingualCore\Infrastructure\WordPress;

use MultilingualCore\Infrastructure\Database\Installer;

final class Lifecycle {

	public static function activate( bool $networkWide = false ): void {
		global $wpdb;

		$installer = new Installer( $wpdb );
		$installer->install();
		self::installCapabilities();

		if ( is_multisite() && $networkWide ) {
			$siteIds = get_sites(
				array(
					'fields' => 'ids',
					'number' => 0,
				)
			);
			foreach ( $siteIds as $siteId ) {
				switch_to_blog( (int) $siteId );
				try {
					$installer->seedLanguage( (int) $siteId );
				} finally {
					restore_current_blog();
				}
			}
		}

		update_option( 'mlc_rewrite_flush_required', 1, false );
	}

	public static function deactivate(): void {
		wp_clear_scheduled_hook( 'mlc_process_jobs' );
		flush_rewrite_rules( false );
	}

	public static function maybeUpgrade(): void {
		if ( MLC_SCHEMA_VERSION !== get_site_option( 'mlc_schema_version' ) ) {
			global $wpdb;
			( new Installer( $wpdb ) )->install();
		}
	}

	public static function maybeFlushRewriteRules(): void {
		if ( get_option( 'mlc_rewrite_flush_required' ) ) {
			flush_rewrite_rules( false );
			delete_option( 'mlc_rewrite_flush_required' );
		}
	}

	private static function installCapabilities(): void {
		$capabilities  = array(
			'mlc_manage_languages',
			'mlc_translate',
			'mlc_review_translations',
			'mlc_manage_strings',
			'mlc_manage_glossary',
			'mlc_manage_settings',
			'mlc_view_health',
		);
		$administrator = get_role( 'administrator' );
		if ( $administrator ) {
			foreach ( $capabilities as $capability ) {
				$administrator->add_cap( $capability );
			}
		}

		add_role(
			'mlc_translator',
			__( 'Translator', 'multilingual-core' ),
			array(
				'read'          => true,
				'mlc_translate' => true,
			)
		);
		add_role(
			'mlc_reviewer',
			__( 'Translation Reviewer', 'multilingual-core' ),
			array(
				'read'                    => true,
				'mlc_translate'           => true,
				'mlc_review_translations' => true,
			)
		);
	}
}
