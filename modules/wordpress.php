<?php

class WordPress_Module extends Red_Module {
	var $canonical    = 'default';
	var $strip_index  = 'default';
	var $time_limit   = 0;
	var $matched;

	function start() {
		// Setup the various filters and actions that allow Redirection to h appen
		add_action( 'init',                    array( &$this, 'init' ) );
		add_action( 'send_headers',            array( &$this, 'send_headers' ) );
		add_filter( 'permalink_redirect_skip', array( &$this, 'permalink_redirect_skip' ) );
		add_filter( 'wp_redirect',             array( &$this, 'wp_redirect' ), 1, 2 );

		// Remove WordPress 2.3 redirection
		// XXX still needed?
		remove_action( 'template_redirect', 'wp_old_slug_redirect' );
		remove_action( 'edit_form_advanced', 'wp_remember_old_slug' );
	}

	function init() {
		global $redirection;

		$url = $_SERVER['REQUEST_URI'];

		// Make sure we don't try and redirect something essential
		if ( !$this->protected_url( $url ) && !$redirection->hasMatched() ) {
			do_action( 'redirection_first', $url, $this );

			$redirects = Red_Item::get_for_url( $url, 'wp' );

			foreach ( (array)$redirects AS $key => $item ) {
				if ( $item->matches( $url ) ) {
					$redirection->setMatched( true );
					$this->matched = $item;
					break;
				}
			}

			do_action( 'redirection_last', $url, $this );
		}
	}

	function protected_url( $url )
	{
		global $redirection;
		$part = explode( '?', $url );

		if ( $part[0] == str_replace( get_bloginfo( 'url' ), '', $redirection->url() ).'/ajax.php' || strpos($url, 'wp-cron.php' ) !== false )
			return true;
		return false;
	}

	function status_header( $status )
	{
		// Fix for incorrect headers sent when using FastCGI/IIS
		if ( substr( php_sapi_name(), 0, 3 ) == 'cgi' )
			return str_replace( 'HTTP/1.1', 'Status:', $status );
		return $status;
	}

	function send_headers( $obj )	{
		if ( !empty( $this->matched ) && $this->matched->match->action_code == '410' ) {
			add_filter( 'status_header', array( &$this, 'set_header_410' ) );
		}
	}

	function set_header_410() {
		return 'HTTP/1.1 410 Gone';
	}

	function wp_redirect( $url, $status )
	{
		global $wp_version, $is_IIS;
    if ( $wp_version < '2.1' ) {
    	status_header( $status );
			return $url;
    } elseif ( $is_IIS ) {
			header( "Refresh: 0;url=$url" );
			return $url;
		} else {
        if ( $status == 301 && php_sapi_name() == 'cgi-fcgi' ) {
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
	}

	function save( $data ) {
		return array();
	}

	function permalink_redirect_skip( $skip ) {
		// only want this if we:ve matched using redirection
		if ( $this->matched )
			$skip[] = $_SERVER['REQUEST_URI'];
		return $skip;
	}

	function is_valid()
	{
		$perm = get_option( 'permalink_structure' );
		if ( $perm === false || $perm == '' )
			return false;
		return true;
	}

	function options()
	{
		if ( !$this->is_valid() )
			echo __( '<strong>Disabled: You must enable <a href="options-permalink.php">permalinks</a> before using this</strong>', 'redirection' );
	}
}
