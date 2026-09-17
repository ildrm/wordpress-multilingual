<?php

declare(strict_types=1);

namespace MultilingualCore\Application;

use InvalidArgumentException;
use MultilingualCore\Contracts\LanguageRepository;
use MultilingualCore\Domain\LanguageTag;
use RuntimeException;

final class LanguageService {

	public function __construct(
		private readonly LanguageRepository $languages,
		private readonly AuditLogger $audit
	) {
	}

	/** @return list<array<string, mixed>> */
	public function all( ?int $blogId = null, bool $publicOnly = false ): array {
		return $this->languages->all( $blogId ?? get_current_blog_id(), $publicOnly );
	}

	/** @return array<string, mixed>|null */
	public function default( ?int $blogId = null ): ?array {
		return $this->languages->default( $blogId ?? get_current_blog_id() );
	}

	/** @param array<string, mixed> $input */
	public function save( array $input, ?int $blogId = null ): int {
		$blogId = $blogId ?? get_current_blog_id();
		$tag    = new LanguageTag( (string) ( $input['tag'] ?? '' ) );
		$locale = sanitize_text_field( (string) ( $input['wp_locale'] ?? '' ) );
		if ( '' === $locale || 1 !== preg_match( '/^[A-Za-z]{2,8}(?:[_-][A-Za-z0-9]{2,8})*$/D', $locale ) ) {
			throw new InvalidArgumentException( 'A valid WordPress locale is required.' );
		}

		$direction = (string) ( $input['direction'] ?? 'ltr' );
		if ( ! in_array( $direction, array( 'ltr', 'rtl' ), true ) ) {
			throw new InvalidArgumentException( 'Direction must be ltr or rtl.' );
		}

		$fallbackId = isset( $input['fallback_language_id'] ) ? absint( $input['fallback_language_id'] ) : null;
		$id         = isset( $input['id'] ) ? absint( $input['id'] ) : 0;
		if ( $id > 0 && $fallbackId === $id ) {
			throw new InvalidArgumentException( 'A language cannot fall back to itself.' );
		}

		$record = array(
			'id'                   => $id,
			'tag'                  => $tag->value(),
			'wp_locale'            => $locale,
			'code'                 => sanitize_key( (string) ( $input['code'] ?? $tag->primary() ) ),
			'native_name'          => sanitize_text_field( (string) ( $input['native_name'] ?? '' ) ),
			'admin_name'           => sanitize_text_field( (string) ( $input['admin_name'] ?? '' ) ),
			'region'               => sanitize_text_field( (string) ( $input['region'] ?? '' ) ),
			'script'               => sanitize_text_field( (string) ( $input['script'] ?? '' ) ),
			'direction'            => $direction,
			'icon'                 => isset( $input['icon'] ) ? esc_url_raw( (string) $input['icon'] ) : '',
			'fallback_language_id' => $fallbackId,
			'date_format'          => sanitize_text_field( (string) ( $input['date_format'] ?? '' ) ),
			'time_format'          => sanitize_text_field( (string) ( $input['time_format'] ?? '' ) ),
			'number_format'        => $this->sanitizeNumberFormat( $input['number_format'] ?? array() ),
			'currency'             => strtoupper( sanitize_key( (string) ( $input['currency'] ?? '' ) ) ),
			'enabled'              => isset( $input['enabled'] ) ? (bool) $input['enabled'] : true,
			'is_public'            => isset( $input['is_public'] ) ? (bool) $input['is_public'] : true,
			'position'             => isset( $input['position'] ) ? min( 65535, absint( $input['position'] ) ) : 0,
		);
		if ( '' === $record['native_name'] || '' === $record['admin_name'] ) {
			throw new InvalidArgumentException( 'Native and administrator language names are required.' );
		}

		if ( $id > 0 ) {
			$this->assertFallbackChain( $blogId, $id, $fallbackId );
		}
		$savedId = $this->languages->save( $blogId, $record );
		$this->audit->record(
			'language_saved',
			array(
				'language_id' => $savedId,
				'tag'         => $tag->value(),
			),
			'language',
			$savedId
		);

		return $savedId;
	}

	public function setDefault( int $languageId, bool $impactAcknowledged, ?int $blogId = null ): void {
		$blogId  = $blogId ?? get_current_blog_id();
		$current = $this->languages->default( $blogId );
		if ( null !== $current && (int) $current['id'] !== $languageId && ! $impactAcknowledged ) {
			throw new RuntimeException( 'Default-language URL and hreflang impact must be acknowledged.' );
		}
		$this->languages->setDefault( $blogId, $languageId );
		$this->audit->record(
			'default_language_changed',
			array(
				'previous_id' => $current['id'] ?? null,
				'language_id' => $languageId,
			),
			'language',
			$languageId
		);
	}

	/** @return array<string, string|int> */
	private function sanitizeNumberFormat( mixed $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}

		return array(
			'decimal_separator'   => sanitize_text_field( (string) ( $value['decimal_separator'] ?? '.' ) ),
			'thousands_separator' => sanitize_text_field( (string) ( $value['thousands_separator'] ?? ',' ) ),
			'decimals'            => min( 8, absint( $value['decimals'] ?? 2 ) ),
		);
	}

	private function assertFallbackChain( int $blogId, int $languageId, ?int $fallbackId ): void {
		if ( null === $fallbackId || 0 === $fallbackId ) {
			return;
		}
		$byId = array();
		foreach ( $this->languages->all( $blogId ) as $language ) {
			$byId[ (int) $language['id'] ] = $language;
		}

		$seen = array( $languageId => true );
		$next = $fallbackId;
		while ( $next > 0 ) {
			if ( ! isset( $byId[ $next ] ) ) {
				throw new InvalidArgumentException( 'The fallback language does not exist on this site.' );
			}
			if ( isset( $seen[ $next ] ) ) {
				throw new InvalidArgumentException( 'The fallback chain contains a cycle.' );
			}
			$seen[ $next ] = true;
			$next          = isset( $byId[ $next ]['fallback_language_id'] ) ? (int) $byId[ $next ]['fallback_language_id'] : 0;
		}
	}
}
