<?php

class Red_Htaccess {
	private $items = array();
	const INSERT_REGEX = '@\n?# Created by Redirection(?:.*?)# End of Redirection\n?@sm';

	private function encode_from( $url ) {
		$url = $this->encode( $url );

		// Apache 2 does not need a leading slashing
		$url = ltrim( $url, '/' );

		// Exactly match the URL
		return '^' . $url . '$';
	}

	private function encode2nd( $url ) {
		$url = urlencode( $url );
		$url = str_replace( '%2F', '/', $url );
		$url = str_replace( '%3F', '?', $url );
		$url = str_replace( '%3A', ':', $url );
		$url = str_replace( '%3D', '=', $url );
		$url = str_replace( '%26', '&', $url );
		$url = str_replace( '%25', '%', $url );
		$url = str_replace( '+', '%20', $url );
		$url = str_replace( '%24', '$', $url );
		return $url;
	}

	private function encode( $url ) {
		$url = urlencode( $url );
		$url = str_replace( '%2F', '/', $url );
		$url = str_replace( '%3F', '?', $url );
		$url = str_replace( '+', '%20', $url );
		$url = str_replace( '.', '\\.', $url );
		return $url;
	}

	private function encode_regex( $url ) {
		// Remove any newlines
		$url = preg_replace( "/[\r\n\t].*?$/s", '', $url );

		// Remove invalid characters
		$url = preg_replace( '/[^\PC\s]/u', '', $url );

		// Make sure spaces are quoted
		$url = str_replace( ' ', '%20', $url );
		$url = str_replace( '%24', '$', $url );

		// No leading slash
		$url = ltrim( $url, '/' );

		// If pattern has a ^ at the start then ensure we don't have a slash immediatley after
		$url = preg_replace( '@^\^/@', '^', $url );

		return $url;
	}

	private function add_referrer( $item, $match ) {
		$from = $this->encode_from( ltrim( $item->get_url(), '/' ) );
		if ( $item->is_regex() ) {
			$from = $this->encode_regex( ltrim( $item->get_url(), '/' ) );
		}

		if ( ( $match->url_from || $match->url_notfrom ) && $match->referrer ) {
			$this->items[] = sprintf( 'RewriteCond %%{HTTP_REFERER} %s [NC]', ( $match->regex ? $this->encode_regex( $match->referrer ) : $this->encode_from( $match->referrer ) ) );

			if ( $match->url_from ) {
				$to = $this->target( $item->get_action_type(), $match->url_from, $item->get_action_code(), $item->is_regex() );
				$this->items[] = sprintf( 'RewriteRule %s %s', $from, $to );
			}

			if ( $match->url_notfrom ) {
				$to = $this->target( $item->get_action_type(), $match->url_notfrom, $item->get_action_code(), $item->is_regex() );
				$this->items[] = sprintf( 'RewriteRule %s %s', $from, $to );
			}
		}
	}

	private function add_agent( $item, $match ) {
		$from = $this->encode( ltrim( $item->get_url(), '/' ) );
		if ( $item->is_regex() ) {
			$from = $this->encode_regex( ltrim( $item->get_url(), '/' ) );
		}

		if ( ( $match->url_from || $match->url_notfrom ) && $match->user_agent ) {
			$this->items[] = sprintf( 'RewriteCond %%{HTTP_USER_AGENT} %s [NC]', ( $match->regex ? $this->encode_regex( $match->user_agent ) : $this->encode2nd( $match->user_agent ) ) );

			if ( $match->url_from ) {
				$to = $this->target( $item->get_action_type(), $match->url_from, $item->get_action_code(), $item->is_regex() );
				$this->items[] = sprintf( 'RewriteRule %s %s', $from, $to );
			}

			if ( $match->url_notfrom ) {
				$to = $this->target( $item->get_action_type(), $match->url_notfrom, $item->get_action_code(), $item->is_regex() );
				$this->items[] = sprintf( 'RewriteRule %s %s', $from, $to );
			}
		}
	}

	private function add_server( $item, $match ) {
		$match->url = $match->url_from;
		$this->items[] = sprintf( 'RewriteCond %%{HTTP_HOST} ^%s$ [NC]', preg_quote( $match->server ) );
		$this->add_url( $item, $match );
	}

