<?php

define( 'REDIRECTION_OPTION', 'redirection_options' );

function red_set_options( array $settings = array() ) {
	$options = red_get_options();
	$monitor_types = array();
	$have_monitor = false;

	if ( isset( $settings['monitor_type_post'] ) ) {
		$monitor_types[] = $settings['monitor_type_post'] ? 'post' : false;
		$have_monitor = true;
	}

	if ( isset( $settings['monitor_type_page'] ) ) {
		$monitor_types[] = $settings['monitor_type_page'] ? 'page' : false;
		$have_monitor = true;
	}

	if ( isset( $settings['monitor_type_trash'] ) ) {
		$monitor_types[] = $settings['monitor_type_trash'] ? 'trash' : false;
		$have_monitor = true;
	}

	if ( isset( $settings['associated_redirect'] ) ) {
		$options['associated_redirect'] = '';

		if ( strlen( $settings['associated_redirect'] ) > 0 ) {
			$sanitizer = new Red_Item_Sanitize();
			$options['associated_redirect'] = trim( $sanitizer->sanitize_url( $settings['associated_redirect'] ) );
		}
	}

	$monitor_types = array_values( array_filter( $monitor_types ) );

	if ( count( $monitor_types ) === 0 ) {
		$options['monitor_post'] = 0;
		$options['associated_redirect'] = '';
	} elseif ( isset( $settings['monitor_post'] ) ) {
		$options['monitor_post'] = max( 0, intval( $settings['monitor_post'], 10 ) );

		if ( ! Red_Group::get( $options['monitor_post'] ) ) {
			$groups = Red_Group::get_all();
			$options['monitor_post'] = $groups[ 0 ]['id'];
		}
	}

	if ( isset( $settings['auto_target'] ) ) {
		$options['auto_target'] = $settings['auto_target'];
	}

	if ( isset( $settings['support'] ) ) {
		$options['support'] = $settings['support'] ? true : false;
	}

	if ( isset( $settings['token'] ) ) {
		$options['token'] = $settings['token'];
	}

	if ( !isset( $settings['token'] ) || trim( $options['token'] ) === '' ) {
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

	if ( $have_monitor ) {
		$options['monitor_types'] = $monitor_types;
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
