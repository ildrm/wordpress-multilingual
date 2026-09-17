<?php

declare(strict_types=1);

namespace MultilingualCore\Domain;

final class SourceFingerprint {

	public static function fromString( string $source ): string {
		$normalized = self::normalize( $source );

		return hash( 'sha256', $normalized );
	}

	public static function normalize( string $source ): string {
		$source = str_replace( array( "\r\n", "\r" ), "\n", $source );
		$source = preg_replace( '/[\t ]+/u', ' ', $source ) ?? $source;
		$source = preg_replace( '/ *\n */u', "\n", $source ) ?? $source;

		return trim( $source );
	}
}
