<?php

/**
 * @param string $plugin
 * @return array<string, mixed>
 */
function red_get_plugin_data( string $plugin ): array {
	if ( ! function_exists( 'get_plugin_data' ) ) {
		include_once ABSPATH . '/wp-admin/includes/plugin.php';
	}

	return get_plugin_data( $plugin );
}

/**
 * @param bool $full
 * @return array<string, string>|list<string>
 */
function red_get_post_types( bool $full = true ): array {
	$types = get_post_types( [ 'public' => true ], 'objects' );
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
 * Set options
 *
 * @param array<string, mixed> $settings Partial settings.
 * @return array<string, mixed>
 */
function red_set_options( array $settings = [] ) {
	return Red_Options::save( $settings );
}

/**
 * @param string $url
 * @return array<string, mixed>|false
 */
function red_parse_url( string $url ) {
	$domain = filter_var( $url, FILTER_SANITIZE_URL );
	if ( $domain === false ) {
		return false;
	}

	if ( substr( $domain, 0, 5 ) !== 'http:' && substr( $domain, 0, 6 ) !== 'https:' ) {
		$domain = ( is_ssl() ? 'https://' : 'http://' ) . $domain;
	}

	return wp_parse_url( $domain );
}

/**
 * @param string $domain
 * @return string
 */
function red_parse_domain_only( string $domain ): string {
	$parsed = red_parse_url( $domain );

	if ( $parsed !== false && isset( $parsed['host'] ) ) {
		return $parsed['host'];
	}

	return '';
}

/**
 * @param string $domain
 * @return string
 */
function red_parse_domain_path( string $domain ): string {
	$parsed = red_parse_url( $domain );

	if ( $parsed !== false && isset( $parsed['host'] ) ) {
		return $parsed['scheme'] . '://' . $parsed['host'] . ( isset( $parsed['path'] ) ? $parsed['path'] : '' );
	}

	return '';
}

/**
 * Have redirects been disabled?
 *
 * @return boolean
 */
function red_is_disabled(): bool {
	return ( defined( 'REDIRECTION_DISABLE' ) && REDIRECTION_DISABLE ) || file_exists( __DIR__ . '/redirection-disable.txt' );
}

/**
 * Get Redirection options
 *
 * @return array<string, mixed>
 */
function red_get_options() {
	return Red_Options::get();
}

/**
 * Get the current REST API
 *
 * @param int|false $type Override with a specific API type.
 * @return string
 */
function red_get_rest_api( $type = false ): string {
	if ( $type === false ) {
		$options = Red_Options::get();
		$type = $options['rest_api'];
	}

	$url = get_rest_url();  // Red_Options::API_JSON

	if ( $type === Red_Options::API_JSON_INDEX ) {
		$url = home_url( '/?rest_route=/' );
	} elseif ( $type === Red_Options::API_JSON_RELATIVE ) {
		$relative = (string) wp_parse_url( $url, PHP_URL_PATH );

		if ( $relative !== '' ) {
			$url = $relative;
		}

		if ( $url === '/index.php' ) {
			// No permalinks. Default to normal REST API
			$url = home_url( '/?rest_route=/' );
		}
	}

	return $url;
}
