<?php

class Red_Htaccess {
	var $items;

	function encode_from( $url )	{
		return '^'.$this->encode( $url ).'$';
	}

	function encode2nd( $url ) {
		$url = urlencode( $url );
		$url = str_replace( '%2F', '/', $url );
		$url = str_replace( '%3A', ':', $url );
		$url = str_replace( '+', '%20', $url );
		$url = str_replace( '%24', '$', $url );
		return $url;
	}

	function encode( $url )	{
		$url = urlencode( $url );
		$url = str_replace( '%2F', '/', $url );
		$url = str_replace( '+', '%20', $url );
		$url = str_replace( '.', '\\.', $url );
		return $url;
	}

	function encode_regex( $url ) {
		$url = str_replace( ' ', '%20', $url );
		$url = str_replace( '.', '\\.', $url );
		$url = str_replace( '\\.*', '.*', $url );
		$url = str_replace( '%24', '$', $url );
		return $url;
	}

	function add_referrer( $item, $match ) {
		$from = $this->encode_from( ltrim( $item->url, '/' ) );
		if ( $item->regex )
			$from = $this->encode_regex( ltrim( $item->url, '/' ) );

		if ( ( $match->url_from || $match->url_notfrom ) && $match->referrer ) {
			$this->items[] = sprintf( 'RewriteCond %%{HTTP_REFERER} %s [NC]', ( $match->regex ? $this->encode_regex( $match->referrer ) : $this->encode_from( $match->referrer ) ) );

			if ( $match->url_from ) {
				$to = $this->target( $item->action_type, $match->url_from, $item->action_code, $item->regex );
				$this->items[] = sprintf( 'RewriteRule %s %s', $from, $to );
			}

			if ( $match->url_notfrom ) {
				$to = $this->target( $item->action_type, $match->url_notfrom, $item->action_code, $item->regex );
				$this->items[] = sprintf( 'RewriteRule %s %s', $from, $to );
			}
		}
	}

	function add_agent( $item, $match ) {
		$from = $this->encode( ltrim( $item->url, '/' ) );
		if ( $item->regex )
			$from = $this->encode_regex( ltrim( $item->url, '/' ) );

		if ( ( $match->url_from || $match->url_notfrom ) && $match->user_agent ) {
			$this->items[] = sprintf( 'RewriteCond %%{HTTP_USER_AGENT} %s [NC]', ( $match->regex ? $this->encode_regex( $match->user_agent ) : $this->encode2nd( $match->user_agent ) ) );

			if ( $match->url_from )	{
				$to = $this->target( $item->action_type, $match->url_from, $item->action_code, $item->regex );
				$this->items[] = sprintf( 'RewriteRule %s %s', $from, $to );
			}

			if ( $match->url_notfrom ) {
				$to = $this->target( $item->action_type, $match->url_notfrom, $item->action_code, $item->regex );
				$this->items[] = sprintf( 'RewriteRule %s %s', $from, $to );
			}
		}
	}

	function add_url( $item, $match ) {
		$to   = $this->target( $item->action_type, $match->url, $item->action_code, $item->regex );
		$from = $this->encode_from( ltrim( $item->url, '/' ) );
		if ( $item->regex )
			$from = $this->encode_regex( ltrim ($item->url, '/' ) );

		if ( $to )
			$this->items[] = sprintf( 'RewriteRule %s %s', $from, $to );
	}

	function action_random( $data, $code, $regex ) {
		// Pick a WP post at random
		global $wpdb;

		$post = $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} ORDER BY RAND() LIMIT 0,1" );
		$url  = parse_url( get_permalink( $post ) );

		return sprintf( '%s [R=%d,L]', $this->encode( $url['path'] ), $code );
	}

	function action_pass( $data, $code, $regex ) {
		if ( $regex )
			return sprintf( '%s [L]', $this->encode2nd( $data ), $code );
		return sprintf( '%s [L]', $this->encode2nd( $data ), $code );
	}

	function action_error( $data, $code, $regex) {
		if ( $code == '410' )
			return '/ [G,L]';
		return '/ [F,L]';
	}

	function action_url( $data, $code, $regex ) {
		if ( $regex )
			return sprintf( '%s [R=%d,L]', $this->encode2nd( $data ), $code );
		return sprintf( '%s [R=%d,L]', $this->encode2nd( $data ), $code );
	}

	function target( $action, $data, $code, $regex ) {
		$target = 'action_'.$action;

		if ( method_exists( $this, $target ) )
			return $this->$target( $data, $code, $regex );
		return '';
	}

	function add( $item ) {
		$target = 'add_'.$item->match_type;

		if ( method_exists( $this, $target ) )
			$this->$target( $item, $item->match );
	}

	function generate() {
		// Head of redirection section - do not localize this
		global $redirection;

		$text[] = '# Created by Redirection';
		$text[] = '# '.date ('r');
		$text[] = '# Redirection '.$redirection->version().' - http://urbangiraffe.com/plugins/redirection/';
		$text[] = '';

		// Default blocked files - I can't think of a reason not to block these
		$text[] = '<Files .htaccess,.svn>';
		$text[] = 'order allow,deny';
		$text[] = 'deny from all';
		$text[] = '</Files>';
		$text[] = '';

		// mod_rewrite section
		$text[] = '';
		$text[] = 'Options +FollowSymlinks';
		$text[] = '';
		$text[] = '<IfModule mod_rewrite.c>';

		// Add redirects
		if ( is_array( $this->items ) )
			$text = array_merge( $text, $this->items );

		// End of mod_rewrite
		$text[] = '</IfModule>';
		$text[] = '';

		// End of redirection section
		$text[] = '# End of Redirection';
		$text[] = '';

		$text = implode( "\r\n", $text );
		$text = str_replace( "\r\n\r\n\r\n", "\r\n", $text );
		$text = str_replace( "\r\n\r\n\r\n", "\r\n", $text );
		return $text;
	}

	function save( $filename, $name ) {
		$text = $this->generate( $name );

		// Does the file already exist?
		if ( file_exists( $filename) ) {
			$existing = @file_get_contents( $filename );

			// Remove any existing Redirection module
			$text .= preg_replace( '@# Created by Redirection Module: '.$name.'(.*?)# End of Redirection@s', '', $existing );
		}

		$file = @fopen( $filename, 'w' );
		if ( $file ) {
			$text = str_replace( "\r\n\r\n\r\n", "\r\n", $text );
			$text = str_replace( "\r\n\r\n\r\n", "\r\n", $text );

			fwrite( $file, $text );
			fclose( $file );
			return true;
		}

		return false;
	}
}
