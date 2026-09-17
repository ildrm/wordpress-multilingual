<?php

declare(strict_types=1);

namespace MultilingualCore\Presentation\Admin;

use InvalidArgumentException;
use MultilingualCore\Application\LanguageService;
use MultilingualCore\Infrastructure\Database\Sql;
use MultilingualCore\Infrastructure\Database\TableNames;
use RuntimeException;
use wpdb;

final class Admin {

	public function __construct(
		private readonly LanguageService $languages,
		private readonly wpdb $database,
		private readonly TableNames $tables
	) {
	}

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_post_mlc_save_language', array( $this, 'saveLanguage' ) );
		add_action( 'admin_post_mlc_set_default_language', array( $this, 'setDefaultLanguage' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
	}

	public function menu(): void {
		add_menu_page(
			__( 'Multilingual', 'multilingual-core' ),
			__( 'Multilingual', 'multilingual-core' ),
			'mlc_translate',
			'multilingual-core',
			array( $this, 'dashboard' ),
			'dashicons-translation',
			58
		);
		add_submenu_page( 'multilingual-core', __( 'Dashboard', 'multilingual-core' ), __( 'Dashboard', 'multilingual-core' ), 'mlc_translate', 'multilingual-core', array( $this, 'dashboard' ) );
		add_submenu_page( 'multilingual-core', __( 'Languages', 'multilingual-core' ), __( 'Languages', 'multilingual-core' ), 'mlc_manage_languages', 'mlc-languages', array( $this, 'languagePage' ) );
		add_submenu_page( 'multilingual-core', __( 'Translation Health', 'multilingual-core' ), __( 'Health', 'multilingual-core' ), 'mlc_view_health', 'mlc-health', array( $this, 'healthPage' ) );
	}

	public function assets( string $hook ): void {
		if ( ! str_contains( $hook, 'multilingual-core' ) && ! str_contains( $hook, 'mlc-' ) ) {
			return;
		}
		wp_enqueue_style( 'mlc-admin', plugins_url( 'assets/admin.css', MLC_PLUGIN_FILE ), array(), MLC_VERSION );
	}

	public function dashboard(): void {
		$this->guard( 'mlc_translate' );
		$languages = $this->languages->all();
		$counts    = $this->statusCounts();
		?>
		<div class="wrap mlc-wrap">
			<h1><?php esc_html_e( 'Multilingual dashboard', 'multilingual-core' ); ?></h1>
			<p class="description"><?php esc_html_e( 'See language coverage and the work that needs attention.', 'multilingual-core' ); ?></p>
			<div class="mlc-cards" role="list">
				<div class="mlc-card" role="listitem"><strong><?php echo esc_html( (string) count( $languages ) ); ?></strong><span><?php esc_html_e( 'Configured languages', 'multilingual-core' ); ?></span><a href="<?php echo esc_url( admin_url( 'admin.php?page=mlc-languages' ) ); ?>"><?php esc_html_e( 'Manage languages', 'multilingual-core' ); ?></a></div>
				<div class="mlc-card" role="listitem"><strong><?php echo esc_html( (string) ( $counts['stale'] ?? 0 ) ); ?></strong><span><?php esc_html_e( 'Stale translations', 'multilingual-core' ); ?></span></div>
				<div class="mlc-card" role="listitem"><strong><?php echo esc_html( (string) ( $counts['needs_review'] ?? 0 ) ); ?></strong><span><?php esc_html_e( 'Need review', 'multilingual-core' ); ?></span></div>
				<div class="mlc-card" role="listitem"><strong><?php echo esc_html( (string) ( $counts['failed'] ?? 0 ) ); ?></strong><span><?php esc_html_e( 'Failed translations', 'multilingual-core' ); ?></span><a href="<?php echo esc_url( admin_url( 'admin.php?page=mlc-health' ) ); ?>"><?php esc_html_e( 'Open health checks', 'multilingual-core' ); ?></a></div>
			</div>
		</div>
		<?php
	}

	public function languagePage(): void {
		$this->guard( 'mlc_manage_languages' );
		$languages = $this->languages->all();
		?>
		<div class="wrap mlc-wrap">
			<h1><?php esc_html_e( 'Languages', 'multilingual-core' ); ?></h1>
			<p class="description"><?php esc_html_e( 'Add regional or custom languages without assuming that a language represents one country.', 'multilingual-core' ); ?></p>
			<?php $this->notice(); ?>
			<table class="widefat striped mlc-table">
				<thead><tr><th><?php esc_html_e( 'Language', 'multilingual-core' ); ?></th><th><?php esc_html_e( 'Tag / locale', 'multilingual-core' ); ?></th><th><?php esc_html_e( 'Direction', 'multilingual-core' ); ?></th><th><?php esc_html_e( 'Status', 'multilingual-core' ); ?></th><th><?php esc_html_e( 'Action', 'multilingual-core' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( $languages as $language ) : ?>
					<tr>
						<td><span lang="<?php echo esc_attr( (string) $language['tag'] ); ?>" dir="<?php echo esc_attr( (string) $language['direction'] ); ?>"><?php echo esc_html( (string) $language['native_name'] ); ?></span><br><small><?php echo esc_html( (string) $language['admin_name'] ); ?></small></td>
						<td><code><?php echo esc_html( (string) $language['tag'] ); ?></code><br><?php echo esc_html( (string) $language['wp_locale'] ); ?></td>
						<td><?php echo esc_html( strtoupper( (string) $language['direction'] ) ); ?></td>
						<td><?php echo $language['is_default'] ? esc_html__( 'Default', 'multilingual-core' ) : ( $language['enabled'] ? esc_html__( 'Enabled', 'multilingual-core' ) : esc_html__( 'Disabled', 'multilingual-core' ) ); ?></td>
						<td>
						<?php if ( ! $language['is_default'] && $language['enabled'] ) : ?>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
								<input type="hidden" name="action" value="mlc_set_default_language">
								<input type="hidden" name="language_id" value="<?php echo esc_attr( (string) $language['id'] ); ?>">
								<?php wp_nonce_field( 'mlc_set_default_language' ); ?>
								<label><input type="checkbox" name="impact_acknowledged" value="1" required> <?php esc_html_e( 'I understand URLs and hreflang may change.', 'multilingual-core' ); ?></label>
								<button class="button" type="submit"><?php esc_html_e( 'Make default', 'multilingual-core' ); ?></button>
							</form>
						<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<h2><?php esc_html_e( 'Add a language', 'multilingual-core' ); ?></h2>
			<form class="mlc-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="mlc_save_language">
				<?php wp_nonce_field( 'mlc_save_language' ); ?>
				<p><label><?php esc_html_e( 'Language tag', 'multilingual-core' ); ?><input required name="tag" placeholder="fa-IR" aria-describedby="mlc-tag-help"></label><span id="mlc-tag-help" class="description"><?php esc_html_e( 'Use a BCP 47 tag, such as fa, en-GB, or zh-Hant-TW.', 'multilingual-core' ); ?></span></p>
				<p><label><?php esc_html_e( 'WordPress locale', 'multilingual-core' ); ?><input required name="wp_locale" placeholder="fa_IR"></label></p>
				<p><label><?php esc_html_e( 'Native name', 'multilingual-core' ); ?><input required name="native_name" placeholder="فارسی"></label></p>
				<p><label><?php esc_html_e( 'Administrator name', 'multilingual-core' ); ?><input required name="admin_name" placeholder="Persian"></label></p>
				<p><label><?php esc_html_e( 'Text direction', 'multilingual-core' ); ?><select name="direction"><option value="ltr"><?php esc_html_e( 'Left to right', 'multilingual-core' ); ?></option><option value="rtl"><?php esc_html_e( 'Right to left', 'multilingual-core' ); ?></option></select></label></p>
				<?php submit_button( __( 'Add language', 'multilingual-core' ) ); ?>
			</form>
		</div>
		<?php
	}

	public function healthPage(): void {
		$this->guard( 'mlc_view_health' );
		$checks = $this->healthChecks();
		?>
		<div class="wrap mlc-wrap"><h1><?php esc_html_e( 'Translation Health', 'multilingual-core' ); ?></h1><p class="description"><?php esc_html_e( 'Actionable checks for schema, routing, caching, and translation integrity.', 'multilingual-core' ); ?></p>
			<ul class="mlc-health">
			<?php foreach ( $checks as $check ) : ?>
				<li class="mlc-health--<?php echo esc_attr( $check['status'] ); ?>"><strong><?php echo esc_html( $check['label'] ); ?></strong><span><?php echo esc_html( $check['message'] ); ?></span></li>
			<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}

	public function saveLanguage(): void {
		$this->guard( 'mlc_manage_languages' );
		check_admin_referer( 'mlc_save_language' );
		try {
			$this->languages->save(
				array(
					'tag'         => isset( $_POST['tag'] ) ? sanitize_text_field( wp_unslash( $_POST['tag'] ) ) : '',
					'wp_locale'   => isset( $_POST['wp_locale'] ) ? sanitize_text_field( wp_unslash( $_POST['wp_locale'] ) ) : '',
					'native_name' => isset( $_POST['native_name'] ) ? sanitize_text_field( wp_unslash( $_POST['native_name'] ) ) : '',
					'admin_name'  => isset( $_POST['admin_name'] ) ? sanitize_text_field( wp_unslash( $_POST['admin_name'] ) ) : '',
					'direction'   => isset( $_POST['direction'] ) ? sanitize_key( wp_unslash( $_POST['direction'] ) ) : 'ltr',
				)
			);
			$this->redirectNotice( 'success', __( 'Language added.', 'multilingual-core' ) );
		} catch ( InvalidArgumentException | RuntimeException $exception ) {
			$this->redirectNotice( 'error', $exception->getMessage() );
		}
	}

	public function setDefaultLanguage(): void {
		$this->guard( 'mlc_manage_languages' );
		check_admin_referer( 'mlc_set_default_language' );
		try {
			$this->languages->setDefault(
				isset( $_POST['language_id'] ) ? absint( $_POST['language_id'] ) : 0,
				isset( $_POST['impact_acknowledged'] ) && '1' === $_POST['impact_acknowledged']
			);
			update_option( 'mlc_rewrite_flush_required', 1, false );
			$this->redirectNotice( 'success', __( 'Default language changed.', 'multilingual-core' ) );
		} catch ( RuntimeException $exception ) {
			$this->redirectNotice( 'error', $exception->getMessage() );
		}
	}

	/** @return array<string, int> */
	private function statusCounts(): array {
		$rows   = $this->database->get_results(
			Sql::prepare( $this->database, "SELECT status, COUNT(*) total FROM {$this->tables->objects()} WHERE blog_id = %d GROUP BY status", get_current_blog_id() ),
			ARRAY_A
		);
		$counts = array();
		foreach ( is_array( $rows ) ? $rows : array() as $row ) {
			$counts[ (string) $row['status'] ] = (int) $row['total'];
		}

		return $counts;
	}

	/** @return list<array{status:string,label:string,message:string}> */
	private function healthChecks(): array {
		$missing = array();
		foreach ( $this->tables->all() as $table ) {
			$found = $this->database->get_var( $this->database->prepare( 'SHOW TABLES LIKE %s', $table ) );
			if ( $found !== $table ) {
				$missing[] = $table;
			}
		}

		return array(
			array(
				'status'  => array() === $missing ? 'good' : 'error',
				'label'   => __( 'Database schema', 'multilingual-core' ),
				// Translators: %d is the number of missing plugin database tables.
				'message' => array() === $missing ? __( 'All plugin tables are available.', 'multilingual-core' ) : sprintf( __( 'Missing %d plugin tables. Deactivate and reactivate after taking a backup.', 'multilingual-core' ), count( $missing ) ),
			),
			array(
				'status'  => wp_using_ext_object_cache() ? 'good' : 'notice',
				'label'   => __( 'Object cache', 'multilingual-core' ),
				'message' => wp_using_ext_object_cache() ? __( 'A persistent object cache is active.', 'multilingual-core' ) : __( 'No persistent object cache is active; correctness is unaffected.', 'multilingual-core' ),
			),
			array(
				'status'  => wp_next_scheduled( 'mlc_process_jobs' ) ? 'good' : 'notice',
				'label'   => __( 'Background queue', 'multilingual-core' ),
				'message' => wp_next_scheduled( 'mlc_process_jobs' ) ? __( 'The queue runner is scheduled.', 'multilingual-core' ) : __( 'No queue work is scheduled.', 'multilingual-core' ),
			),
		);
	}

	private function notice(): void {
		$key    = 'mlc_notice_' . get_current_user_id();
		$notice = get_transient( $key );
		delete_transient( $key );
		if ( is_array( $notice ) ) {
			printf( '<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>', esc_attr( (string) $notice['type'] ), esc_html( (string) $notice['message'] ) );
		}
	}

	private function redirectNotice( string $type, string $message ): never {
		set_transient(
			'mlc_notice_' . get_current_user_id(),
			array(
				'type'    => $type,
				'message' => $message,
			),
			MINUTE_IN_SECONDS
		);
		wp_safe_redirect( admin_url( 'admin.php?page=mlc-languages' ) );
		exit;
	}

	private function guard( string $capability ): void {
		if ( ! current_user_can( $capability ) ) {
			wp_die( esc_html__( 'You are not allowed to perform this action.', 'multilingual-core' ), '', array( 'response' => 403 ) );
		}
	}
}
