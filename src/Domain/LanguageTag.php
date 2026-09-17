<?php

declare(strict_types=1);

namespace MultilingualCore\Domain;

use InvalidArgumentException;

final class LanguageTag {

	private const PATTERN = '/^(?<language>[A-Za-z]{2,8})(?:-(?<script>[A-Za-z]{4}))?(?:-(?<region>[A-Za-z]{2}|[0-9]{3}))?(?:-(?<variant>[A-Za-z0-9]{5,8}|[0-9][A-Za-z0-9]{3}))*$/D';

	private string $value;

	public function __construct( string $value ) {
		$value = str_replace( '_', '-', trim( $value ) );
		if ( 1 !== preg_match( self::PATTERN, $value, $parts ) ) {
			throw new InvalidArgumentException( 'The language tag is not a supported BCP 47 language tag.' );
		}

		$normalized = strtolower( $parts['language'] );
		if ( ! empty( $parts['script'] ) ) {
			$normalized .= '-' . ucfirst( strtolower( $parts['script'] ) );
		}
		if ( ! empty( $parts['region'] ) ) {
			$normalized .= '-' . strtoupper( $parts['region'] );
		}
		if ( ! empty( $parts['variant'] ) ) {
			$normalized .= '-' . strtolower( $parts['variant'] );
		}

		$this->value = $normalized;
	}

	public function value(): string {
		return $this->value;
	}

	public function primary(): string {
		return explode( '-', $this->value, 2 )[0];
	}

	public function equals( self $other ): bool {
		return $this->value === $other->value;
	}

	public function __toString(): string {
		return $this->value;
	}
}
