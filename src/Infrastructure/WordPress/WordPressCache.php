<?php

declare(strict_types=1);

namespace MultilingualCore\Infrastructure\WordPress;

use MultilingualCore\Contracts\Cache;

final class WordPressCache implements Cache {

	public function get( string $group, string $key ): mixed {
		$value = wp_cache_get( $key, 'mlc_' . $group, false, $found );

		return $found ? $value : null;
	}

	public function set( string $group, string $key, mixed $value, int $ttl = 0 ): bool {
		return wp_cache_set( $key, $value, 'mlc_' . $group, $ttl );
	}

	public function delete( string $group, string $key ): bool {
		return wp_cache_delete( $key, 'mlc_' . $group );
	}
}
