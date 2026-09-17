<?php
/**
 * Plugin Name:       Multilingual Core
 * Description:       A performance-first, extensible multilingual platform for WordPress.
 * Version:           0.1.0
 * Requires at least: 6.9
 * Requires PHP:      8.1
 * Author:            Multilingual Core contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       multilingual-core
 * Domain Path:       /languages
 * Network:           true
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MLC_VERSION', '0.1.0' );
define( 'MLC_SCHEMA_VERSION', '1.0.0' );
define( 'MLC_PLUGIN_FILE', __FILE__ );
define( 'MLC_PLUGIN_DIR', __DIR__ . '/' );

$mlc_autoload = MLC_PLUGIN_DIR . 'vendor/autoload.php';
if ( is_readable( $mlc_autoload ) ) {
	require_once $mlc_autoload;
} else {
	require_once MLC_PLUGIN_DIR . 'src/Autoloader.php';
	MultilingualCore\Autoloader::register( MLC_PLUGIN_DIR . 'src/' );
}
require_once MLC_PLUGIN_DIR . 'includes/api.php';

register_activation_hook( __FILE__, array( MultilingualCore\Infrastructure\WordPress\Lifecycle::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( MultilingualCore\Infrastructure\WordPress\Lifecycle::class, 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function (): void {
		MultilingualCore\Plugin::boot()->register();
	},
	5
);
