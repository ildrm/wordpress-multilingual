<?php

declare(strict_types=1);

namespace MultilingualCore\Application;

final class LanguageContext {

	/** @var array<string, mixed>|null */
	private ?array $current = null;

	public function __construct( private readonly LanguageService $languages ) {
	}

	/** @return array<string, mixed>|null */
	public function current(): ?array {
		if ( null !== $this->current ) {
			return $this->current;
		}

		$queryTag = function_exists( 'get_query_var' ) ? (string) get_query_var( 'mlc_lang', '' ) : '';
		$path     = wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '/' ), PHP_URL_PATH );
		$homePath = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
		if ( is_string( $path ) && is_string( $homePath ) && '/' !== $homePath && str_starts_with( $path, rtrim( $homePath, '/' ) . '/' ) ) {
			$path = substr( $path, strlen( rtrim( $homePath, '/' ) ) );
		}
		$first      = '' !== $queryTag ? $queryTag : ( explode( '/', trim( (string) $path, '/' ) )[0] ?? '' );
		$candidates = $this->languages->all( null, true );
		foreach ( $candidates as $language ) {
			if ( $first === $language['tag'] || $first === $language['code'] ) {
				$this->current = $language;

				return $this->current;
			}
		}

		$this->current = $this->languages->default();

		return $this->current;
	}

	/** @param array<string, mixed>|null $language */
	public function set( ?array $language ): void {
		$this->current = $language;
	}
}
