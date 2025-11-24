<?php

/**
 * Redirection options manager - pure static utility class.
 *
 * Usage: Red_Options::get() returns a fully typed array.
 *
 * @phpstan-type RedirectionOptions array{
 *    support: bool,
 *    token: string,
 *    monitor_post: int,
 *    monitor_types: array<string>,
 *    associated_redirect: string,
 *    auto_target: string,
 *    expire_redirect: int,
 *    expire_404: int,
 *    log_external: bool,
 *    log_header: bool,
 *    track_hits: bool,
 *    modules: array<int, mixed>,
 *    redirect_cache: int,
 *    ip_logging: int,
 *    ip_headers: array<string>,
 *    ip_proxy: array<string>,
 *    last_group_id: int,
 *    rest_api: int,
 *    https: bool,
 *    headers: array<mixed>,
 *    database: string,
 *    relocate: string,
 *    preferred_domain: string,
 *    aliases: array<string>,
 *    permalinks: array<mixed>,
 *    cache_key: int,
 *    plugin_update: string,
 *    update_notice: int,
 *    flag_query: 'ignore'|'exact'|'pass'|'exactorder',
 *    flag_case: bool,
 *    flag_trailing: bool,
 *    flag_regex: bool,
 *    database_stage?: array{stage?: string|false, stages?: array<mixed>, status?: string|false},
 *    location?: string
 * }
 */
class Red_Options {
	/**
	 * Options DB key. Previously defined by REDIRECTION_OPTION.
	 */
	public const OPTION_KEY = 'redirection_options';

	/**
	 * REST API location constants. Previously REDIRECTION_API_JSON*, now centralized here.
	 */
	public const API_JSON = 0;
	public const API_JSON_INDEX = 1;
	public const API_JSON_RELATIVE = 3;

	/**
	 * In-memory cache for build_options result.
	 *
	 * @var RedirectionOptions|null
	 */
	private static $options_cache = null;

	/**
	 * Prevent instantiation - this is a static utility class.
	 */
	private function __construct() {
	}

	/**
	 * Load current options and return as a typed array.
	 *
	 * @phpstan-return RedirectionOptions
	 * @return array
	 */
	public static function get(): array {
		return self::build_options();
	}

	/**
	 * Reset the options cache.
	 * @return void
	 */
	public static function reset() {
		self::$options_cache = null;
	}

	/**
	 * Build Redirection options (array form).
	 * Mirrors legacy red_get_options() behavior.
	 *
	 * @phpstan-return RedirectionOptions
	 * @return array
	 */
	private static function build_options(): array {
		// Return cached result if available
		if ( self::$options_cache !== null ) {
			return self::$options_cache;
		}

		$options = get_option( self::OPTION_KEY );
		$fresh_install = false;

		if ( $options === false ) {
			$fresh_install = true;
		}

		if ( ! is_array( $options ) ) {
			$options = [];
		}

		if ( red_is_disabled() ) {
			$options['https'] = false;
		}

		$defaults = self::get_default_options();

		foreach ( $defaults as $key => $value ) {
			if ( ! isset( $options[ $key ] ) ) {
				$options[ $key ] = $value;
			}
		}

		if ( $fresh_install ) {
			// Default flags for new installs - ignore case and trailing slashes
			$options['flag_case'] = true;
			$options['flag_trailing'] = true;
		}

		// Back-compat. If monitor_post is set without types then it's from an older Redirection
		if ( $options['monitor_post'] > 0 && count( $options['monitor_types'] ) === 0 ) {
			$options['monitor_types'] = [ 'post' ];
		}

		// Remove old options not in get_default_options()
		foreach ( array_keys( $options ) as $key ) {
			if ( ! isset( $defaults[ $key ] ) && $key !== 'database_stage' ) {
				unset( $options[ $key ] );
			}
		}

		// Back-compat fix
		if ( $options['rest_api'] === false || ! in_array( $options['rest_api'], [ self::API_JSON, self::API_JSON_INDEX, self::API_JSON_RELATIVE ], true ) ) {
			$options['rest_api'] = self::API_JSON;
		}

		if ( isset( $options['modules'] ) && isset( $options['modules']['2'] ) && isset( $options['modules']['2']['location'] ) ) {
			$options['location'] = $options['modules']['2']['location'];
		}

		/** @var RedirectionOptions $options */
		// Cache the result before returning
		self::$options_cache = $options;
		return $options;
	}

	/**
	 * Get default options. Contains all valid options.
	 *
	 * @phpstan-return RedirectionOptions
	 * @return array<string, mixed>
	 */
	public static function get_default_options() {
		$flags = new Red_Source_Flags();
		$defaults = [
			'support' => false,
			'token' => md5( uniqid() ),
			'monitor_post' => 0,
			'monitor_types' => [],
			'associated_redirect' => '',
			'auto_target' => '',
			'expire_redirect' => 7,
			'expire_404' => 7,
			'log_external' => false,
			'log_header' => false,
			'track_hits' => true,
			'modules' => [],
			'redirect_cache' => 1,
			'ip_logging' => 0,
			'ip_headers' => [],
			'ip_proxy' => [],
			'last_group_id' => 0,
			'rest_api' => self::API_JSON,
			'https' => false,
			'headers' => [],
			'database' => '',
			'relocate' => '',
			'preferred_domain' => '',
			'aliases' => [],
			'permalinks' => [],
			'cache_key' => 0,
			'plugin_update' => 'prompt',
			'update_notice' => 0,
		];

		$defaults = array_merge( $defaults, $flags->get_json() );

		return apply_filters( 'red_default_options', $defaults );
	}

