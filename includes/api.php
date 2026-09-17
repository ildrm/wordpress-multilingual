<?php

declare(strict_types=1);

use MultilingualCore\Plugin;

if (! function_exists('mlc_get_current_language')) {
	/** @return array<string, mixed>|null */
	function mlc_get_current_language(): ?array
	{
		return Plugin::boot()->context()->current();
	}
}

if (! function_exists('mlc_get_default_language')) {
	/** @return array<string, mixed>|null */
	function mlc_get_default_language(): ?array
	{
		return Plugin::boot()->languages()->default();
	}
}

if (! function_exists('mlc_get_languages')) {
	/** @return list<array<string, mixed>> */
	function mlc_get_languages(bool $publicOnly = true): array
	{
		return Plugin::boot()->languages()->all(null, $publicOnly);
	}
}

if (! function_exists('mlc_get_translations')) {
	/** @return array<string, array{object_id:int,status:string}> */
	function mlc_get_translations(string $objectType, int $objectId): array
	{
		return Plugin::boot()->translations()->forObject($objectType, $objectId);
	}
}

if (! function_exists('mlc_get_translated_url')) {
	function mlc_get_translated_url(string $url, string $languageTag): string
	{
		return Plugin::boot()->urls()->forUrl($url, $languageTag);
	}
}

if (! function_exists('mlc_register_string')) {
	/** @param array<string, mixed> $metadata */
	function mlc_register_string(string $domain, string $context, string $source, array $metadata = array()): int
	{
		return Plugin::boot()->strings()->register($domain, $context, $source, $metadata);
	}
}

if (! function_exists('mlc_language_switcher')) {
	/** @param array<string, mixed> $attributes */
	function mlc_language_switcher(array $attributes = array()): void
	{
		echo wp_kses_post(do_shortcode('[mlc_language_switcher ' . implode(' ', array_map(static fn (string $key, mixed $value): string => sprintf('%s="%s"', sanitize_key($key), esc_attr((string) $value)), array_keys($attributes), $attributes)) . ']'));
	}
}
