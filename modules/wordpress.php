<?php

class WordPress_Module extends Red_Module {
	const MODULE_ID = 1;

	private $matched = false;
	private $can_log = true;

	public function get_id() {
		return self::MODULE_ID;
	}

	public function get_name() {
		return 'WordPress';
	}

	public function start() {
		// Only run redirect rules if we're not disabled
		if ( ! red_is_disabled() ) {
			// The main redirect loop
			add_action( 'init', [ $this, 'init' ] );

			// Force HTTPs code
			add_action( 'init', [ $this, 'force_https' ] );

			// Send site HTTP headers as well as 410 error codes
			add_action( 'send_headers', [ $this, 'send_headers' ] );

			// Redirect HTTP headers and server-specific overrides
			add_filter( 'wp_redirect', [ $this, 'wp_redirect' ], 1, 2 );
		}

		// Setup the various filters and actions that allow Redirection to happen
		add_action( 'redirection_visit', [ $this, 'redirection_visit' ], 10, 3 );
		add_action( 'redirection_do_nothing', [ $this, 'redirection_do_nothing' ] );

		// Prevent WordPress overriding a canonical redirect
		add_filter( 'redirect_canonical', [ $this, 'redirect_canonical' ], 10, 2 );

		// Log 404s and perform 'URL and WordPress page type' redirects
		add_action( 'template_redirect', [ $this, 'template_redirect' ] );
	}

	/*
	 * This ensures that a matched URL is not overriddden by WordPress, if the URL happens to be a WordPress URL of some kind
	 * For example: /?author=1 will be redirected to /author/name unless this returns false
	 */
	public function redirect_canonical( $redirect_url, $requested_url ) {
		if ( $this->matched ) {
			return false;
		}

		return $redirect_url;
	}

	public function template_redirect() {
		if ( ! is_404() || $this->matched ) {
			return;
		}

		if ( $this->match_404_type() ) {
			// Don't log an intentionally redirected 404
			return;
		}

		$options = red_get_options();

		if ( isset( $options['expire_404'] ) && $options['expire_404'] >= 0 && apply_filters( 'redirection_log_404', $this->can_log ) ) {
			RE_404::create( Redirection_Request::get_request_url(), Redirection_Request::get_user_agent(), Redirection_Request::get_ip(), Redirection_Request::get_referrer() );
		}
	}

	private function match_404_type() {
		if ( ! property_exists( $this, 'redirects' ) || count( $this->redirects ) === 0 ) {
			return false;
		}

		$page_types = array_values( array_filter( $this->redirects, [ $this, 'only_404' ] ) );

		if ( count( $page_types ) > 0 ) {
			$url = apply_filters( 'redirection_url_source', Redirection_Request::get_request_url() );

			foreach ( $page_types as $page_type ) {
				if ( $page_type->is_match( $url ) ) {
					return true;
				}
			}
		}

		return false;
	}

	private function only_404( $redirect ) {
		return $redirect->match->get_type() === 'page';
	}

	// Return true to stop further processing of the 'do nothing'
	public function redirection_do_nothing() {
		$this->can_log = false;
		return true;
	}

	public function redirection_visit( $redirect, $url, $target ) {
		$redirect->visit( $url, $target );
	}

	public function force_https() {
		$options = red_get_options();

		if ( $options['https'] && ! is_ssl() ) {
			$target = rtrim( parse_url( home_url(), PHP_URL_HOST ), '/' ) . esc_url_raw( Redirection_Request::get_request_url() );
			add_filter( 'x_redirect_by', [ $this, 'x_redirect_by' ] );
			wp_safe_redirect( 'https://' . $target, 301 );
			die();
		}
	}

	public function x_redirect_by() {
		return 'redirection';
	}

	/**
	 * This is the key to Redirection and where requests are matched to redirects
	 */
	public function init() {
		$url = Redirection_Request::get_request_url();
		$url = apply_filters( 'redirection_url_source', $url );
		$url = rawurldecode( $url );

		// Make sure we don't try and redirect something essential
		if ( $url && ! $this->protected_url( $url ) && $this->matched === false ) {
			do_action( 'redirection_first', $url, $this );

			// Get all redirects that match the URL
			$redirects = Red_Item::get_for_url( $url );

			// Redirects will be ordered by position. Run through the list until one fires
			foreach ( (array) $redirects as $item ) {
				if ( $item->is_match( $url ) ) {
					$this->matched = $item;
					break;
				}
			}

			do_action( 'redirection_last', $url, $this );

			if ( ! $this->matched ) {
				// Keep them for later
				$this->redirects = $redirects;
			}
		}
	}

	/*
	 * Protect certain URLs from being redirected. Note we don't need to protect wp-admin, as this code doesn't run there
	 */
	private function protected_url( $url ) {
		$rest = wp_parse_url( red_get_rest_api() );
		$rest_api = $rest['path'] . ( isset( $rest['query'] ) ? '?' . $rest['query'] : '' );

		if ( substr( $url, 0, strlen( $rest_api ) ) === $rest_api ) {
			// Never redirect the REST API
			return true;
		}

		return false;
	}

	public function status_header( $status ) {
		// Fix for incorrect headers sent when using FastCGI/IIS
		if ( substr( php_sapi_name(), 0, 3 ) === 'cgi' ) {
			return str_replace( 'HTTP/1.1', 'Status:', $status );
		}

		return $status;
	}

	public function send_headers( $obj ) {
		if ( ! empty( $this->matched ) && $this->matched->action->get_code() === 410 ) {
			add_filter( 'status_header', [ $this, 'set_header_410' ] );
		}

		// Add any custom headers
		$options = red_get_options();
		$headers = new Red_Http_Headers( $options['headers'] );
		$headers->run( $headers->get_site_headers() );
	}

	public function set_header_410() {
		return 'HTTP/1.1 410 Gone';
	}

	public function wp_redirect( $url, $status = 302 ) {
		global $wp_version, $is_IIS;

		$options = red_get_options();
		$headers = new Red_Http_Headers( $options['headers'] );
		$headers->run( $headers->get_redirect_headers() );

		if ( $is_IIS ) {
			header( "Refresh: 0;url=$url" );
			return $url;
		}

		if ( $status === 301 && php_sapi_name() === 'cgi-fcgi' ) {
			$servers_to_check = [ 'lighttpd', 'nginx' ];

			foreach ( $servers_to_check as $name ) {
				if ( isset( $_SERVER['SERVER_SOFTWARE'] ) && stripos( $_SERVER['SERVER_SOFTWARE'], $name ) !== false ) {
					status_header( $status );
					header( "Location: $url" );
					exit( 0 );
				}
			}
		}

		if ( intval( $status, 10 ) === 307 ) {
			status_header( $status );
			nocache_headers();
			return $url;
		}

		$options = red_get_options();

		// Do we need to set the cache header?
		if ( ! headers_sent() && isset( $options['redirect_cache'] ) && $options['redirect_cache'] !== 0 && intval( $status, 10 ) === 301 ) {
			if ( $options['redirect_cache'] === -1 ) {
				// No cache - just use WP function
				nocache_headers();
			} else {
				// Custom cache
				header( 'Expires: ' . gmdate( 'D, d M Y H:i:s T', time() + $options['redirect_cache'] * 60 * 60 ) );
				header( 'Cache-Control: max-age=' . $options['redirect_cache'] * 60 * 60 );
			}
		}

		status_header( $status );
		return $url;
	}

	public function update( array $data ) {
		return false;
	}

	protected function load( $options ) {
	}

	protected function flush_module() {
	}

	public function reset() {
		$this->can_log = true;
	}
}
