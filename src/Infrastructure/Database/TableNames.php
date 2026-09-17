<?php

declare(strict_types=1);

namespace MultilingualCore\Infrastructure\Database;

final class TableNames {

	public function __construct( private readonly string $basePrefix ) {
	}

	public function languages(): string {
		return $this->basePrefix . 'mlc_languages';
	}

	public function groups(): string {
		return $this->basePrefix . 'mlc_translation_groups';
	}

	public function objects(): string {
		return $this->basePrefix . 'mlc_translation_objects';
	}

	public function strings(): string {
		return $this->basePrefix . 'mlc_strings';
	}

	public function stringTranslations(): string {
		return $this->basePrefix . 'mlc_string_translations';
	}

	public function memory(): string {
		return $this->basePrefix . 'mlc_translation_memory';
	}

	public function glossary(): string {
		return $this->basePrefix . 'mlc_glossary';
	}

	public function slugs(): string {
		return $this->basePrefix . 'mlc_translated_slugs';
	}

	public function jobs(): string {
		return $this->basePrefix . 'mlc_jobs';
	}

	public function audit(): string {
		return $this->basePrefix . 'mlc_audit_log';
	}

	/** @return list<string> */
	public function all(): array {
		return array(
			$this->audit(),
			$this->jobs(),
			$this->slugs(),
			$this->glossary(),
			$this->memory(),
			$this->stringTranslations(),
			$this->strings(),
			$this->objects(),
			$this->groups(),
			$this->languages(),
		);
	}
}
