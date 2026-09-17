<?php

declare(strict_types=1);

namespace MultilingualCore\Infrastructure\Database;

use MultilingualCore\Contracts\Cache;
use MultilingualCore\Contracts\LanguageRepository;
use RuntimeException;
use wpdb;

final class WpdbLanguageRepository implements LanguageRepository {

	public function __construct(
		private readonly wpdb $database,
		private readonly TableNames $tables,
		private readonly Cache $cache
	) {
	}

	public function all( int $blogId, bool $publicOnly = false ): array {
		$key    = $blogId . ':' . (int) $publicOnly;
		$cached = $this->cache->get( 'languages', $key );
		if ( is_array( $cached ) ) {
			return array_values( array_filter( $cached, 'is_array' ) );
		}

		$where = 'blog_id = %d';
		if ( $publicOnly ) {
			$where .= ' AND enabled = 1 AND is_public = 1';
		}

		$query = Sql::prepare(
			$this->database,
			"SELECT id, tag, wp_locale, code, native_name, admin_name, region, script, direction, icon,
			fallback_language_id, date_format, time_format, number_format, currency, enabled, is_public,
			is_default, position FROM {$this->tables->languages()} WHERE $where ORDER BY position ASC, id ASC",
			$blogId
		);
		$rows  = $this->database->get_results( $query, ARRAY_A );
		$rows  = is_array( $rows ) ? array_values( array_map( array( $this, 'cast' ), $rows ) ) : array();

		$this->cache->set( 'languages', $key, $rows, HOUR_IN_SECONDS );

		return $rows;
	}

	public function findByTag( int $blogId, string $tag ): ?array {
		foreach ( $this->all( $blogId ) as $language ) {
			if ( $language['tag'] === $tag && $language['enabled'] ) {
				return $language;
			}
		}

		return null;
	}

	public function default( int $blogId ): ?array {
		foreach ( $this->all( $blogId ) as $language ) {
			if ( $language['is_default'] && $language['enabled'] ) {
				return $language;
			}
		}

		return null;
	}

	public function save( int $blogId, array $language ): int {
		$now  = current_time( 'mysql', true );
		$id   = isset( $language['id'] ) ? (int) $language['id'] : 0;
		$data = array(
			'blog_id'              => $blogId,
			'tag'                  => (string) $language['tag'],
			'wp_locale'            => (string) $language['wp_locale'],
			'code'                 => (string) $language['code'],
			'native_name'          => (string) $language['native_name'],
			'admin_name'           => (string) $language['admin_name'],
			'region'               => '' !== $language['region'] ? $language['region'] : null,
			'script'               => '' !== $language['script'] ? $language['script'] : null,
			'direction'            => (string) $language['direction'],
			'icon'                 => '' !== $language['icon'] ? $language['icon'] : null,
			'fallback_language_id' => ! empty( $language['fallback_language_id'] ) ? $language['fallback_language_id'] : null,
			'date_format'          => '' !== $language['date_format'] ? $language['date_format'] : null,
			'time_format'          => '' !== $language['time_format'] ? $language['time_format'] : null,
			'number_format'        => wp_json_encode( $language['number_format'] ),
			'currency'             => '' !== $language['currency'] ? $language['currency'] : null,
			'enabled'              => (int) $language['enabled'],
			'is_public'            => (int) $language['is_public'],
			'position'             => (int) $language['position'],
			'updated_at'           => $now,
		);

		if ( $id > 0 ) {
			$updated = $this->database->update(
				$this->tables->languages(),
				$data,
				array(
					'id'      => $id,
					'blog_id' => $blogId,
				)
			);
			if ( false === $updated ) {
				throw new RuntimeException( 'Language update failed.' );
			}
		} else {
			$data['created_at'] = $now;
			$data['is_default'] = 0;
			$inserted           = $this->database->insert( $this->tables->languages(), $data );
			if ( false === $inserted ) {
				throw new RuntimeException( 'Language creation failed.' );
			}
			$id = (int) $this->database->insert_id;
		}

		$this->invalidate( $blogId );

		return $id;
	}

	public function setDefault( int $blogId, int $languageId ): bool {
		$this->database->query( 'START TRANSACTION' );
		try {
			$exists = (int) $this->database->get_var(
				Sql::prepare(
					$this->database,
					"SELECT COUNT(*) FROM {$this->tables->languages()} WHERE id = %d AND blog_id = %d AND enabled = 1 FOR UPDATE",
					$languageId,
					$blogId
				)
			);
			if ( 1 !== $exists ) {
				throw new RuntimeException( 'The default language must be enabled and belong to this site.' );
			}

			$this->database->update( $this->tables->languages(), array( 'is_default' => 0 ), array( 'blog_id' => $blogId ) );
			$updated = $this->database->update(
				$this->tables->languages(),
				array(
					'is_default' => 1,
					'updated_at' => current_time( 'mysql', true ),
				),
				array(
					'id'      => $languageId,
					'blog_id' => $blogId,
				)
			);
			if ( false === $updated ) {
				throw new RuntimeException( 'Default language update failed.' );
			}

			$this->database->query( 'COMMIT' );
			$this->invalidate( $blogId );

			return true;
		} catch ( \Throwable $exception ) {
			$this->database->query( 'ROLLBACK' );
			throw $exception;
		}
	}

	/**
	 * @param  array<string, mixed> $row Raw database row.
	 * @return array<string, mixed>
	 */
	private function cast( array $row ): array {
		foreach ( array( 'id', 'fallback_language_id', 'position' ) as $key ) {
			$row[ $key ] = null === $row[ $key ] ? null : (int) $row[ $key ];
		}
		foreach ( array( 'enabled', 'is_public', 'is_default' ) as $key ) {
			$row[ $key ] = (bool) $row[ $key ];
		}
		$row['number_format'] = $row['number_format'] ? json_decode( (string) $row['number_format'], true ) : array();

		return $row;
	}

	private function invalidate( int $blogId ): void {
		$this->cache->delete( 'languages', $blogId . ':0' );
		$this->cache->delete( 'languages', $blogId . ':1' );
	}
}
