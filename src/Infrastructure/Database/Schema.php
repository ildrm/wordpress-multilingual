<?php

declare(strict_types=1);

namespace MultilingualCore\Infrastructure\Database;

final class Schema {

	public function __construct(
		private readonly TableNames $tables,
		private readonly string $charsetCollate
	) {
	}

	/** @return list<string> */
	public function statements(): array {
		$c = $this->charsetCollate;

		return array(
			"CREATE TABLE {$this->tables->languages()} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				blog_id bigint(20) unsigned NOT NULL DEFAULT 1,
				tag varchar(35) NOT NULL,
				wp_locale varchar(35) NOT NULL,
				code varchar(12) NOT NULL,
				native_name varchar(191) NOT NULL,
				admin_name varchar(191) NOT NULL,
				region varchar(8) NULL,
				script varchar(8) NULL,
				direction varchar(3) NOT NULL DEFAULT 'ltr',
				icon varchar(255) NULL,
				fallback_language_id bigint(20) unsigned NULL,
				date_format varchar(191) NULL,
				time_format varchar(191) NULL,
				number_format longtext NULL,
				currency varchar(3) NULL,
				enabled tinyint(1) unsigned NOT NULL DEFAULT 1,
				is_public tinyint(1) unsigned NOT NULL DEFAULT 1,
				is_default tinyint(1) unsigned NOT NULL DEFAULT 0,
				position smallint(5) unsigned NOT NULL DEFAULT 0,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY blog_tag (blog_id,tag),
				KEY blog_enabled_position (blog_id,enabled,is_public,position),
				KEY fallback_language_id (fallback_language_id)
			) $c;",
			"CREATE TABLE {$this->tables->groups()} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				blog_id bigint(20) unsigned NOT NULL DEFAULT 1,
				object_type varchar(32) NOT NULL,
				object_subtype varchar(64) NOT NULL DEFAULT '',
				source_blog_id bigint(20) unsigned NOT NULL DEFAULT 1,
				source_object_id bigint(20) unsigned NOT NULL,
				source_fingerprint char(64) NOT NULL DEFAULT '',
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY source_identity (blog_id,object_type,source_blog_id,source_object_id),
				KEY type_subtype (blog_id,object_type,object_subtype)
			) $c;",
			"CREATE TABLE {$this->tables->objects()} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				group_id bigint(20) unsigned NOT NULL,
				blog_id bigint(20) unsigned NOT NULL DEFAULT 1,
				language_id bigint(20) unsigned NOT NULL,
				object_type varchar(32) NOT NULL,
				object_subtype varchar(64) NOT NULL DEFAULT '',
				object_id bigint(20) unsigned NOT NULL,
				source_relation varchar(24) NOT NULL DEFAULT 'translation',
				status varchar(32) NOT NULL DEFAULT 'draft',
				source_revision bigint(20) unsigned NULL,
				source_fingerprint char(64) NOT NULL DEFAULT '',
				version bigint(20) unsigned NOT NULL DEFAULT 1,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY object_identity (blog_id,object_type,object_id),
				UNIQUE KEY group_language (group_id,language_id),
				KEY group_status (group_id,status),
				KEY language_type (language_id,object_type,object_subtype)
			) $c;",
			"CREATE TABLE {$this->tables->strings()} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				stable_hash char(64) NOT NULL,
				text_domain varchar(191) NOT NULL,
				context varchar(191) NOT NULL DEFAULT '',
				original_value longtext NOT NULL,
				normalized_value longtext NOT NULL,
				source_hash char(64) NOT NULL,
				source_location varchar(255) NULL,
				origin varchar(191) NOT NULL DEFAULT '',
				string_type varchar(32) NOT NULL DEFAULT 'text',
				plural_metadata longtext NULL,
				is_active tinyint(1) unsigned NOT NULL DEFAULT 1,
				is_translatable tinyint(1) unsigned NOT NULL DEFAULT 1,
				discovered_at datetime NOT NULL,
				last_seen_at datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY stable_hash (stable_hash),
				KEY domain_active (text_domain,is_active),
				KEY source_hash (source_hash)
			) $c;",
			"CREATE TABLE {$this->tables->stringTranslations()} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				string_id bigint(20) unsigned NOT NULL,
				language_id bigint(20) unsigned NOT NULL,
				translated_value longtext NOT NULL,
				status varchar(32) NOT NULL DEFAULT 'draft',
				translator_id bigint(20) unsigned NULL,
				source_version bigint(20) unsigned NOT NULL DEFAULT 1,
				translation_version bigint(20) unsigned NOT NULL DEFAULT 1,
				reviewer_id bigint(20) unsigned NULL,
				reviewed_at datetime NULL,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY string_language (string_id,language_id),
				KEY language_status (language_id,status)
			) $c;",
			"CREATE TABLE {$this->tables->memory()} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				source_language_id bigint(20) unsigned NOT NULL,
				target_language_id bigint(20) unsigned NOT NULL,
				normalized_source longtext NOT NULL,
				source_hash char(64) NOT NULL,
				translated_segment longtext NOT NULL,
				context_fingerprint char(64) NOT NULL DEFAULT '',
				origin varchar(32) NOT NULL DEFAULT 'human',
				quality_state varchar(24) NOT NULL DEFAULT 'unrated',
				approval_state varchar(24) NOT NULL DEFAULT 'draft',
				usage_count bigint(20) unsigned NOT NULL DEFAULT 0,
				last_used_at datetime NULL,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY exact_match (source_hash,source_language_id,target_language_id,approval_state),
				KEY language_pair (source_language_id,target_language_id,quality_state)
			) $c;",
			"CREATE TABLE {$this->tables->glossary()} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				blog_id bigint(20) unsigned NOT NULL DEFAULT 1,
				source_language_id bigint(20) unsigned NOT NULL,
				target_language_id bigint(20) unsigned NOT NULL,
				source_term varchar(255) NOT NULL,
				target_term varchar(255) NOT NULL DEFAULT '',
				case_sensitive tinyint(1) unsigned NOT NULL DEFAULT 0,
				whole_word tinyint(1) unsigned NOT NULL DEFAULT 1,
				rule_type varchar(24) NOT NULL DEFAULT 'preferred',
				context varchar(255) NULL,
				notes text NULL,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY language_pair (blog_id,source_language_id,target_language_id),
				KEY source_term (source_term(191))
			) $c;",
			"CREATE TABLE {$this->tables->slugs()} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				blog_id bigint(20) unsigned NOT NULL DEFAULT 1,
				object_type varchar(32) NOT NULL,
				object_id bigint(20) unsigned NOT NULL,
				language_id bigint(20) unsigned NOT NULL,
				route_scope varchar(64) NOT NULL DEFAULT '',
				slug varchar(200) NOT NULL,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY object_language (blog_id,object_type,object_id,language_id),
				UNIQUE KEY route_slug (blog_id,language_id,route_scope,slug(150))
			) $c;",
			"CREATE TABLE {$this->tables->jobs()} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				blog_id bigint(20) unsigned NOT NULL DEFAULT 1,
				job_type varchar(64) NOT NULL,
				idempotency_key char(64) NOT NULL,
				status varchar(24) NOT NULL DEFAULT 'pending',
				payload longtext NOT NULL,
				result longtext NULL,
				source_fingerprint char(64) NULL,
				target_version bigint(20) unsigned NULL,
				attempts smallint(5) unsigned NOT NULL DEFAULT 0,
				available_at datetime NOT NULL,
				locked_at datetime NULL,
				locked_by varchar(64) NULL,
				last_error text NULL,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY idempotency_key (idempotency_key),
				KEY runnable (status,available_at),
				KEY blog_type_status (blog_id,job_type,status)
			) $c;",
			"CREATE TABLE {$this->tables->audit()} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				blog_id bigint(20) unsigned NOT NULL DEFAULT 1,
				actor_id bigint(20) unsigned NULL,
				event_type varchar(64) NOT NULL,
				object_type varchar(32) NULL,
				object_id bigint(20) unsigned NULL,
				context longtext NULL,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY blog_event_created (blog_id,event_type,created_at),
				KEY object_lookup (object_type,object_id)
			) $c;",
		);
	}
}
