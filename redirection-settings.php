<?php

define( 'REDIRECTION_OPTION', 'redirection_options' );
define( 'REDIRECTION_API_JSON', 0 );
define( 'REDIRECTION_API_JSON_INDEX', 1 );
define( 'REDIRECTION_API_JSON_RELATIVE', 3 );

function red_get_plugin_data( $plugin ) {
	if ( ! function_exists( 'get_plugin_data' ) ) {
		/** @psalm-suppress MissingFile */
		include_once ABSPATH . '/wp-admin/includes/plugin.php';
	}

	return get_plugin_data( $plugin );
}

function red_get_post_types( $full = true ) {
	$types = get_post_types( array( 'public' => true ), 'objects' );
	$types[] = (object) array(
		'name' => 'trash',
		'label' => __( 'Trash', 'default' ),
	);

	$post_types = [];
	foreach ( $types as $type ) {
		if ( $type->name === 'attachment' ) {
			continue;
		}

		if ( $full && strlen( $type->label ) > 0 ) {
			$post_types[ $type->name ] = $type->label;
		} else {
			$post_types[] = $type->name;
		}
	}

	return apply_filters( 'redirection_post_types', $post_types );
}

/**
 * Get default options. Contains all valid options
 *
 * @return array
 */
function red_get_default_options() {
	$flags = new Red_Source_Flags();
	$defaults = [
		'support'             => false,
		'token'               => md5( uniqid() ),
		'monitor_post'        => 0,   // Dont monitor posts by default
		'monitor_types'       => [],
		'associated_redirect' => '',
		'auto_target'         => '',
		'expire_redirect'     => 7,   // Expire in 7 days
		'expire_404'          => 7,   // Expire in 7 days
		'log_external'        => false,
		'log_header'          => false,
		'track_hits'          => true,
		'modules'             => [],
		'newsletter'          => false,
		'redirect_cache'      => 1,   // 1 hour
		'ip_logging'          => 0,   // No IP logging
		'last_group_id'       => 0,
		'rest_api'            => REDIRECTION_API_JSON,
		'https'               => false,
		'headers'             => [],
		'database'            => '',
		'relocate'            => '',
		'preferred_domain'    => '',
		'aliases'             => [],
		'permalinks'          => [],
		'cache_key'           => 0,
		'plugin_update'       => 'prompt',
		'update_notice'       => 0,
	];
	$defaults = array_merge( $defaults, $flags->get_json() );

	return apply_filters( 'red_default_options', $defaults );
}

/**
 * Set options
 *
 * @param array $settings Partial settings.
 * @return array
 */
function red_set_options( array $settings = [] ) {
	$options = red_get_options();
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

		if ( ! Red_Group::get( $options['monitor_post'] ) && $options['monitor_post'] !== 0 ) {
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

		if ( ! Red_Group::get( $options['last_group_id'] ) ) {
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
	foreach ( [ 'support', 'https', 'newsletter', 'log_external', 'log_header', 'track_hits' ] as $name ) {
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
		if ( $module ) {
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
		$options['permalinks'] = array_map( 'sanitize_text_field', $settings['permalinks'] );
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

	update_option( REDIRECTION_OPTION, apply_filters( 'redirection_save_options', $options ) );
	return $options;
}

function red_parse_url( $url ) {
	$domain = filter_var( $url, FILTER_SANITIZE_URL );
	if ( substr( $domain, 0, 5 ) !== 'http:' && substr( $domain, 0, 6 ) !== 'https:' ) {
		$domain = ( is_ssl() ? 'https://' : 'http://' ) . $domain;
	}

	return wp_parse_url( $domain );
}

function red_parse_domain_only( $domain ) {
	$parsed = red_parse_url( $domain );

	if ( $parsed && isset( $parsed['host'] ) ) {
		return $parsed['host'];
	}

	return '';
}

function red_parse_domain_path( $domain ) {
	$parsed = red_parse_url( $domain );

	if ( $parsed && isset( $parsed['host'] ) ) {
		return $parsed['scheme'] . '://' . $parsed['host'] . ( isset( $parsed['path'] ) ? $parsed['path'] : '' );
	}

	return '';
}

/**
 * Have redirects been disabled?
 *
 * @return boolean
 */
function red_is_disabled() {
	return ( defined( 'REDIRECTION_DISABLE' ) && REDIRECTION_DISABLE ) || file_exists( __DIR__ . '/redirection-disable.txt' );
}

/**
 * Get Redirection options
 *
 * @return array
 */
function red_get_options() {
	$options = get_option( REDIRECTION_OPTION );
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

	$defaults = red_get_default_options();

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

	// Remove old options not in red_get_default_options()
	foreach ( array_keys( $options ) as $key ) {
		if ( ! isset( $defaults[ $key ] ) && $key !== 'database_stage' ) {
			unset( $options[ $key ] );
		}
	}

	// Back-compat fix
	if ( $options['rest_api'] === false || ! in_array( $options['rest_api'], [ REDIRECTION_API_JSON, REDIRECTION_API_JSON_INDEX, REDIRECTION_API_JSON_RELATIVE ], true ) ) {
		$options['rest_api'] = REDIRECTION_API_JSON;
	}

	if ( isset( $options['modules'] ) && isset( $options['modules']['2'] ) && isset( $options['modules']['2']['location'] ) ) {
		$options['location'] = $options['modules']['2']['location'];
	}

	return $options;
}

/**
 * Get the current REST API
 *
 * @param boolean $type Override with a specific API type.
 * @return string
 */
function red_get_rest_api( $type = false ) {
	if ( $type === false ) {
		$options = red_get_options();
		$type = $options['rest_api'];
	}

	$url = get_rest_url();  // REDIRECTION_API_JSON

	if ( $type === REDIRECTION_API_JSON_INDEX ) {
		$url = home_url( '/?rest_route=/' );
	} elseif ( $type === REDIRECTION_API_JSON_RELATIVE ) {
		/** @psalm-suppress TooManyArguments, InvalidCast */
		$relative = (string) wp_parse_url( $url, PHP_URL_PATH );

		if ( $relative ) {
			$url = $relative;
		}

		if ( $url === '/index.php' ) {
			// No permalinks. Default to normal REST API
			$url = home_url( '/?rest_route=/' );
		}
	}

	return $url;
}
