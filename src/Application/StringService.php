<?php

declare(strict_types=1);

namespace MultilingualCore\Application;

use MultilingualCore\Contracts\StringRepository;

final class StringService {

	public function __construct( private readonly StringRepository $strings ) {
	}

	/** @param array<string, mixed> $metadata */
	public function register( string $domain, string $context, string $source, array $metadata = array() ): int {
		return $this->strings->register( sanitize_key( $domain ), sanitize_text_field( $context ), $source, $metadata );
	}

	/**
	 * @param  list<string> $hashes Stable hashes.
	 * @return array<string, string>
	 */
	public function translations( array $hashes, int $languageId ): array {
		$hashes = array_values( array_filter( $hashes, static fn ( string $hash ): bool => 1 === preg_match( '/^[a-f0-9]{64}$/D', $hash ) ) );

		return $this->strings->translations( $hashes, absint( $languageId ) );
	}
}
