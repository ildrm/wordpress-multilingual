<?php

declare(strict_types=1);

namespace MultilingualCore\Infrastructure\Database;

use MultilingualCore\Contracts\Cache;
use MultilingualCore\Contracts\StringRepository;
use MultilingualCore\Domain\SourceFingerprint;
use RuntimeException;
use wpdb;

final class WpdbStringRepository implements StringRepository {

	public function __construct(
		private readonly wpdb $database,
		private readonly TableNames $tables,
		private readonly Cache $cache
	) {
	}

	public function register( string $domain, string $context, string $source, array $metadata = array() ): int {
		$normalized = SourceFingerprint::normalize( $source );
		$stableHash = hash( 'sha256', $domain . "\0" . $context . "\0" . ( $metadata['origin'] ?? '' ) . "\0" . $normalized );
		$existing   = $this->database->get_var(
			Sql::prepare( $this->database, "SELECT id FROM {$this->tables->strings()} WHERE stable_hash = %s", $stableHash )
		);
		$now        = current_time( 'mysql', true );
		if ( $existing ) {
			$this->database->update(
				$this->tables->strings(),
				array(
					'last_seen_at' => $now,
					'is_active'    => 1,
				),
				array( 'id' => (int) $existing )
			);

			return (int) $existing;
		}

		$inserted = $this->database->insert(
			$this->tables->strings(),
			array(
				'stable_hash'      => $stableHash,
				'text_domain'      => $domain,
				'context'          => $context,
				'original_value'   => $source,
				'normalized_value' => $normalized,
				'source_hash'      => SourceFingerprint::fromString( $source ),
				'source_location'  => $metadata['source_location'] ?? null,
				'origin'           => $metadata['origin'] ?? '',
				'string_type'      => $metadata['string_type'] ?? 'text',
				'plural_metadata'  => isset( $metadata['plural'] ) ? wp_json_encode( $metadata['plural'] ) : null,
				'is_active'        => 1,
				'is_translatable'  => isset( $metadata['translatable'] ) ? (int) (bool) $metadata['translatable'] : 1,
				'discovered_at'    => $now,
				'last_seen_at'     => $now,
			)
		);
		if ( false === $inserted ) {
			throw new RuntimeException( 'String registration failed.' );
		}

		return (int) $this->database->insert_id;
	}

	public function translations( array $hashes, int $languageId ): array {
		$hashes = array_values( array_unique( array_filter( $hashes ) ) );
		if ( array() === $hashes ) {
			return array();
		}

		$key    = $languageId . ':' . hash( 'sha256', implode( '|', $hashes ) );
		$cached = $this->cache->get( 'dictionary', $key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$placeholders = implode( ',', array_fill( 0, count( $hashes ), '%s' ) );
		$args         = array_merge( array( $languageId ), $hashes );
		$query        = Sql::prepare(
			$this->database,
			"SELECT s.stable_hash, st.translated_value
			FROM {$this->tables->strings()} s
			INNER JOIN {$this->tables->stringTranslations()} st ON st.string_id = s.id
			WHERE st.language_id = %d AND st.status IN ('translated','approved')
			AND s.stable_hash IN ($placeholders)",
			...$args
		);
		$rows         = $this->database->get_results( $query, ARRAY_A );
		$map          = array();
		foreach ( is_array( $rows ) ? $rows : array() as $row ) {
			$map[ (string) $row['stable_hash'] ] = (string) $row['translated_value'];
		}

		$this->cache->set( 'dictionary', $key, $map, HOUR_IN_SECONDS );

		return $map;
	}
}
