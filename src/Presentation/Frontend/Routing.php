<?php

declare(strict_types=1);

namespace MultilingualCore\Presentation\Frontend;

use MultilingualCore\Application\LanguageService;

final class Routing {

	public function __construct( private readonly LanguageService $languages ) {
	}

	public function register(): void {
		add_filter( 'query_vars', array( $this, 'queryVars' ) );
		add_filter( 'rewrite_rules_array', array( $this, 'rewriteRules' ) );
	}

	/**
	 * @param  list<string> $variables Public query variables.
	 * @return list<string>
	 */
	public function queryVars( array $variables ): array {
		$variables[] = 'mlc_lang';

		return $variables;
	}

	/**
	 * @param  array<string, string> $rules WordPress rewrite rules.
	 * @return array<string, string>
	 */
	public function rewriteRules( array $rules ): array {
		$tags = array();
		foreach ( $this->languages->all( null, true ) as $language ) {
			if ( ! $language['is_default'] || get_option( 'mlc_prefix_default_language', false ) ) {
				$tags[] = preg_quote( (string) $language['tag'], '#' );
			}
		}
		if ( array() === $tags ) {
			return $rules;
		}

		$alternation = implode( '|', array_unique( $tags ) );
		$localized   = array(
			'^(' . $alternation . ')/?$' => 'index.php?mlc_lang=$matches[1]',
		);
		foreach ( $rules as $pattern => $query ) {
			$pattern   = ltrim( $pattern, '^' );
			$query     = preg_replace_callback(
				'/\$matches\[(\d+)\]/',
				static fn ( array $captures ): string => '$matches[' . ( (int) $captures[1] + 1 ) . ']',
				$query
			) ?? $query;
			$separator = str_contains( $query, '?' ) ? '&' : '?';
			$localized[ '^(' . $alternation . ')/' . $pattern ] = $query . $separator . 'mlc_lang=$matches[1]';
		}

		return $localized + $rules;
	}
}
