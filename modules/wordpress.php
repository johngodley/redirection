<?php

class WordPress_Module extends Red_Module {
	const MODULE_ID = 1;

	private $matched = false;

	public function get_id() {
		return self::MODULE_ID;
	}

	public function can_edit_config() {
		return false;
	}

	public function render_config() {
	}

	public function get_config() {
		return array();
	}

	public function start() {
		// Setup the various filters and actions that allow Redirection to happen
		add_action( 'init',                    array( &$this, 'init' ) );
		add_action( 'send_headers',            array( &$this, 'send_headers' ) );
		add_filter( 'permalink_redirect_skip', array( &$this, 'permalink_redirect_skip' ) );
		add_filter( 'wp_redirect',             array( &$this, 'wp_redirect' ), 1, 2 );
		add_action( 'template_redirect', array( &$this, 'template_redirect' ) );

		// Remove WordPress 2.3 redirection
		remove_action( 'template_redirect', 'wp_old_slug_redirect' );
		remove_action( 'edit_form_advanced', 'wp_remember_old_slug' );
	}

	public function init() {
		$url = $_SERVER['REQUEST_URI'];

		// Make sure we don't try and redirect something essential
		if ( ! $this->protected_url( $url ) && $this->matched === false ) {
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

	private function protected_url( $url ) {
		return false;
	}

	public function template_redirect() {
		if ( is_404() )	{
			$options = red_get_options();

			if ( isset( $options['expire_404'] ) && $options['expire_404'] >= 0 ) {
				RE_404::create( $this->get_url(), $this->get_user_agent(), $this->get_ip(), $this->get_referrer() );
			}
		}
	}

	public function status_header( $status ) {
		// Fix for incorrect headers sent when using FastCGI/IIS
		if ( substr( php_sapi_name(), 0, 3 ) === 'cgi' )
			return str_replace( 'HTTP/1.1', 'Status:', $status );
		return $status;
	}

	public function send_headers( $obj ) {
		if ( ! empty( $this->matched ) && $this->matched->match->action_code === '410' ) {
			add_filter( 'status_header', array( &$this, 'set_header_410' ) );
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
		elseif ( $status === 301 && php_sapi_name() === 'cgi-fcgi' ) {
			$servers_to_check = array( 'lighttpd', 'nginx' );

			foreach ( $servers_to_check as $name ) {
				if ( stripos( $_SERVER['SERVER_SOFTWARE'], $name ) !== false ) {
					status_header( $status );
					header( "Location: $url" );
					exit( 0 );
				}
			}
		}

		status_header( $status );
		return $url;
	}

	public function update( $data ) {
		return false;
	}

	protected function load( $options ) {
	}

	protected function flush_module() {
	}

	public function permalink_redirect_skip( $skip ) {
		// only want this if we've matched using redirection
		if ( $this->matched )
			$skip[] = $_SERVER['REQUEST_URI'];
		return $skip;
	}

	public function get_name() {
		return __( 'WordPress', 'redirection' );
	}

	public function get_description() {
		return __( 'WordPress-powered redirects. This requires no further configuration, and you can track hits.', 'redirection' );
	}

	private function get_url() {
		if ( isset( $_SERVER['REQUEST_URI'] ) )
			return $_SERVER['REQUEST_URI'];
		return '';
	}

	private function get_user_agent() {
		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) )
			return $_SERVER['HTTP_USER_AGENT'];
		return false;
	}

	private function get_referrer() {
		if ( isset( $_SERVER['HTTP_REFERER'] ) )
			return $_SERVER['HTTP_REFERER'];
		return false;
	}

	private function get_ip() {
		if ( isset( $_SERVER['REMOTE_ADDR'] ) )
		  return $_SERVER['REMOTE_ADDR'];
		elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
		  return $_SERVER['HTTP_X_FORWARDED_FOR'];
		return '';
	}
}
