<?php

class Red_Htaccess {
	var $settings;
	var $items;

	function __construct( $settings ) {
		foreach ( $settings AS $key => $value ) {
			$this->settings[$key] = $value;
		}
	}

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

	function generate( $name ) {
		// Head of redirection section - do not localize this
		global $redirection;

		$text[] = '# Created by Redirection Module: '.$name;
		$text[] = '# '.date ('r');
		$text[] = '# Redirection '.$redirection->version().' - http://urbangiraffe.com/plugins/redirection/';
		$text[] = '';

		// Default blocked files - I can't think of a reason not to block these
		$text[] = '<Files .htaccess,.svn>';
		$text[] = 'order allow,deny';
		$text[] = 'deny from all';
		$text[] = '</Files>';
		$text[] = '';

		// PHP options
		if ( isset( $this->settings['error_level'] ) && $this->settings['error_level'] != 'default' )
			$text[] = 'php_value error_reporting '.( $this->settings == 'none' ? '0' : 'E_ALL' );

		if ( isset( $this->settings['memory_limit'] ) && $this->settings['memory_limit'] != 0 )
			$text[] = 'php_value memory_limit '.$this->settings['memory_limit'].'M';

		if ( ( isset( $this->settings['allow_ip'] ) && $this->settings['allow_ip'] ) || ( isset( $this->settings['ban_ip'] ) && $this->settings['ban_ip'] ) ) {
			$text[] = '';
			$text[] = 'order allow,deny';

			if ( isset( $this->settings['ban_ip'] ) && $this->settings['ban_ip'] ) {
				$ips = array_filter( explode( ',', $this->settings['ban_ip'] ) );

				if ( count( $ips ) > 0 ) {
					foreach ( $ips AS $ip ) {
						$text[] = 'deny from '.$ip;
					}
				}
			}

			if ( $this->settings['allow_ip'] ) {
				$ips = array_filter( explode( ',', $this->settings['allow_ip'] ) );

				if ( count( $ips ) > 0 ) {
					foreach ( $ips AS $ip ) {
						$text[] = 'allow from '.$ip;
					}
				}
			}
			else
				$text[] = 'allow from all';
		}

		// mod_rewrite section
		$text[] = '';
		$text[] = 'Options +FollowSymlinks';
		$text[] = '';
		$text[] = '<IfModule mod_rewrite.c>';

		if ( $this->settings['canonical'] != 'default' ) {
			$text[] = 'RewriteEngine On';
			$base   = $this->settings['site'];

			if ( $base == '' )
				$base = get_option( 'home' );

			$parts = parse_url( $base );
			$base  = str_replace( 'www.', '', $parts['host'] );

			if ( $this->settings['canonical'] == 'nowww' ) {
				$text[] = 'RewriteCond %{HTTP_HOST} ^www\.'.str_replace( '.', '\\.', $base ).'$ [NC]';
				$text[] = 'RewriteRule ^(.*)$ http://'.$base.'/$1 [R=301,L]';
			}
			elseif ( $this->settings['canonical'] == 'www' ) {
				$text[] = 'RewriteCond %{HTTP_HOST} ^'.str_replace( '.', '\\.', $base ).'$ [NC]';
				$text[] = 'RewriteRule ^(.*)$ http://www.'.$base.'/$1 [R=301,L]';
			}

			$text[] = '';
		}

		if ( $this->settings['strip_index'] == 'yes' ) {
			$text[] = 'RewriteCond %{THE_REQUEST} (.*)index\.(php|htm|html)\ HTTP/';
			$text[] = 'RewriteRule ^(.*)index\.(php|html|htm)$ $1 [R=301,NC,L]';
			$text[] = '';
		}

		// Add redirects
		if ( is_array( $this->items ) )
			$text = array_merge( $text, $this->items );

		// End of mod_rewrite
		$text[] = '</IfModule>';
		$text[] = '';

		if ( isset( $this->settings['raw'] ) && $this->settings['raw'] )
			$text[] = $this->settings['raw'];

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
