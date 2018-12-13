<?php

include_once dirname( REDIRECTION_FILE ) . '/models/database.php';

class Red_Fixer {
	public function get_status() {
		global $wpdb;

		$options = red_get_options();

		$database = new RE_Database();
		$groups = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_groups" ), 10 );
		$bad_group = $this->get_missing();
		$monitor_group = $options['monitor_post'];
		$valid_monitor = Red_Group::get( $monitor_group ) || $monitor_group === 0;
		$rest_status = $this->get_rest_status();

		$result = array(
			$rest_status,
			$this->get_rest_route_status( $rest_status ),
			array_merge( array(
				'id' => 'db',
				'name' => __( 'Database tables', 'redirection' ),
			), $database->get_status() ),
			array(
				'name' => __( 'Valid groups', 'redirection' ),
				'id' => 'groups',
				'message' => $groups === 0 ? __( 'No valid groups, so you will not be able to create any redirects', 'redirection' ) : __( 'Valid groups detected', 'redirection' ),
				'status' => $groups === 0 ? 'problem' : 'good',
			),
			array(
				'name' => __( 'Valid redirect group', 'redirection' ),
				'id' => 'redirect_groups',
				'message' => count( $bad_group ) > 0 ? __( 'Redirects with invalid groups detected', 'redirection' ) : __( 'All redirects have a valid group', 'redirection' ),
				'status' => count( $bad_group ) > 0 ? 'problem' : 'good',
			),
			array(
				'name' => __( 'Post monitor group', 'redirection' ),
				'id' => 'monitor',
				'message' => $valid_monitor === false ? __( 'Post monitor group is invalid', 'redirection' ) : __( 'Post monitor group is valid', 'redirection' ),
				'status' => $valid_monitor === false ? 'problem' : 'good',
			),
			$this->get_http_settings(),
		);

		return $result;
	}

	private function get_http_settings() {
		$site = wp_parse_url( get_site_url(), PHP_URL_SCHEME );
		$home = wp_parse_url( get_home_url(), PHP_URL_SCHEME );

		$message = __( 'Site and home are consistent', 'redirection' );
		if ( $site !== $home ) {
			/* translators: 1: Site URL, 2: Home URL */
			$message = sprintf( __( 'Site and home URL are inconsistent. Please correct from your Settings > General page: %1$1s is not %2$2s', 'redirection' ), get_site_url(), get_home_url() );
		}

		return array(
			'name' => __( 'Site and home protocol', 'redirection' ),
			'id' => 'redirect_url',
			'message' => $message,
			'status' => $site === $home ? 'good' : 'problem',
		);
	}

	private function get_rest_route_status( $status ) {
		$result = array(
			'name' => __( 'Redirection routes', 'redirection' ),
			'id' => 'routes',
			'status' => 'problem',
		);

		if ( $status['status'] === 'good' ) {
			$response = $this->request_from_api( red_get_rest_api() );

			$result['message'] = __( 'Redirection does not appear in your REST API routes. Have you disabled it with a plugin?', 'redirection' );

			if ( $response && is_array( $response ) && isset( $response['body'] ) ) {
				$json = $this->get_json( $response['body'] );

				if ( isset( $json['success'] ) ) {
					$result['message'] = __( 'Redirection routes are working', 'redirection' );
					$result['status'] = 'good';
				}
			}
		} else {
			$result['message'] = __( 'REST API is not working so routes not checked', 'redirection' );
		}

		return $result;
	}

	public function get_rest_status() {
		$status = array(
			'name' => __( 'WordPress REST API', 'redirection' ),
			'id' => 'rest',
			'status' => 'good',
			/* translators: %s: URL of REST API */
			'message' => sprintf( __( 'WordPress REST API is working at %s', 'redirection' ), red_get_rest_api() ),
		);

		// Special case for OVH servers - this is as close as I can get to detecting mod_security
		$options = red_get_options();
		if ( $options['rest_api'] === 0 && strpos( php_uname( 'a' ), 'ovh' ) !== false ) {
			red_set_options( array( 'rest_api' => 2 ) );
		}

		$result = $this->check_api( red_get_rest_api() );

		if ( is_wp_error( $result ) ) {
			$status['status'] = 'problem';
			$status['message'] = $result->get_error_message();
		}

		return $status;
	}

	public function fix( $status ) {
		foreach ( $status as $item ) {
			if ( $item['status'] !== 'good' ) {
				$fixer = 'fix_' . $item['id'];

				if ( method_exists( $this, $fixer ) ) {
					$result = $this->$fixer();
				}

				if ( is_wp_error( $result ) ) {
					return $result;
				}
			}
		}

		return $this->get_status();
	}

