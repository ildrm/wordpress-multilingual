<?php

declare(strict_types=1);

namespace MultilingualCore\Contracts;

interface TranslationRepository {

	/**
	 * @param list<int> $objectIds
	 * @return array<int, array<string, mixed>>
	 */
	public function languagesForObjects( int $blogId, string $objectType, array $objectIds ): array;

	/** @return array<string, array{object_id:int,status:string}> */
	public function translations( int $blogId, string $objectType, int $objectId ): array;

	/** @param array<string, mixed> $record */
	public function assign( array $record ): int;
}
