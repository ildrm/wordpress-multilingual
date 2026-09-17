<?php

declare(strict_types=1);

namespace MultilingualCore\Contracts;

interface StringRepository {

	/** @param array<string, mixed> $metadata */
	public function register( string $domain, string $context, string $source, array $metadata = array() ): int;

	/**
	 * @param list<string> $hashes
	 * @return array<string, string>
	 */
	public function translations( array $hashes, int $languageId ): array;
}
