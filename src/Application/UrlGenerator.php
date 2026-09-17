<?php

declare(strict_types=1);

namespace MultilingualCore\Application;

use InvalidArgumentException;

final class UrlGenerator {

	public function __construct( private readonly LanguageService $languages ) {
	}

	public function forUrl( string $url, string $languageTag ): string {
		$parts = wp_parse_url( $url );
		$home  = wp_parse_url( home_url( '/' ) );
		if ( ! is_array( $parts ) || ! is_array( $home ) || ! $this->sameOrigin( $parts, $home ) ) {
			throw new InvalidArgumentException( 'Only same-origin WordPress URLs can be translated.' );
		}

		$target = null;
		foreach ( $this->languages->all( null, true ) as $language ) {
			if ( $language['tag'] === $languageTag ) {
				$target = $language;
				break;
			}
		}
		if ( null === $target ) {
			return $url;
		}

		$path     = '/' . ltrim( (string) ( $parts['path'] ?? '/' ), '/' );
		$homePath = '/' . trim( (string) ( $home['path'] ?? '' ), '/' );
		if ( '/' !== $homePath && ( $path === $homePath || str_starts_with( $path, $homePath . '/' ) ) ) {
			$path = (string) substr( $path, strlen( $homePath ) );
			$path = '/' . ltrim( $path, '/' );
		}
		$prefixes = array();
		foreach ( $this->languages->all( null, true ) as $language ) {
			$prefixes[] = preg_quote( (string) $language['tag'], '#' );
			$prefixes[] = preg_quote( (string) $language['code'], '#' );
		}
		$path = preg_replace( '#^/(?:' . implode( '|', array_unique( $prefixes ) ) . ')(?=/|$)#', '', $path ) ?? $path;

		$default       = $this->languages->default();
		$prefixDefault = (bool) get_option( 'mlc_prefix_default_language', false );
		$prefix        = ( $target['is_default'] && ! $prefixDefault ) ? '' : '/' . rawurlencode( (string) $target['tag'] );
		$result        = home_url( $prefix . '/' . ltrim( $path, '/' ) );
		if ( isset( $parts['query'] ) ) {
			$result .= '?' . $parts['query'];
		}
		if ( isset( $parts['fragment'] ) ) {
			$result .= '#' . rawurlencode( (string) $parts['fragment'] );
		}

		return $result;
	}

	/**
	 * @param array<string, string|int> $url  Candidate URL parts.
	 * @param array<string, string|int> $home Home URL parts.
	 */
	private function sameOrigin( array $url, array $home ): bool {
		$scheme = static fn ( array $parts ): string => strtolower( (string) ( $parts['scheme'] ?? 'http' ) );
		$port   = static function ( array $parts ) use ( $scheme ): int {
			if ( isset( $parts['port'] ) ) {
				return (int) $parts['port'];
			}

			return 'https' === $scheme( $parts ) ? 443 : 80;
		};

		return strtolower( (string) ( $url['host'] ?? '' ) ) === strtolower( (string) ( $home['host'] ?? '' ) )
			&& $scheme( $url ) === $scheme( $home )
			&& $port( $url ) === $port( $home );
	}
}
