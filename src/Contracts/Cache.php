<?php

declare(strict_types=1);

namespace MultilingualCore\Contracts;

interface Cache {

	public function get( string $group, string $key ): mixed;

	public function set( string $group, string $key, mixed $value, int $ttl = 0 ): bool;

	public function delete( string $group, string $key ): bool;
}
