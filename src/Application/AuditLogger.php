<?php

declare(strict_types=1);

namespace MultilingualCore\Application;

use MultilingualCore\Infrastructure\Database\TableNames;
use wpdb;

final class AuditLogger {

	private const SENSITIVE_KEYS = array( 'api_key', 'authorization', 'cookie', 'password', 'secret', 'token' );

	public function __construct( private readonly wpdb $database, private readonly TableNames $tables ) {
	}

	/** @param array<string, mixed> $context */
	public function record( string $eventType, array $context = array(), ?string $objectType = null, ?int $objectId = null ): void {
		$actorId = get_current_user_id();
		$this->database->insert(
			$this->tables->audit(),
			array(
				'blog_id'     => get_current_blog_id(),
				'actor_id'    => $actorId > 0 ? $actorId : null,
				'event_type'  => sanitize_key( $eventType ),
				'object_type' => $objectType ? sanitize_key( $objectType ) : null,
				'object_id'   => $objectId,
				'context'     => wp_json_encode( $this->redact( $context ) ),
				'created_at'  => current_time( 'mysql', true ),
			)
		);
	}

	/**
	 * @param  array<string, mixed> $context Context to redact.
	 * @return array<string, mixed>
	 */
	private function redact( array $context ): array {
		foreach ( $context as $key => $value ) {
			if ( in_array( strtolower( (string) $key ), self::SENSITIVE_KEYS, true ) ) {
				$context[ $key ] = '[redacted]';
			} elseif ( is_array( $value ) ) {
				$context[ $key ] = $this->redact( $value );
			}
		}

		return $context;
	}
}
