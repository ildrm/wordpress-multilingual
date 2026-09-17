<?php

declare(strict_types=1);

namespace MultilingualCore\Domain;

final class TranslationMemoryMatcher {

	public function confidence( string $source, string $candidate ): float {
		$lower     = static fn ( string $value ): string => function_exists( 'mb_strtolower' ) ? mb_strtolower( $value ) : strtolower( $value );
		$source    = SourceFingerprint::normalize( $lower( $source ) );
		$candidate = SourceFingerprint::normalize( $lower( $candidate ) );

		if ( $source === $candidate ) {
			return 1.0;
		}
		if ( '' === $source || '' === $candidate ) {
			return 0.0;
		}

		$distance = levenshtein( $source, $candidate );
		$length   = max( strlen( $source ), strlen( $candidate ) );

		return max( 0.0, round( 1 - ( $distance / $length ), 4 ) );
	}

	public function mayAutoApprove( float $confidence ): bool {
		return 1.0 === $confidence;
	}
}
