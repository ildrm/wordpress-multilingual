<?php

declare(strict_types=1);

namespace MultilingualCore\Domain;

use InvalidArgumentException;

final class TranslationStatus {

	public const UNTRANSLATED           = 'untranslated';
	public const DRAFT                  = 'draft';
	public const MACHINE_TRANSLATED     = 'machine_translated';
	public const TRANSLATED             = 'translated';
	public const NEEDS_REVIEW           = 'needs_review';
	public const APPROVED               = 'approved';
	public const STALE                  = 'stale';
	public const FAILED                 = 'failed';
	public const INTENTIONALLY_EXCLUDED = 'intentionally_excluded';

	private const VALUES = array(
		self::UNTRANSLATED,
		self::DRAFT,
		self::MACHINE_TRANSLATED,
		self::TRANSLATED,
		self::NEEDS_REVIEW,
		self::APPROVED,
		self::STALE,
		self::FAILED,
		self::INTENTIONALLY_EXCLUDED,
	);

	public static function assert( string $status ): string {
		if ( ! in_array( $status, self::VALUES, true ) ) {
			throw new InvalidArgumentException( 'Unknown translation status.' );
		}

		return $status;
	}

	/** @return list<string> */
	public static function values(): array {
		return self::VALUES;
	}
}
