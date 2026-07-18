<?php

require_once __DIR__ . '/includes/core/functions.php';

/**
 * @return array<string, string>
 */
function redirection_refactor_class_map() {
	return [
		'Redirection' => 'Redirection\\Plugin\\Front',
		'Redirection_Admin' => 'Redirection\\Plugin\\Admin',
		'Redirection_Canonical' => 'Redirection\\Plugin\\Canonical',
		'Redirection_Capabilities' => 'Redirection\\Core\\Capabilities',
		'Redirection_IP' => 'Redirection\\Request\\Ip',
		'Redirection_Request' => 'Redirection\\Request\\Request',
		'Red_Action' => 'Redirection\\Action\\Action',
		'Error_Action' => 'Redirection\\Action\\Error',
		'Nothing_Action' => 'Redirection\\Action\\Nothing',
		'Pass_Action' => 'Redirection\\Action\\Pass',
		'Random_Action' => 'Redirection\\Action\\Random',
		'Url_Action' => 'Redirection\\Action\\Url',
		'Red_Match' => 'Redirection\\Compare\\Compare',
		'Agent_Match' => 'Redirection\\Compare\\Agent',
		'Cookie_Match' => 'Redirection\\Compare\\Cookie',
		'Custom_Match' => 'Redirection\\Compare\\Custom',
		'Header_Match' => 'Redirection\\Compare\\Header',
		'Ip_Match' => 'Redirection\\Compare\\Ip',
		'Language_Match' => 'Redirection\\Compare\\Language',
		'Login_Match' => 'Redirection\\Compare\\Login',
		'Page_Match' => 'Redirection\\Compare\\Page',
		'Referrer_Match' => 'Redirection\\Compare\\Referrer',
		'Role_Match' => 'Redirection\\Compare\\Role',
		'Server_Match' => 'Redirection\\Compare\\Server',
		'URL_Match' => 'Redirection\\Compare\\Url',
		'FromNotFrom_Match' => 'Redirection\\Compare\\FromNotFrom',
		'FromUrl_Match' => 'Redirection\\Compare\\FromUrl',
		'Red_Fixer' => 'Redirection\\Core\\Fixer',
		'Red_Regex' => 'Redirection\\Core\\Regex',
		'Red_Group' => 'Redirection\\Group\\Group',
		'Red_Group_Filters' => 'Redirection\\Group\\Filters',
		'Red_404_Log' => 'Redirection\\Log\\Error',
		'Red_Log' => 'Redirection\\Log\\Log',
		'Red_Redirect_Log' => 'Redirection\\Log\\Redirect',
		'Apache_Module' => 'Redirection\\Module\\Apache',
		'Nginx_Module' => 'Redirection\\Module\\Nginx',
		'Red_Module' => 'Redirection\\Module\\Module',
		'WordPress_Module' => 'Redirection\\Module\\Plugin',
		'Redirect_Cache' => 'Redirection\\Redirect\\Cache',
		'Red_Item' => 'Redirection\\Redirect\\Redirect',
		'Red_Item_Filters' => 'Redirection\\Redirect\\Filters',
		'Red_Item_Sanitize' => 'Redirection\\Redirect\\Sanitize',
		'Red_Source_Options' => 'Redirection\\Redirect\\SourceOptions',
		'Red_Http_Headers' => 'Redirection\\Request\\Headers',
		'Red_Options' => 'Redirection\\Settings\\Settings',
		'Red_Source_Flags' => 'Redirection\\Url\\SourceFlags',
		'Red_Url' => 'Redirection\\Url\\Url',
		'Red_Url_Encode' => 'Redirection\\Url\\Encode',
		'Red_Url_Match' => 'Redirection\\Url\\Matcher',
		'Red_Url_Path' => 'Redirection\\Url\\Path',
		'Red_Url_Query' => 'Redirection\\Url\\Query',
		'Red_Url_Request' => 'Redirection\\Url\\Request',
		'Red_Url_Transform' => 'Redirection\\Url\\Transform',
		'Red_Flusher' => 'Redirection\\Plugin\\Flusher',
		'Red_Monitor' => 'Redirection\\Plugin\\Monitor',
		'Red_Permalinks' => 'Redirection\\Plugin\\Permalinks',
	];
}