	private function get_missing() {
		global $wpdb;

		return $wpdb->get_results( "SELECT {$wpdb->prefix}redirection_items.id FROM {$wpdb->prefix}redirection_items LEFT JOIN {$wpdb->prefix}redirection_groups ON {$wpdb->prefix}redirection_items.group_id = {$wpdb->prefix}redirection_groups.id WHERE {$wpdb->prefix}redirection_groups.id IS NULL" );
	}

	public function fix_rest() {
		// First check the default REST API
		$result = $this->check_api( get_rest_url() );

		if ( is_wp_error( $result ) ) {
			$options = red_get_options();
			if ( $options['https'] ) {
				// Disable this just be to safe
				red_set_options( array( 'https' => false ) );
			}

			// Try directly at index.php?rest_route
			$rest_api = red_get_rest_api( REDIRECTION_API_JSON_INDEX );
			$result = $this->check_api( $rest_api );

			if ( is_wp_error( $result ) ) {
				$rest_api = red_get_rest_api( REDIRECTION_API_ADMIN );
				$response = $this->request_from_api( $rest_api );

				if ( is_array( $response ) && isset( $response['body'] ) && $response['body'] === '0' ) {
					red_set_options( array( 'rest_api' => 2 ) );
					return true;
				}

				red_set_options( array( 'rest_api' => 0 ) );
				return false;
			}

			// It worked! Save the URL
			red_set_options( array( 'rest_api' => 1 ) );
			return true;
		}

		// Working
		red_set_options( array( 'rest_api' => 0 ) );
		return true;
	}

	private function normalize_url( $url ) {
		if ( substr( $url, 0, 4 ) !== 'http' ) {
			$parts = wp_parse_url( get_site_url() );
			$url = ( isset( $parts['scheme'] ) ? $parts['scheme'] : 'http' ) . '://' . $parts['host'] . $url;
		}

		return $url;
	}

	private function request_from_api( $url ) {
		$url = $this->normalize_url( $url . 'redirection/v1/plugin/test' );
		$url = add_query_arg( '_wpnonce', wp_create_nonce( 'wp_rest' ), $url );
		$options = array(
			'cookies' => $_COOKIE,
			'redirection' => 0,
			'body' => '{}',
		);

		// For REST API calls set the content-type - some servers get tripped up on this
		if ( strpos( $url, '/wp-json/' ) !== false || strpos( $url, 'rest_route' ) !== false ) {
			$options['headers'] = array(
				'content-type: application/json; charset=utf-8',
			);
		}

		// Match our user agent
		if ( Redirection_Request::get_user_agent() ) {
			$options['user-agent'] = Redirection_Request::get_user_agent();
		}

		return wp_remote_post( $url, $options );
	}

	private function check_api( $url ) {
		$response = $this->request_from_api( $url );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$http_code = wp_remote_retrieve_response_code( $response );

		$specific = 'REST API returns an error code';
		if ( $http_code === 200 ) {
			$json = $this->get_json( $response['body'] );

			if ( $json || $response['body'] === '0' ) {
				return true;
			} else {
				$specific = 'REST API returned invalid JSON data. This is probably an error page of some kind and indicates it has been disabled';
				$specific .= ' - ' . json_last_error_msg();
			}
		} elseif ( $http_code === 301 || $http_code === 302 ) {
			$specific = 'REST API is being redirected. This indicates it has been disabled or you have a trailing slash redirect.';
		} elseif ( $http_code === 404 ) {
			$specific = 'REST API is returning a 404 error. This indicates it has been disabled.';
		} elseif ( $http_code ) {
			$specific = 'REST API returned a ' . $http_code . ' code.';
		}

		return new WP_Error( 'redirection', $specific . ' (' . ( $http_code ? $http_code : 'unknown' ) . ' - ' . $url . ')' );
	}

	private function get_json( $body ) {
		if ( strpos( bin2hex( $body ), 'efbbbf' ) !== false ) {
			$body = substr( $body, 3 );
		}

		return @json_decode( $body, true );
	}

	private function fix_db() {
		$database = new RE_Database();

		try {
			$database->create_tables();
		} catch ( Exception $e ) {
			return new WP_Error( __( 'Failed to fix database tables', 'redirection' ) );
		}

		return true;
	}

	private function fix_groups() {
		if ( Red_Group::create( 'new group', 1 ) === false ) {
			return new WP_Error( __( 'Unable to create group', 'redirection' ) );
		}

		return true;
	}

	private function fix_redirect_groups() {
		global $wpdb;

		$missing = $this->get_missing();

		foreach ( $missing as $row ) {
			$wpdb->update( $wpdb->prefix . 'redirection_items', array( 'group_id' => $this->get_valid_group() ), array( 'id' => $row->id ) );
		}
	}

	private function fix_monitor() {
		red_set_options( array( 'monitor_post' => $this->get_valid_group() ) );
	}

	private function get_valid_group() {
		$groups = Red_Group::get_all();

		return $groups[0]['id'];
	}
}