	private function add_url( $item, $match ) {
		$url = $item->get_url();

		if ( $item->is_regex() === false && strpos( $url, '?' ) !== false || strpos( $url, '&' ) !== false ) {
			$url_parts = wp_parse_url( $url );
			$url = $url_parts['path'];
			$query = isset( $url_parts['query'] ) ? $url_parts['query'] : '';
			$this->items[] = sprintf( 'RewriteCond %%{QUERY_STRING} ^%s$', $query );
		}

		$to = $this->target( $item->get_action_type(), $match->url, $item->get_action_code(), $item->is_regex() );
		$from = $this->encode_from( $url );

		if ( $item->is_regex() ) {
			$from = $this->encode_regex( $item->get_url() );
		}

		if ( $to ) {
			$this->items[] = sprintf( 'RewriteRule %s %s', $from, $to );
		}
	}

	private function action_random( $data, $code, $regex ) {
		// Pick a WP post at random
		global $wpdb;

		$post = $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} ORDER BY RAND() LIMIT 0,1" );
		$url  = wp_parse_url( get_permalink( $post ) );

		return sprintf( '%s [R=%d,L]', $this->encode( $url['path'] ), $code );
	}

	private function action_pass( $data, $code, $regex ) {
		if ( $regex ) {
			return sprintf( '%s [L]', $this->encode2nd( $data ), $code );
		}
		return sprintf( '%s [L]', $this->encode2nd( $data ), $code );
	}

	private function action_error( $data, $code, $regex ) {
		if ( $code === 410 ) {
			return '/ [G]';
		}
		return '/ [F]';
	}

	private function action_url( $data, $code, $regex ) {
		if ( $regex ) {
			return sprintf( '%s [R=%d,L]', $this->encode2nd( $data ), $code );
		}
		return sprintf( '%s [R=%d,L]', $this->encode2nd( $data ), $code );
	}

	private function target( $action, $data, $code, $regex ) {
		$target = 'action_' . $action;

		if ( method_exists( $this, $target ) ) {
			return $this->$target( $data, $code, $regex );
		}
		return '';
	}

	private function generate() {
		$version = red_get_plugin_data( dirname( dirname( __FILE__ ) ) . '/redirection.php' );

		if ( count( $this->items ) === 0 ) {
			return '';
		}

		$text[] = '# Created by Redirection';
		$text[] = '# ' . date( 'r' );
		$text[] = '# Redirection ' . trim( $version['Version'] ) . ' - https://redirection.me';
		$text[] = '';

		// mod_rewrite section
		$text[] = '<IfModule mod_rewrite.c>';

		// Add http => https option
		$options = red_get_options();
		if ( $options['https'] ) {
			$text[] = 'RewriteCond %{HTTPS} off';
			$text[] = 'RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI}';
		}

		// Add redirects
		$text = array_merge( $text, array_filter( $this->items ) );

		// End of mod_rewrite
		$text[] = '</IfModule>';
		$text[] = '';

		// End of redirection section
		$text[] = '# End of Redirection';

		$text = implode( "\n", $text );
		return "\n" . $text . "\n";
	}

	public function add( $item ) {
		$target = 'add_' . $item->get_match_type();

		if ( method_exists( $this, $target ) ) {
			$this->$target( $item, $item->match );
		}
	}

	public function get( $existing = false ) {
		$text = $this->generate();

		if ( $existing ) {
			if ( preg_match( self::INSERT_REGEX, $existing ) > 0 ) {
				$text = preg_replace( self::INSERT_REGEX, str_replace( '$', '\\$', $text ), $existing );
			} else {
				$text = $text . "\n" . trim( $existing );
			}
		}

		return trim( $text );
	}

	public function save( $filename, $content_to_save = false ) {
		$existing = false;

		if ( file_exists( $filename ) ) {
			$existing = file_get_contents( $filename );
		}

		$file = @fopen( $filename, 'w' );
		if ( $file ) {
			$result = fwrite( $file, $this->get( $existing ) );
			fclose( $file );

			return $result !== false;
		}

		return false;
	}
}
