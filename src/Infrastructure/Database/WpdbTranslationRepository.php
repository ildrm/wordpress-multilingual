<?php

declare(strict_types=1);

namespace MultilingualCore\Infrastructure\Database;

use MultilingualCore\Contracts\Cache;
use MultilingualCore\Contracts\TranslationRepository;
use MultilingualCore\Domain\TranslationStatus;
use RuntimeException;
use wpdb;

final class WpdbTranslationRepository implements TranslationRepository {

	public function __construct(
		private readonly wpdb $database,
		private readonly TableNames $tables,
		private readonly Cache $cache
	) {
	}

	public function languagesForObjects( int $blogId, string $objectType, array $objectIds ): array {
		$objectIds = array_values( array_unique( array_filter( array_map( 'absint', $objectIds ) ) ) );
		if ( array() === $objectIds ) {
			return array();
		}

		$placeholders = implode( ',', array_fill( 0, count( $objectIds ), '%d' ) );
		$args         = array_merge( array( $blogId, $objectType ), $objectIds );
		$query        = Sql::prepare(
			$this->database,
			"SELECT object_id, language_id, group_id, status, source_fingerprint, version
			FROM {$this->tables->objects()}
			WHERE blog_id = %d AND object_type = %s AND object_id IN ($placeholders)",
			...$args
		);
		$rows         = $this->database->get_results( $query, ARRAY_A );

		$mapped = array();
		foreach ( is_array( $rows ) ? $rows : array() as $row ) {
			$mapped[ (int) $row['object_id'] ] = array(
				'language_id'        => (int) $row['language_id'],
				'group_id'           => (int) $row['group_id'],
				'status'             => (string) $row['status'],
				'source_fingerprint' => (string) $row['source_fingerprint'],
				'version'            => (int) $row['version'],
			);
		}

		return $mapped;
	}

	public function translations( int $blogId, string $objectType, int $objectId ): array {
		$key    = $blogId . ':' . $objectType . ':' . $objectId;
		$cached = $this->cache->get( 'relationships', $key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$query = Sql::prepare(
			$this->database,
			"SELECT l.tag, target.object_id, target.status
			FROM {$this->tables->objects()} source
			INNER JOIN {$this->tables->objects()} target ON target.group_id = source.group_id
			INNER JOIN {$this->tables->languages()} l ON l.id = target.language_id AND l.enabled = 1
			WHERE source.blog_id = %d AND source.object_type = %s AND source.object_id = %d",
			$blogId,
			$objectType,
			$objectId
		);
		$rows  = $this->database->get_results( $query, ARRAY_A );
		$map   = array();
		foreach ( is_array( $rows ) ? $rows : array() as $row ) {
			$map[ (string) $row['tag'] ] = array(
				'object_id' => (int) $row['object_id'],
				'status'    => (string) $row['status'],
			);
		}
		$this->cache->set( 'relationships', $key, $map, HOUR_IN_SECONDS );

		return $map;
	}

	public function assign( array $record ): int {
		$status = TranslationStatus::assert( (string) ( $record['status'] ?? TranslationStatus::DRAFT ) );
		$data   = array(
			'group_id'           => (int) $record['group_id'],
			'blog_id'            => (int) $record['blog_id'],
			'language_id'        => (int) $record['language_id'],
			'object_type'        => (string) $record['object_type'],
			'object_subtype'     => (string) ( $record['object_subtype'] ?? '' ),
			'object_id'          => (int) $record['object_id'],
			'source_relation'    => (string) ( $record['source_relation'] ?? 'translation' ),
			'status'             => $status,
			'source_revision'    => isset( $record['source_revision'] ) ? (int) $record['source_revision'] : null,
			'source_fingerprint' => (string) ( $record['source_fingerprint'] ?? '' ),
			'version'            => 1,
			'created_at'         => current_time( 'mysql', true ),
			'updated_at'         => current_time( 'mysql', true ),
		);

		$existingMembers = $this->database->get_results(
			Sql::prepare(
				$this->database,
				"SELECT blog_id, object_type, object_id FROM {$this->tables->objects()} WHERE group_id = %d",
				$data['group_id']
			),
			ARRAY_A
		);

		if ( false === $this->database->insert( $this->tables->objects(), $data ) ) {
			throw new RuntimeException( 'Translation assignment failed.' );
		}

		$this->cache->delete( 'relationships', $data['blog_id'] . ':' . $data['object_type'] . ':' . $data['object_id'] );
		foreach ( is_array( $existingMembers ) ? $existingMembers : array() as $member ) {
			$this->cache->delete( 'relationships', (int) $member['blog_id'] . ':' . (string) $member['object_type'] . ':' . (int) $member['object_id'] );
		}

		return (int) $this->database->insert_id;
	}
}
