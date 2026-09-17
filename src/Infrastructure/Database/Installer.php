<?php

declare(strict_types=1);

namespace MultilingualCore\Infrastructure\Database;

use wpdb;

final class Installer {

	public function __construct( private readonly wpdb $database ) {
	}

	public function install(): void {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$tables = new TableNames( $this->database->base_prefix );
		$schema = new Schema( $tables, $this->database->get_charset_collate() );
		foreach ( $schema->statements() as $statement ) {
			dbDelta( $statement );
		}

		update_site_option( 'mlc_schema_version', MLC_SCHEMA_VERSION );
		$this->seedLanguage( get_current_blog_id(), $tables );
	}

	public function seedLanguage( int $blogId, ?TableNames $tables = null ): void {
		$tables ??= new TableNames( $this->database->base_prefix );
		$count    = (int) $this->database->get_var(
			Sql::prepare( $this->database, "SELECT COUNT(*) FROM {$tables->languages()} WHERE blog_id = %d", $blogId )
		);
		if ( $count > 0 ) {
			return;
		}

		$locale = get_locale();
		$locale = '' !== $locale ? $locale : 'en_US';
		$tag    = str_replace( '_', '-', $locale );
		$code   = strtolower( explode( '_', $locale, 2 )[0] );
		$now    = current_time( 'mysql', true );
		$this->database->insert(
			$tables->languages(),
			array(
				'blog_id'     => $blogId,
				'tag'         => $tag,
				'wp_locale'   => $locale,
				'code'        => $code,
				'native_name' => $locale,
				'admin_name'  => $locale,
				'direction'   => is_rtl() ? 'rtl' : 'ltr',
				'enabled'     => 1,
				'is_public'   => 1,
				'is_default'  => 1,
				'position'    => 0,
				'created_at'  => $now,
				'updated_at'  => $now,
			)
		);
	}
}
