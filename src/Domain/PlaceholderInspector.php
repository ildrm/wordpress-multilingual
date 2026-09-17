<?php

declare(strict_types=1);

namespace MultilingualCore\Domain;

final class PlaceholderInspector {

	/**
	 * @return list<string>
	 */
	public function extract( string $value ): array {
		preg_match_all( '/(?:%%|%(?:\d+\$)?[bcdeEfFgGosuxX]|\{\{?[A-Za-z0-9_.-]+\}?\}|\[[A-Za-z][^\]\r\n]*\])/u', $value, $matches );
		$tokens = array_values( array_unique( $matches[0] ) );
		sort( $tokens, SORT_STRING );

		return $tokens;
	}

	/**
	 * @return array{valid: bool, missing: list<string>, added: list<string>}
	 */
	public function compare( string $source, string $translation ): array {
		$sourceTokens = $this->extract( $source );
		$targetTokens = $this->extract( $translation );

		return array(
			'valid'   => $sourceTokens === $targetTokens,
			'missing' => array_values( array_diff( $sourceTokens, $targetTokens ) ),
			'added'   => array_values( array_diff( $targetTokens, $sourceTokens ) ),
		);
	}
}
