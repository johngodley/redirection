<?php

define( 'REDIRECTION_OPTION', 'redirection_options' );
define( 'REDIRECTION_API_JSON', 0 );
define( 'REDIRECTION_API_JSON_INDEX', 1 );
define( 'REDIRECTION_API_ADMIN', 2 );
define( 'REDIRECTION_API_JSON_RELATIVE', 3 );
define( 'REDIRECTION_API_POST', 4 );

function red_get_plugin_data( $plugin ) {
	if ( ! function_exists( 'get_plugin_data' ) ) {
		include_once ABSPATH . '/wp-admin/includes/plugin.php';
	}

	return get_plugin_data( $plugin );
}

function red_get_post_types( $full = true ) {
	$types = get_post_types( array( 'public' => true ), 'objects' );
	$types[] = (object) array(
		'name' => 'trash',
		'label' => __( 'Trash' ),
	);

	$post_types = array();
	foreach ( $types as $type ) {
		if ( $type->name === 'attachment' ) {
			continue;
		}

		if ( $full ) {
			$post_types[ $type->name ] = $type->label;
		} else {
			$post_types[] = $type->name;
		}
	}

	return apply_filters( 'redirection_post_types', $post_types );
}

function red_set_options( array $settings = array() ) {
	$options = red_get_options();
	$monitor_types = array();

	if ( isset( $settings['version'] ) ) {
		$options['version'] = $settings['version'];
	}

	if ( isset( $settings['rest_api'] ) && in_array( intval( $settings['rest_api'], 10 ), array( 0, 1, 2, 3, 4 ) ) ) {
		$options['rest_api'] = intval( $settings['rest_api'] );
	}

	if ( isset( $settings['monitor_types'] ) && is_array( $settings['monitor_types'] ) ) {
		$allowed = red_get_post_types( false );

		foreach ( $settings['monitor_types'] as $type ) {
			if ( in_array( $type, $allowed ) ) {
				$monitor_types[] = $type;
			}
		}

		$options['monitor_types'] = $monitor_types;
	}

	if ( isset( $settings['associated_redirect'] ) ) {
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
			$options['monitor_post'] = $groups[0]['id'];
		}
	}

	if ( isset( $settings['auto_target'] ) ) {
		$options['auto_target'] = $settings['auto_target'];
	}

	if ( isset( $settings['last_group_id'] ) ) {
		$options['last_group_id'] = max( 0, intval( $settings['last_group_id'], 10 ) );

		if ( ! Red_Group::get( $options['last_group_id'] ) ) {
			$groups = Red_Group::get_all();
			$options['last_group_id'] = $groups[0]['id'];
		}
	}

	if ( isset( $settings['support'] ) ) {
		$options['support'] = $settings['support'] ? true : false;
	}

	if ( isset( $settings['token'] ) ) {
		$options['token'] = $settings['token'];
	}

	if ( isset( $settings['https'] ) ) {
		$options['https'] = $settings['https'] ? true : false;
	}

	if ( ! isset( $settings['token'] ) || trim( $options['token'] ) === '' ) {
		$options['token'] = md5( uniqid() );
	}

	if ( isset( $settings['newsletter'] ) ) {
		$options['newsletter'] = $settings['newsletter'] ? true : false;
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

	if ( isset( $settings['location'] ) ) {
		$module = Red_Module::get( 2 );
		$options['modules'][2] = $module->update( $settings );
	}

	if ( ! empty( $options['monitor_post'] ) && count( $options['monitor_types'] ) === 0 ) {
		// If we have a monitor_post set, but no types, then blank everything
		$options['monitor_post'] = 0;
		$options['associated_redirect'] = '';
	}

	update_option( REDIRECTION_OPTION, apply_filters( 'redirection_save_options', $options ) );
	return $options;
}

function red_get_options() {
	$options = get_option( REDIRECTION_OPTION );
	if ( $options === false ) {
		$options = array();
	}

	$defaults = apply_filters( 'red_default_options', array(
		'support'             => false,
		'token'               => md5( uniqid() ),
		'monitor_post'        => 0,   // Dont monitor posts by default
		'monitor_types'       => array(),
		'associated_redirect' => '',
		'auto_target'         => '',
		'expire_redirect'     => 7,   // Expire in 7 days
		'expire_404'          => 7,   // Expire in 7 days
		'modules'             => array(),
		'newsletter'          => false,
		'redirect_cache'      => 1,   // 1 hour
		'ip_logging'          => 1,   // Full IP logging
		'last_group_id'       => 0,
		'rest_api'            => false,
		'https'               => false,
		'version'             => REDIRECTION_VERSION,
	) );

	foreach ( $defaults as $key => $value ) {
		if ( ! isset( $options[ $key ] ) ) {
			$options[ $key ] = $value;
		}
	}

	// Back-compat. If monitor_post is set without types then it's from an older Redirection
	if ( $options['monitor_post'] > 0 && count( $options['monitor_types'] ) === 0 ) {
		$options['monitor_types'] = array( 'post' );
	}

	return $options;
}

function red_get_rest_api( $type = false ) {
	if ( $type === false ) {
		$options = red_get_options();
		$type = $options['rest_api'];
	}

	$url = get_rest_url();  // REDIRECTION_API_JSON

	if ( $type === REDIRECTION_API_JSON_INDEX ) {
		$url = home_url( '/index.php?rest_route=/' );
	} elseif ( $type === REDIRECTION_API_ADMIN ) {
		$url = admin_url( 'admin-ajax.php?action=red_proxy&rest_path=' );
	} elseif ( $type === REDIRECTION_API_JSON_RELATIVE ) {
		$url = wp_parse_url( $url, PHP_URL_PATH );
	} elseif ( $type === REDIRECTION_API_POST ) {
		$url = admin_url( 'tools.php?page=redirection.php&action=red_proxy&rest_path=' );
	}

	return $url;
}
