<?php

/**
 * @phpstan-import-type RedirectCreateDetails from Red_Item
 */

class Red_Item_Sanitize {
	/**
	 * Trim strings recursively in an array
	 *
	 * @param array<string, mixed> $array_to_clean
	 * @return array<string, mixed>
	 */
	private function clean_array( $array_to_clean ) {
		foreach ( $array_to_clean as $name => $value ) {
			if ( is_array( $value ) ) {
				$array_to_clean[ $name ] = $this->clean_array( $value );
			} elseif ( is_string( $value ) ) {
				$value = trim( $value );
				$array_to_clean[ $name ] = $value;
			} else {
				$array_to_clean[ $name ] = $value;
			}
		};

		return $array_to_clean;
	}

	/**
	 * Convert absolute URL into server match where appropriate
	 *
	 * @param string $url
	 * @param array<string, mixed> $details
	 * @return array<string, mixed>
	 */
	private function set_server( $url, array $details ) {
		$return = [];
		$domain = wp_parse_url( $url, PHP_URL_HOST );

		// Auto-convert an absolute URL to relative + server match
		if ( is_string( $domain ) && $domain !== Redirection_Request::get_server_name() ) {
			$return['match_type'] = 'server';

			if ( isset( $details['action_data']['url'] ) ) {
				$return['action_data'] = [
					'server' => $domain,
					'url_from' => $details['action_data']['url'],
				];
			} else {
				$return['action_data'] = [ 'server' => $domain ];
			}

			$url = wp_parse_url( $url, PHP_URL_PATH );
			if ( $url === null || $url === false ) {
				$url = '/';
			}
		}

		$return['url'] = $url;
		return $return;
	}

	/**
	 * Sanitize and normalize redirect details
	 *
	 * @param array<string, mixed>|RedirectCreateDetails $details
	 * @return array<string, mixed>|\WP_Error
	 */
	public function get( array $details ) {
		$data = [];
		$details = $this->clean_array( $details );

		// Set regex
		$data['regex'] = isset( $details['regex'] ) && intval( $details['regex'], 10 ) === 1 ? 1 : 0;

		// Auto-migrate the regex to the source flags
		$data['match_data'] = [ 'source' => [ 'flag_regex' => $data['regex'] === 1 ? true : false ] ];

		$flags = new Red_Source_Flags();

		// Set flags
		if ( isset( $details['match_data'] ) && isset( $details['match_data']['source'] ) ) {
			$defaults = Red_Options::get();

			// Parse the source flags
			$flags = new Red_Source_Flags( $details['match_data']['source'] );

			// Remove defaults
			$data['match_data']['source'] = $flags->get_json_without_defaults( $defaults );
			$data['regex'] = $flags->is_regex() ? 1 : 0;
		}

		// If match_data is empty then don't save anything
		// @phpstan-ignore isset.offset
		if ( isset( $data['match_data']['source'] ) && count( $data['match_data']['source'] ) === 0 ) {
			$data['match_data']['source'] = [];
		}

		if ( isset( $details['match_data']['options'] ) && is_array( $details['match_data']['options'] ) ) {
			$source = new Red_Source_Options( $details['match_data']['options'] );
			$data['match_data']['options'] = $source->get_json();
		}

		$data['match_data'] = array_filter( $data['match_data'] );

		if ( empty( $data['match_data'] ) ) {
			$data['match_data'] = null;
		}

		// Parse URL
		$url = empty( $details['url'] ) ? $this->auto_generate() : $details['url'];
		if ( strpos( $url, 'http:' ) !== false || strpos( $url, 'https:' ) !== false ) {
			$details = array_merge( $details, $this->set_server( $url, $details ) );
		}

		$data['match_type'] = isset( $details['match_type'] ) ? sanitize_text_field( $details['match_type'] ) : 'url';
		$data['url'] = $this->get_url( $url, $data['regex'] === 1 ? true : false );

		if ( isset( $details['hits'] ) ) {
			$data['last_count'] = intval( $details['hits'], 10 );
		}

		if ( isset( $details['last_access'] ) ) {
			$last_access = strtotime( sanitize_text_field( $details['last_access'] ) );
			if ( $last_access !== false ) {
				$data['last_access'] = gmdate( 'Y-m-d H:i:s', $last_access );
			}
		}

		if ( ! is_wp_error( $data['url'] ) ) {
			$matcher = new Red_Url_Match( $data['url'] );
			$data['match_url'] = $matcher->get_url();

			// If 'exact order' then save the match URL with query params
			if ( $flags->is_query_exact_order() ) {
				$data['match_url'] = $matcher->get_url_with_params();
			}
		}

		$data['title'] = ! empty( $details['title'] ) ? $details['title'] : null;
		$data['group_id'] = $this->get_group( isset( $details['group_id'] ) ? $details['group_id'] : 0 );
		$data['position'] = $this->get_position( $details );

		// Set match_url to 'regex'
		if ( $data['regex'] === 1 ) {
			$data['match_url'] = 'regex';
		}

		if ( is_string( $data['title'] ) ) {
			$data['title'] = trim( substr( sanitize_text_field( $data['title'] ), 0, 500 ) );
			$data['title'] = wp_kses( $data['title'], 'strip' );

			if ( strlen( $data['title'] ) === 0 ) {
				$data['title'] = null;
			}
		}

		$matcher = Red_Match::create( isset( $details['match_type'] ) ? sanitize_text_field( $details['match_type'] ) : '' );
		if ( $matcher === null ) {
			return new WP_Error( 'redirect', 'Invalid redirect matcher' );
		}

		$action_code = isset( $details['action_code'] ) ? intval( $details['action_code'], 10 ) : 0;
		$action = Red_Action::create( isset( $details['action_type'] ) ? sanitize_text_field( $details['action_type'] ) : '', $action_code );
		if ( $action === null ) {
			return new WP_Error( 'redirect', 'Invalid redirect action' );
		}

		$data['action_type'] = sanitize_text_field( $details['action_type'] );
		$data['action_code'] = $this->get_code( $details['action_type'], $action_code );

		if ( isset( $details['action_data'] ) && is_array( $details['action_data'] ) ) {
			$match_data = $matcher->save( $details['action_data'] ? $details['action_data'] : array(), ! $this->is_url_type( $data['action_type'] ) );
			$data['action_data'] = is_array( $match_data ) ? serialize( $match_data ) : $match_data;
		}

		// Any errors?
		foreach ( $data as $value ) {
			if ( is_wp_error( $value ) ) {
				return $value;
			}
		}

		return apply_filters( 'redirection_validate_redirect', $data );
	}

