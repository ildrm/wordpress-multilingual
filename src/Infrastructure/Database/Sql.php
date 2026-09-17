<?php

declare(strict_types=1);

namespace MultilingualCore\Infrastructure\Database;

use RuntimeException;
use wpdb;

final class Sql {

	public static function prepare( wpdb $database, string $query, mixed ...$arguments ): string {
		$prepared = call_user_func_array( array( $database, 'prepare' ), array_merge( array( $query ), $arguments ) );
		if ( ! is_string( $prepared ) || '' === $prepared ) {
			throw new RuntimeException( 'The database query could not be prepared.' );
		}

		return $prepared;
	}
}
