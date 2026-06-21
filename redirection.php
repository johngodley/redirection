<?php
/*
Plugin Name: Redirection
Plugin URI: https://redirection.me/
Description: Manage all your 301 redirects and monitor 404 errors
Version: 5.8.0
Author: John Godley
Text Domain: redirection
Requires PHP: 7.4
Requires at least: 6.6
============================================================================================================
For full license details see license.txt
============================================================================================================
*/

define( 'REDIRECTION_DB_VERSION', '4.2' );     // DB schema version. Only change if DB needs changing
define( 'REDIRECTION_FILE', __FILE__ );

if ( ! defined( 'REDIRECTION_FLYING_SOLO' ) ) {
	define( 'REDIRECTION_FLYING_SOLO', apply_filters( 'redirection_flying_solo', true ) );
}

// This file must support PHP < 7.4 so as not to crash
if ( version_compare( PHP_VERSION, '7.4' ) < 0 ) {
	add_filter( 'plugin_action_links_' . basename( dirname( REDIRECTION_FILE ) ) . '/' . basename( REDIRECTION_FILE ), 'red_deprecated_php' );

	/**
	 * @param array<string> $links
	 * @return array<string>
	 */
	function red_deprecated_php( array $links ): array {
		/* translators: 1: server PHP version. 2: required PHP version. */
		array_unshift( $links, '<a href="https://redirection.me/support/problems/php-version/" style="color: red; text-decoration: underline">' . sprintf( __( 'Disabled! Detected PHP %1$s, need PHP %2$s+', 'redirection' ), phpversion(), '7.4' ) . '</a>' );
		return $links;
	}

	return;
}

require_once __DIR__ . '/build/redirection-version.php';
require_once __DIR__ . '/redirection-settings.php';
require_once __DIR__ . '/models/options.php';
require_once __DIR__ . '/models/redirect/redirect.php';
require_once __DIR__ . '/models/url/url.php';
require_once __DIR__ . '/models/regex.php';
require_once __DIR__ . '/models/module.php';
require_once __DIR__ . '/models/log/log.php';
require_once __DIR__ . '/models/flusher.php';
require_once __DIR__ . '/models/match.php';
require_once __DIR__ . '/models/action.php';
require_once __DIR__ . '/models/request.php';
require_once __DIR__ . '/models/header.php';
require_once __DIR__ . '/models/group.php';

/**
 * Autoload the migrated file I/O classes only.
 *
 * This lets us adopt autoloading incrementally for admin/CLI-only paths
 * without changing the rest of the plugin bootstrap in one step.
 *
 * @param string $requested_class Requested class name.
 * @return void
 */
function redirection_autoload_fileio( $requested_class ) {
	$legacy = [
		'Red_FileIO' => __DIR__ . '/includes/fileio/class-fileio.php',
		'Red_Htaccess' => __DIR__ . '/includes/fileio/class-htaccess.php',
		'Red_Apache_File' => __DIR__ . '/includes/fileio/format/class-apache.php',
		'Red_Csv_File' => __DIR__ . '/includes/fileio/format/class-csv.php',
		'Red_Json_File' => __DIR__ . '/includes/fileio/format/class-json.php',
		'Red_Nginx_File' => __DIR__ . '/includes/fileio/format/class-nginx.php',
		'Red_Rss_File' => __DIR__ . '/includes/fileio/format/class-rss.php',
	];

	if ( isset( $legacy[ $requested_class ] ) ) {
		require_once $legacy[ $requested_class ];
		return;
	}

	$prefix = 'Redirection\\FileIO\\';
	if ( strncmp( $prefix, $requested_class, strlen( $prefix ) ) ) {
		return;
	}

	$relative_class = substr( $requested_class, strlen( $prefix ) );
	if ( $relative_class === '' ) {
		return;
	}

	$normalize = static function ( $value ) {
		return str_replace( '_', '-', strtolower( $value ) );
	};

	$segments = explode( '\\', $relative_class );
	$class_name = array_pop( $segments );
	if ( ! is_string( $class_name ) || $class_name === '' ) {
		return;
	}

	$base_dir = __DIR__ . '/includes/fileio/';

	if ( count( $segments ) > 0 ) {
		$base_dir .= implode( '/', array_map( $normalize, $segments ) ) . '/';
	}

	$path = $base_dir . 'class-' . $normalize( $class_name ) . '.php';

	if ( file_exists( $path ) ) {
		require_once $path;
	}
}

spl_autoload_register( 'redirection_autoload_fileio' );

/**
 * Clear PHP opcache when plugin is updated. This is to help with mid-update errors.
 *
 * @param object $upgrader The upgrader object.
 * @param array{action: string, type: string, plugins?: string[]} $options The upgrade options.
 * @return void
 */
function redirection_clear_opcache_on_upgrade( $upgrader, $options ) {
	if ( $options['action'] !== 'update' || $options['type'] !== 'plugin' ) {
		return;
	}

	$plugin_basename = plugin_basename( REDIRECTION_FILE );
	$plugins = $options['plugins'] ?? [];

	if ( ! in_array( $plugin_basename, $plugins, true ) ) {
		return;
	}

	if ( function_exists( 'opcache_reset' ) ) {
		// Suppress warnings if opcache_reset is restricted by server configuration
		@opcache_reset(); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}
}

add_action( 'upgrader_process_complete', 'redirection_clear_opcache_on_upgrade', 10, 2 );

/**
 * @return bool
 */
function red_is_wpcli() {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return true;
	}

	return false;
}

/**
 * Detect a plain PHP CLI context (e.g., a cron script that loads wp-load.php
 * directly). Distinct from red_is_wpcli(), which is only true under WP-CLI.
 * Used to skip front-end redirect enforcement that would otherwise call die()
 * and silently terminate the CLI process.
 *
 * @return bool
 */
function red_is_cli() {
	return PHP_SAPI === 'cli';
}

/**
 * @return bool
 */
function red_is_admin() {
	if ( is_admin() ) {
		return true;
	}

	return false;
}

/**
 * @return void
 */
function red_start_rest() {
	require_once __DIR__ . '/redirection-admin.php';
	require_once __DIR__ . '/api/api.php';

	Redirection_Api::init();
	Redirection_Admin::init();

	remove_action( 'rest_api_init', 'red_start_rest' );
}

/**
 * @return void
 */
function redirection_locale() {
	load_plugin_textdomain( 'redirection', false, dirname( plugin_basename( REDIRECTION_FILE ) ) . '/locale/' );
}

if ( red_is_admin() || red_is_wpcli() ) {
	require_once __DIR__ . '/redirection-admin.php';
	require_once __DIR__ . '/api/api.php';
} else {
	require_once __DIR__ . '/redirection-front.php';
}

if ( red_is_wpcli() ) {
	require_once __DIR__ . '/redirection-cli.php';
}

add_action( 'rest_api_init', 'red_start_rest' );
add_action( 'init', 'redirection_locale' );
