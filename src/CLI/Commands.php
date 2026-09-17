<?php

declare(strict_types=1);

namespace MultilingualCore\CLI;

use MultilingualCore\Application\LanguageService;
use MultilingualCore\Infrastructure\Database\Installer;
use MultilingualCore\Infrastructure\Database\TableNames;
use WP_CLI;
use wpdb;

final class Commands {

	public function __construct(
		private readonly LanguageService $languages,
		private readonly wpdb $database,
		private readonly TableNames $tables
	) {
	}

	/** Lists configured languages. */
	public function languageList(): void {
		WP_CLI\Utils\format_items( 'table', $this->languages->all(), array( 'id', 'tag', 'wp_locale', 'native_name', 'enabled', 'is_default' ) );
	}

	/** Reports schema and queue health with a non-zero exit on failure. */
	public function health(): void {
		$missing = array();
		foreach ( $this->tables->all() as $table ) {
			if ( $table !== $this->database->get_var( $this->database->prepare( 'SHOW TABLES LIKE %s', $table ) ) ) {
				$missing[] = $table;
			}
		}
		if ( $missing ) {
			WP_CLI::error( 'Missing database tables: ' . implode( ', ', $missing ) );
		}
		WP_CLI::success( 'Schema ' . MLC_SCHEMA_VERSION . ' is healthy.' );
	}

	/** Applies resumable schema migrations. */
	public function migrate(): void {
		( new Installer( $this->database ) )->install();
		WP_CLI::success( 'Schema migration completed.' );
	}
}
