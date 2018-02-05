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
		// Setup the various filters and actions that allow Redirection to happen
		add_action( 'init',                    array( $this, 'init' ) );
		add_action( 'send_headers',            array( $this, 'send_headers' ) );
		add_filter( 'permalink_redirect_skip', array( $this, 'permalink_redirect_skip' ) );
		add_filter( 'wp_redirect',             array( $this, 'wp_redirect' ), 1, 2 );
		add_filter( 'status_header',           array( $this, 'status_header_404' ), 10, 4 );
		add_action( 'redirection_visit',       array( $this, 'redirection_visit' ), 10, 3 );
		add_action( 'redirection_do_nothing',  array( $this, 'redirection_do_nothing' ) );
		add_filter( 'redirect_canonical',      array( $this, 'redirect_canonical' ), 10, 2 );

		// Remove WordPress 2.3 redirection
		remove_action( 'template_redirect', 'wp_old_slug_redirect' );
	}

	/**
	 * This ensures that a matched URL is not overriddden by WordPress, if the URL happens to be a WordPress URL of some kind
	 * For example: /?author=1 will be redirected to /author/name unless this returns false
	 */
	public function redirect_canonical( $redirect_url, $requested_url ) {
		if ( $this->matched ) {
			return false;
		}

		return $redirect_url;
	}

	public function status_header_404( $status_header, $code, $description, $protocol ) {
		if ( $code === 404 ) {
			$options = red_get_options();

			if ( isset( $options['expire_404'] ) && $options['expire_404'] >= 0 && apply_filters( 'redirection_log_404', $this->can_log ) ) {
				RE_404::create( Redirection_Request::get_request_url(), Redirection_Request::get_user_agent(), Redirection_Request::get_ip(), Redirection_Request::get_referrer() );
			}
		}

		return $status_header;
	}

	public function redirection_do_nothing() {
		$this->can_log = false;
		return false;
	}

	public function redirection_visit( $redirect, $url, $target ) {
		$redirect->visit( $url, $target );
	}

	public function init() {
		$url = apply_filters( 'redirection_url_source', Redirection_Request::get_request_url() );

		// Make sure we don't try and redirect something essential
		if ( $url && ! $this->protected_url( $url ) && $this->matched === false ) {
			do_action( 'redirection_first', $url, $this );

			$redirects = Red_Item::get_for_url( $url, 'wp' );

			foreach ( (array) $redirects as $item ) {
				if ( $item->matches( $url ) ) {
					$this->matched = $item;
					break;
				}
			}

			do_action( 'redirection_last', $url, $this );
		}
	}

	/**
	 * Protect certain URLs from being redirected. Note we don't need to protect wp-admin, as this code doesn't run there
	 */
	private function protected_url( $url ) {
		$rest = parse_url( red_get_rest_api() );
		$rest_api = $rest['path'].( isset( $rest['query'] ) ? '?'.$rest['query'] : '' );

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
		if ( ! empty( $this->matched ) && $this->matched->match->action_code === '410' ) {
			add_filter( 'status_header', array( $this, 'set_header_410' ) );
		}
	}

	public function set_header_410() {
		return 'HTTP/1.1 410 Gone';
	}

	public function wp_redirect( $url, $status ) {
		global $wp_version, $is_IIS;

		if ( $is_IIS ) {
			header( "Refresh: 0;url=$url" );
			return $url;
		}

		if ( $status === 301 && php_sapi_name() === 'cgi-fcgi' ) {
			$servers_to_check = array( 'lighttpd', 'nginx' );

			foreach ( $servers_to_check as $name ) {
				if ( stripos( $_SERVER['SERVER_SOFTWARE'], $name ) !== false ) {
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

	public function permalink_redirect_skip( $skip ) {
		// only want this if we've matched using redirection
		if ( $this->matched ) {
			$skip[] = $_SERVER['REQUEST_URI'];
		}

		return $skip;
	}

	public function reset() {
		$this->can_log = true;
	}
}
