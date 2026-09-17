<?php

declare(strict_types=1);

namespace MultilingualCore\Application;

use MultilingualCore\Contracts\TranslationRepository;

final class TranslationService {

	public function __construct( private readonly TranslationRepository $translations ) {
	}

	/** @return array<string, array{object_id:int,status:string}> */
	public function forObject( string $objectType, int $objectId, ?int $blogId = null ): array {
		return $this->translations->translations( $blogId ?? get_current_blog_id(), sanitize_key( $objectType ), absint( $objectId ) );
	}

	/**
	 * @param list<int> $objectIds
	 * @return array<int, array<string, mixed>>
	 */
	public function languagesForObjects( string $objectType, array $objectIds, ?int $blogId = null ): array {
		return $this->translations->languagesForObjects( $blogId ?? get_current_blog_id(), sanitize_key( $objectType ), $objectIds );
	}
}