/**
 * Check whether a name refers to a declared class, interface, or trait.
 * class_alias() can target any of the three, but class_exists() alone
 * misses traits and interfaces.
 *
 * @param string $name Symbol name.
 * @param bool $autoload Whether to trigger autoloading.
 * @return bool
 */
function redirection_refactor_symbol_exists( $name, $autoload = true ) {
	return class_exists( $name, $autoload ) || interface_exists( $name, $autoload ) || trait_exists( $name, $autoload );
}

/**
 * @param string $requested_class Requested class name.
 * @return void
 */
function redirection_refactor_autoload_legacy( $requested_class ) {
	$map = redirection_refactor_class_map();

	if ( ! isset( $map[ $requested_class ] ) || redirection_refactor_symbol_exists( $requested_class, false ) ) {
		return;
	}

	if ( redirection_refactor_symbol_exists( $map[ $requested_class ] ) ) {
		class_alias( $map[ $requested_class ], $requested_class );
	}
}

spl_autoload_register( 'redirection_refactor_autoload_legacy' );

if ( ! function_exists( 'red_get_plugin_data' ) && function_exists( '\Redirection\Settings\red_get_plugin_data' ) ) {
	/**
	 * @param string $plugin
	 * @return array<string, mixed>
	 */
	function red_get_plugin_data( string $plugin ): array {
		return \Redirection\Settings\red_get_plugin_data( $plugin );
	}
}

if ( ! function_exists( 'red_get_post_types' ) && function_exists( '\Redirection\Settings\red_get_post_types' ) ) {
	/**
	 * @param bool $full
	 * @return array<string, string>|list<string>
	 */
	function red_get_post_types( bool $full = true ): array {
		return \Redirection\Settings\red_get_post_types( $full );
	}
}

if ( ! function_exists( 'red_parse_url' ) && function_exists( '\Redirection\Settings\red_parse_url' ) ) {
	/**
	 * @param string $url
	 * @return array<string, mixed>|false
	 */
	function red_parse_url( string $url ) {
		return \Redirection\Settings\red_parse_url( $url );
	}
}

if ( ! function_exists( 'red_set_options' ) ) {
	/**
	 * @param array<string, mixed> $settings
	 * @return array<string, mixed>
	 */
	function red_set_options( array $settings = [] ) {
		if ( class_exists( 'Red_Options', false ) ) {
			return \Red_Options::save( $settings );
		}

		return \Redirection\Settings\Settings::save( $settings );
	}
}

if ( ! function_exists( 'red_parse_domain_only' ) && function_exists( '\Redirection\Settings\red_parse_domain_only' ) ) {
	/**
	 * @param string $domain
	 * @return string
	 */
	function red_parse_domain_only( string $domain ): string {
		return \Redirection\Settings\red_parse_domain_only( $domain );
	}
}

if ( ! function_exists( 'red_parse_domain_path' ) && function_exists( '\Redirection\Settings\red_parse_domain_path' ) ) {
	/**
	 * @param string $domain
	 * @return string
	 */
	function red_parse_domain_path( string $domain ): string {
		return \Redirection\Settings\red_parse_domain_path( $domain );
	}
}

if ( ! function_exists( 'red_is_disabled' ) && function_exists( '\Redirection\Settings\red_is_disabled' ) ) {
	/**
	 * @return bool
	 */
	function red_is_disabled(): bool {
		return \Redirection\Settings\red_is_disabled();
	}
}

if ( ! function_exists( 'red_get_options' ) ) {
	/**
	 * @return array<string, mixed>
	 */
	function red_get_options() {
		if ( class_exists( 'Red_Options', false ) ) {
			return \Red_Options::get();
		}

		return \Redirection\Settings\Settings::get();
	}
}

if ( ! function_exists( 'red_get_rest_api' ) && function_exists( '\Redirection\Settings\red_get_rest_api' ) ) {
	/**
	 * @param int|false $type
	 * @return string
	 */
	function red_get_rest_api( $type = false ): string {
		return \Redirection\Settings\red_get_rest_api( $type );
	}
}