	/**
	 * Persist option values using the same sanitization/merge rules as legacy red_set_options.
	 * This is a static method that saves options and returns the updated array.
	 *
	 * @param array<string, mixed> $settings Partial settings to apply.
	 * @phpstan-return RedirectionOptions
	 * @return array
	 */
	public static function save( array $settings ): array {
		// Clear the cache before applying settings
		self::$options_cache = null;

		$options = self::apply_settings( $settings );
		update_option( self::OPTION_KEY, apply_filters( 'redirection_save_options', $options ) );

		// Clear the cache after saving to ensure fresh data on next get()
		self::$options_cache = null;

		/** @var RedirectionOptions $options */
		return $options;
	}

	/**
	 * Apply settings and return the updated options array without saving.
	 *
	 * @param array<string, mixed> $settings Partial settings to apply.
	 * @phpstan-return RedirectionOptions
	 * @return array
	 */
	private static function apply_settings( array $settings ): array {
		$options = self::build_options();
		$monitor_types = [];

		if ( isset( $settings['database'] ) ) {
			$options['database'] = sanitize_text_field( $settings['database'] );
		}

		if ( array_key_exists( 'database_stage', $settings ) ) {
			if ( $settings['database_stage'] === false ) {
				unset( $options['database_stage'] );
			} else {
				$options['database_stage'] = $settings['database_stage'];
			}
		}

		if ( isset( $settings['ip_proxy'] ) && is_array( $settings['ip_proxy'] ) ) {
			$options['ip_proxy'] = array_map(
				function ( $ip ) {
					$ip = new Redirection_IP( $ip );
					return $ip->get();
				},
				$settings['ip_proxy']
			);

			$options['ip_proxy'] = array_values( array_filter( $options['ip_proxy'] ) );
		}

		if ( isset( $settings['ip_headers'] ) && is_array( $settings['ip_headers'] ) ) {
			$available = Redirection_Request::get_ip_headers();
			$options['ip_headers'] = array_filter(
				$settings['ip_headers'],
				function ( $header ) use ( $available ) {
					return in_array( $header, $available, true );
				}
			);
			$options['ip_headers'] = array_values( $options['ip_headers'] );
		}

		if ( isset( $settings['rest_api'] ) && in_array( intval( $settings['rest_api'], 10 ), array( 0, 1, 2, 3, 4 ), true ) ) {
			$options['rest_api'] = intval( $settings['rest_api'], 10 );
		}

		if ( isset( $settings['monitor_types'] ) && is_array( $settings['monitor_types'] ) ) {
			$allowed = red_get_post_types( false );

			foreach ( $settings['monitor_types'] as $type ) {
				if ( in_array( $type, $allowed, true ) ) {
					$monitor_types[] = $type;
				}
			}

			$options['monitor_types'] = $monitor_types;
		}

		if ( isset( $settings['associated_redirect'] ) && is_string( $settings['associated_redirect'] ) ) {
			$options['associated_redirect'] = '';

			if ( strlen( $settings['associated_redirect'] ) > 0 ) {
				$sanitizer = new Red_Item_Sanitize();
				$options['associated_redirect'] = trim( $sanitizer->sanitize_url( $settings['associated_redirect'] ) );
			}
		}

		if ( isset( $settings['monitor_types'] ) && count( $monitor_types ) === 0 ) {
			$options['monitor_post'] = 0;
			$options['associated_redirect'] = '';
		} elseif ( isset( $settings['monitor_post'] ) ) {
			$options['monitor_post'] = max( 0, intval( $settings['monitor_post'], 10 ) );

			if ( Red_Group::get( $options['monitor_post'] ) === false && $options['monitor_post'] !== 0 ) {
				$groups = Red_Group::get_all();

				if ( count( $groups ) > 0 ) {
					$options['monitor_post'] = $groups[0]['id'];
				}
			}
		}

		if ( isset( $settings['auto_target'] ) && is_string( $settings['auto_target'] ) ) {
			$options['auto_target'] = sanitize_text_field( $settings['auto_target'] );
		}

		if ( isset( $settings['last_group_id'] ) ) {
			$options['last_group_id'] = max( 0, intval( $settings['last_group_id'], 10 ) );

			if ( Red_Group::get( $options['last_group_id'] ) === false ) {
				$groups = Red_Group::get_all();
				$options['last_group_id'] = $groups[0]['id'];
			}
		}

		if ( isset( $settings['token'] ) && is_string( $settings['token'] ) ) {
			$options['token'] = sanitize_text_field( $settings['token'] );
		}

		if ( isset( $settings['token'] ) && trim( $options['token'] ) === '' ) {
			$options['token'] = md5( uniqid() );
		}

		// Boolean settings
		foreach ( [ 'support', 'https', 'log_external', 'log_header', 'track_hits' ] as $name ) {
			if ( isset( $settings[ $name ] ) ) {
				$options[ $name ] = $settings[ $name ] ? true : false;
			}
		}

		if ( isset( $settings['expire_redirect'] ) ) {
			$options['expire_redirect'] = max( -1, min( intval( $settings['expire_redirect'], 10 ), 60 ) );
		}

		if ( isset( $settings['expire_404'] ) ) {
			$options['expire_404'] = max( -1, min( intval( $settings['expire_404'], 10 ), 60 ) );
		}

		if ( isset( $settings['ip_logging'] ) ) {
			$options['ip_logging'] = max( 0, min( 2, intval( $settings['ip_logging'], 10 ) ) );
		}

		if ( isset( $settings['redirect_cache'] ) ) {
			$options['redirect_cache'] = intval( $settings['redirect_cache'], 10 );

			if ( ! in_array( $options['redirect_cache'], array( -1, 0, 1, 24, 24 * 7 ), true ) ) {
				$options['redirect_cache'] = 1;
			}
		}

		if ( isset( $settings['location'] ) && ( ! isset( $options['location'] ) || $options['location'] !== $settings['location'] ) ) {
			$module = Red_Module::get( 2 );

			if ( $module !== false ) {
				$options['modules'][2] = $module->update( $settings );
			}
		}

		if ( ! empty( $options['monitor_post'] ) && count( $options['monitor_types'] ) === 0 ) {
			// If we have a monitor_post set, but no types, then blank everything
			$options['monitor_post'] = 0;
			$options['associated_redirect'] = '';
		}

		if ( isset( $settings['plugin_update'] ) && in_array( $settings['plugin_update'], [ 'prompt', 'admin' ], true ) ) {
			$options['plugin_update'] = sanitize_text_field( $settings['plugin_update'] );
		}

		$flags = new Red_Source_Flags();
		$flags_present = [];

		foreach ( array_keys( $flags->get_json() ) as $flag ) {
			if ( isset( $settings[ $flag ] ) ) {
				$flags_present[ $flag ] = $settings[ $flag ];
			}
		}

		if ( count( $flags_present ) > 0 ) {
			$flags->set_flags( $flags_present );
			$options = array_merge( $options, $flags->get_json() );
		}

		if ( isset( $settings['headers'] ) ) {
			$headers = new Red_Http_Headers( $settings['headers'] );
			$options['headers'] = $headers->get_json();
		}

		if ( isset( $settings['aliases'] ) && is_array( $settings['aliases'] ) ) {
			$options['aliases'] = array_map( 'sanitize_text_field', $settings['aliases'] );
			$options['aliases'] = array_values( array_filter( array_map( 'red_parse_domain_only', $settings['aliases'] ) ) );
			$options['aliases'] = array_slice( $options['aliases'], 0, 20 ); // Max 20
		}

		if ( isset( $settings['permalinks'] ) && is_array( $settings['permalinks'] ) ) {
			$options['permalinks'] = array_map(
				function ( $permalink ) {
					return sanitize_option( 'permalink_structure', $permalink );
				},
				$settings['permalinks']
			);
			$options['permalinks'] = array_values( array_filter( array_map( 'trim', $options['permalinks'] ) ) );
			$options['permalinks'] = array_slice( $options['permalinks'], 0, 10 ); // Max 10
		}

		if ( isset( $settings['preferred_domain'] ) && in_array( $settings['preferred_domain'], [ '', 'www', 'nowww' ], true ) ) {
			$options['preferred_domain'] = sanitize_text_field( $settings['preferred_domain'] );
		}

		if ( isset( $settings['relocate'] ) && is_string( $settings['relocate'] ) ) {
			$options['relocate'] = red_parse_domain_path( sanitize_text_field( $settings['relocate'] ) );

			if ( strlen( $options['relocate'] ) > 0 ) {
				$options['preferred_domain'] = '';
				$options['aliases'] = [];
				$options['https'] = false;
			}
		}

		if ( isset( $settings['cache_key'] ) ) {
			$key = intval( $settings['cache_key'], 10 );

			if ( $settings['cache_key'] === true ) {
				$key = time();
			} elseif ( $settings['cache_key'] === false ) {
				$key = 0;
			}

			$options['cache_key'] = $key;
		}

		if ( isset( $settings['update_notice'] ) ) {
			$major_version = explode( '-', REDIRECTION_VERSION )[0];   // Remove any beta suffix
			$major_version = implode( '.', array_slice( explode( '.', $major_version ), 0, 2 ) );
			$options['update_notice'] = $major_version;
		}

		/** @var RedirectionOptions $options */
		return $options;
	}
}
