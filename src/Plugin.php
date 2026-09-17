<?php

declare(strict_types=1);

namespace MultilingualCore;

use MultilingualCore\Application\AuditLogger;
use MultilingualCore\Application\LanguageContext;
use MultilingualCore\Application\LanguageService;
use MultilingualCore\Application\StringService;
use MultilingualCore\Application\TranslationService;
use MultilingualCore\Application\UrlGenerator;
use MultilingualCore\CLI\Commands;
use MultilingualCore\Infrastructure\Database\TableNames;
use MultilingualCore\Infrastructure\Database\WpdbLanguageRepository;
use MultilingualCore\Infrastructure\Database\WpdbStringRepository;
use MultilingualCore\Infrastructure\Database\WpdbTranslationRepository;
use MultilingualCore\Infrastructure\WordPress\Lifecycle;
use MultilingualCore\Infrastructure\WordPress\WordPressCache;
use MultilingualCore\Presentation\Admin\Admin;
use MultilingualCore\Presentation\Frontend\Hreflang;
use MultilingualCore\Presentation\Frontend\LanguageSwitcher;
use MultilingualCore\Presentation\Frontend\Routing;
use MultilingualCore\Presentation\Rest\LanguageController;
use MultilingualCore\Presentation\Rest\TranslationController;

final class Plugin {

	private static ?self $instance = null;
	private readonly LanguageService $languages;
	private readonly TranslationService $translations;
	private readonly StringService $strings;
	private readonly LanguageContext $context;
	private readonly UrlGenerator $urls;

	private function __construct() {
		global $wpdb;

		$tables             = new TableNames( $wpdb->base_prefix );
		$cache              = new WordPressCache();
		$audit              = new AuditLogger( $wpdb, $tables );
		$this->languages    = new LanguageService( new WpdbLanguageRepository( $wpdb, $tables, $cache ), $audit );
		$this->translations = new TranslationService( new WpdbTranslationRepository( $wpdb, $tables, $cache ) );
		$this->strings      = new StringService( new WpdbStringRepository( $wpdb, $tables, $cache ) );
		$this->context      = new LanguageContext( $this->languages );
		$this->urls         = new UrlGenerator( $this->languages );
	}

	public static function boot(): self {
		self::$instance ??= new self();

		return self::$instance;
	}

	public function register(): void {
		global $wpdb;
		$tables = new TableNames( $wpdb->base_prefix );

		load_plugin_textdomain( 'multilingual-core', false, dirname( plugin_basename( MLC_PLUGIN_FILE ) ) . '/languages' );
		add_action( 'admin_init', array( Lifecycle::class, 'maybeUpgrade' ) );
		add_action( 'init', array( Lifecycle::class, 'maybeFlushRewriteRules' ), 99 );
		add_action(
			'wp_initialize_site',
			static function ( \WP_Site $site ): void {
				switch_to_blog( (int) $site->blog_id );
				try {
					( new \MultilingualCore\Infrastructure\Database\Installer( $GLOBALS['wpdb'] ) )->seedLanguage( (int) $site->blog_id );
				} finally {
					restore_current_blog();
				}
			}
		);

		( new Routing( $this->languages ) )->register();
		$switcher = new LanguageSwitcher( $this->languages, $this->context, $this->translations, $this->urls );
		add_action( 'init', array( $switcher, 'register' ) );
		$hreflang = new Hreflang( $this->translations, $this->languages, $this->urls );
		add_action( 'wp_head', array( $hreflang, 'render' ), 2 );

		$languageRest    = new LanguageController( $this->languages );
		$translationRest = new TranslationController( $this->translations, $this->urls );
		add_action( 'rest_api_init', array( $languageRest, 'register' ) );
		add_action( 'rest_api_init', array( $translationRest, 'register' ) );

		if ( is_admin() ) {
			( new Admin( $this->languages, $wpdb, $tables ) )->register();
		}
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			$commands = new Commands( $this->languages, $wpdb, $tables );
			\WP_CLI::add_command( 'multilingual-core language list', array( $commands, 'languageList' ) );
			\WP_CLI::add_command( 'multilingual-core health', array( $commands, 'health' ) );
			\WP_CLI::add_command( 'multilingual-core migrate', array( $commands, 'migrate' ) );
		}
	}

	public function languages(): LanguageService {
		return $this->languages;
	}

	public function translations(): TranslationService {
		return $this->translations;
	}

	public function strings(): StringService {
		return $this->strings;
	}

	public function context(): LanguageContext {
		return $this->context;
	}

	public function urls(): UrlGenerator {
		return $this->urls;
	}
}
