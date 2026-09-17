<?php

declare(strict_types=1);

namespace MultilingualCore\Contracts;

interface LanguageRepository {

	/** @return list<array<string, mixed>> */
	public function all( int $blogId, bool $publicOnly = false ): array;

	/** @return array<string, mixed>|null */
	public function findByTag( int $blogId, string $tag ): ?array;

	/** @return array<string, mixed>|null */
	public function default( int $blogId ): ?array;

	/** @param array<string, mixed> $language */
	public function save( int $blogId, array $language ): int;

	public function setDefault( int $blogId, int $languageId ): bool;
}