	/**
	 * @param array<string, mixed> $details
	 * @return int
	 */
	protected function get_position( $details ) {
		if ( isset( $details['position'] ) ) {
			return max( 0, intval( $details['position'], 10 ) );
		}

		return 0;
	}

	/**
	 * @param string $type
	 * @return bool
	 */
	protected function is_url_type( $type ) {
		if ( $type === 'url' || $type === 'pass' ) {
			return true;
		}

		return false;
	}

	/**
	 * @param int $code
	 * @return bool
	 */
	public function is_valid_redirect_code( $code ) {
		return in_array( $code, array( 301, 302, 303, 304, 307, 308 ), true );
	}

	/**
	 * @param int $code
	 * @return bool
	 */
	public function is_valid_error_code( $code ) {
		return in_array( $code, array( 400, 401, 403, 404, 410, 418, 451, 500, 501, 502, 503, 504 ), true );
	}

	/**
	 * @param string $action_type
	 * @param int $code
	 * @return int
	 */
	protected function get_code( $action_type, $code ) {
		if ( $action_type === 'url' || $action_type === 'random' ) {
			if ( $this->is_valid_redirect_code( $code ) ) {
				return $code;
			}

			return 301;
		}

		if ( $action_type === 'error' ) {
			if ( $this->is_valid_error_code( $code ) ) {
				return $code;
			}

			return 404;
		}

		return 0;
	}

	/**
	 * @param int|string $group_id
	 * @return int|\WP_Error
	 */
	protected function get_group( $group_id ) {
		$group_id = intval( $group_id, 10 );

		if ( Red_Group::get( $group_id ) === false ) {
			return new WP_Error( 'redirect', 'Invalid group when creating redirect' );
		}

		return $group_id;
	}

	/**
	 * @param string $url
	 * @param bool $regex
	 * @return string|\WP_Error
	 */
	protected function get_url( $url, $regex ) {
		$url = self::sanitize_url( $url, $regex );

		if ( $url === '' ) {
			return new WP_Error( 'redirect', 'Invalid source URL' );
		}

		return $url;
	}

	/**
	 * @return string
	 */
	protected function auto_generate() {
		$options = Red_Options::get();
		$url = '';

		if ( strlen( $options['auto_target'] ) > 0 ) {
			$id = time();
			$url = str_replace( '$dec$', (string) $id, $options['auto_target'] );
			$url = str_replace( '$hex$', sprintf( '%x', $id ), $url );
		}

		return $url;
	}

	/**
	 * @param string $url
	 * @param bool $regex
	 * @return string
	 */
	public function sanitize_url( $url, $regex = false ) {
		$url = wp_kses( $url, 'strip' );
		$url = str_replace( '&amp;', '&', $url );

		// Make sure that the old URL is relative
		$url = (string) preg_replace( '@^https?://(.*?)/@', '/', $url );
		$url = (string) preg_replace( '@^https?://(.*?)$@', '/', $url );

		// No new lines
		$url = (string) preg_replace( "/[\r\n\t].*?$/s", '', $url );

		// Clean control codes
		$url = (string) preg_replace( '/[^\PC\s]/u', '', $url );

		// Ensure a slash at start
		if ( substr( $url, 0, 1 ) !== '/' && $regex === false ) {
			$url = '/' . $url;
		}

		// Try and URL decode any i10n characters
		$decoded = $this->remove_bad_encoding( rawurldecode( $url ) );

		// Was there any invalid characters?
		if ( $decoded === false ) {
			// Yes. Use the url as an undecoded URL, and check for invalid characters
			$decoded = $this->remove_bad_encoding( $url );

			// Was there any invalid characters?
			if ( $decoded === false ) {
				// Yes, it's still a problem. Use the URL as-is and hope for the best
				return $url;
			}
		}

		if ( $regex ) {
			$decoded = str_replace( '?&lt;!', '?<!', $decoded );
		}

		// Return the URL
		return $decoded;
	}

	/**
	 * Remove any bad encoding, where possible
	 *
	 * @param string $text Text.
	 * @return string|false
	 */
	/**
	 * @param string $text
	 * @return string|false
	 */
	private function remove_bad_encoding( $text ) {
		// Try and remove bad decoding
		if ( function_exists( 'iconv' ) ) {
			return @iconv( 'UTF-8', 'UTF-8//IGNORE', sanitize_textarea_field( $text ) );
		}

		return sanitize_textarea_field( $text );
	}
}
