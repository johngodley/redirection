<?php

/**
 * Convert redirects to .htaccess format
 *
 * Ignores:
 * - Trailing slash flag
 * - Query flags
 */
class Red_Htaccess {
	/**
	 * Array of redirect lines
	 *
	 * @var array<string>
	 */
	private $items = array();

	const INSERT_REGEX = '@\n?# Created by Redirection(?:.*?)# End of Redirection\n?@sm';

	/**
	 * Encode the 'from' URL
	 *
	 * @param string $url From URL.
	 * @param bool   $ignore_trailing Ignore trailing slashes.
	 * @return string
	 */
	private function encode_from( $url, $ignore_trailing ) {
		$url = $this->encode( $url );

		// Apache 2 does not need a leading slashing
		$url = ltrim( $url, '/' );

		if ( $ignore_trailing ) {
			$url = rtrim( $url, '/' ) . '/?';
		}

		// Exactly match the URL
		return '^' . $url . '$';
	}

	/**
	 * URL encode some things, but other things can be passed through
	 *
	 * @param string $url URL.
	 * @return string
	 */
	private function encode2nd( $url ) {
		$allowed = [
			'%2F' => '/',
			'%3F' => '?',
			'%3A' => ':',
			'%3D' => '=',
			'%26' => '&',
			'%25' => '%',
			'+' => '%20',
			'%24' => '$',
			'%23' => '#',
		];

		$url = rawurlencode( $url );
		return $this->replace_encoding( $url, $allowed );
	}

	/**
	 * Replace encoded characters in a URL
	 *
	 * @param string $str Source string.
	 * @param array  $allowed Allowed encodings.
	 * @return string
	 */
	private function replace_encoding( $str, $allowed ) {
		foreach ( $allowed as $before => $after ) {
			$str = str_replace( $before, $after, $str );
		}

		return $str;
	}

	/**
	 * Encode a URL
	 *
	 * @param string $url URL.
	 * @return string
	 */
	private function encode( $url ) {
		$allowed = [
			'%2F' => '/',
			'%3F' => '?',
			'+' => '%20',
			'.' => '\\.',
		];

		return $this->replace_encoding( rawurlencode( $url ), $allowed );
	}

	/**
	 * Encode a regex URL
	 *
	 * @param string $url URL.
	 * @return string
	 */
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

	/**
	 * Add a referrer redirect
	 *
	 * @param Red_Item       $item Redirect item.
	 * @param Referrer_Match $match Redirect match.
	 * @return void
	 */
	private function add_referrer( $item, $match ) {
		$from = $this->encode_from( ltrim( $item->get_url(), '/' ), $item->source_flags && $item->source_flags->is_ignore_trailing() );
		if ( $item->is_regex() ) {
			$from = $this->encode_regex( ltrim( $item->get_url(), '/' ) );
		}

		if ( ( $match->url_from || $match->url_notfrom ) && $match->referrer ) {
			$referrer = $match->regex ? $this->encode_regex( $match->referrer ) : $this->encode_from( $match->referrer, false );
			$to = false;

			if ( $match->url_from ) {
				$to = $this->target( $item->get_action_type(), $match->url_from, $item->get_action_code(), $item->get_match_data() );
			}

			if ( $match->url_notfrom ) {
				$to = $this->target( $item->get_action_type(), $match->url_notfrom, $item->get_action_code(), $item->get_match_data() );
			}

			$this->items[] = sprintf( 'RewriteCond %%{HTTP_REFERER} %s [NC]', $referrer );
			if ( $to ) {
				$this->items[] = sprintf( 'RewriteRule %s %s', $from, $to );
			}
		}
	}

	/**
	 * Add a useragent redirect
	 *
	 * @param Red_Item    $item Redirect item.
	 * @param Agent_Match $match Redirect match.
	 * @return void
	 */
	private function add_agent( $item, $match ) {
		$from = $this->encode( ltrim( $item->get_url(), '/' ) );
		if ( $item->is_regex() ) {
			$from = $this->encode_regex( ltrim( $item->get_url(), '/' ) );
		}

		if ( ( $match->url_from || $match->url_notfrom ) && $match->agent ) {
			$agent = ( $match->regex ? $this->encode_regex( $match->agent ) : $this->encode2nd( $match->agent ) );
			$to = false;

			if ( $match->url_from ) {
				$to = $this->target( $item->get_action_type(), $match->url_from, $item->get_action_code(), $item->get_match_data() );
			}

			if ( $match->url_notfrom ) {
				$to = $this->target( $item->get_action_type(), $match->url_notfrom, $item->get_action_code(), $item->get_match_data() );
			}

			$this->items[] = sprintf( 'RewriteCond %%{HTTP_USER_AGENT} %s [NC]', $agent );
			if ( $to ) {
				$this->items[] = sprintf( 'RewriteRule %s %s', $from, $to );
			}
		}
	}

	/**
	 * Add a server redirect
	 *
	 * @param Red_Item     $item Redirect item.
	 * @param Server_Match $match Redirect match.
	 * @return void
	 */
	private function add_server( $item, $match ) {
		$match->url = $match->url_from;
		$this->items[] = sprintf( 'RewriteCond %%{HTTP_HOST} ^%s$ [NC]', preg_quote( wp_parse_url( $match->server, PHP_URL_HOST ), '/' ) );
		$this->add_url( $item, $match );
	}

	/**
	 * Add a redirect
	 *
	 * @param Red_Item  $item Redirect item.
	 * @param Red_Match $match Redirect match.
	 * @return void
	 */
	private function add_url( $item, $match ) {
		$url = $item->get_url();

		if ( $item->is_regex() === false && strpos( $url, '?' ) !== false ) {
			$url_parts = wp_parse_url( $url );

			if ( isset( $url_parts['path'] ) ) {
				$url = $url_parts['path'];
				$query = isset( $url_parts['query'] ) ? $url_parts['query'] : '';
				$this->items[] = sprintf( 'RewriteCond %%{QUERY_STRING} ^%s$', $query );
			}
		}

		$to = $this->target( $item->get_action_type(), $match->url, $item->get_action_code(), $item->get_match_data() );
		$from = $this->encode_from( $url, $item->source_flags && $item->source_flags->is_ignore_trailing() );

		if ( $item->is_regex() ) {
			$from = $this->encode_regex( $item->get_url() );
		}

		if ( $to ) {
			$this->items[] = sprintf( 'RewriteRule %s %s', $from, $to );
		}
	}

	/**
	 * Add a redirect flags
	 *
	 * @return string
	 */
	private function add_flags( $current, array $flags ) {
		return $current . ' [' . implode( ',', $flags ) . ']';
	}

	/**
	 * Get source flags
	 *
	 * @param array<string> $existing Existing flags.
	 * @param array<string> $source Source flags.
	 * @param string        $url URL.
	 * @return array<string>
	 */
	private function get_source_flags( array $existing, array $source, $url ) {
		$flags = [];

		if ( isset( $source['flag_case'] ) && $source['flag_case'] ) {
			$flags[] = 'NC';
		}

		if ( isset( $source['flag_query'] ) && $source['flag_query'] === 'pass' ) {
			$flags[] = 'QSA';
		}

		if ( strpos( $url, '#' ) !== false ) {
			$flags[] = 'NE';
		}

		return array_merge( $existing, $flags );
	}

	/**
	 * Add a random target.
	 *
	 * @param [type] $data
	 * @param [type] $code
	 * @param [type] $match_data
	 * @return string
	 */
	private function action_random( $data, $code, $match_data ) {
		// Pick a WP post at random
		global $wpdb;

		$post = $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} ORDER BY RAND() LIMIT 0,1" );
		$url  = wp_parse_url( get_permalink( $post ) );

		$flags = [ sprintf( 'R=%d', $code ) ];
		$flags[] = 'L';
		$flags = $this->get_source_flags( $flags, $match_data['source'], $data );

		return $this->add_flags( $this->encode( $url['path'] ), $flags );
	}

	/**
	 * Add a passthrough target.
	 *
	 * @param [type] $data
	 * @param [type] $code
	 * @param [type] $match_data
	 * @return string
	 */
	private function action_pass( $data, $code, $match_data ) {
		$flags = $this->get_source_flags( [ 'L' ], $match_data['source'], $data );

		return $this->add_flags( $this->encode2nd( $data ), $flags );
	}

	/**
	 * Add an error target.
	 *
	 * @param [type] $data
	 * @param [type] $code
	 * @param [type] $match_data
	 * @return string
	 */
	private function action_error( $data, $code, $match_data ) {
		$flags = $this->get_source_flags( [ 'F' ], $match_data['source'], $data );

		if ( $code === 410 ) {
			$flags = $this->get_source_flags( [ 'G' ], $match_data['source'], $data );
		}

		return $this->add_flags( '/', $flags );
	}

	/**
	 * Add a URL target.
	 *
	 * @param [type] $data
	 * @param [type] $code
	 * @param [type] $match_data
	 * @return string
	 */
	private function action_url( $data, $code, $match_data ) {
		$flags = [ sprintf( 'R=%d', $code ) ];
		$flags[] = 'L';
		$flags = $this->get_source_flags( $flags, $match_data['source'], $data );

		return $this->add_flags( $this->encode2nd( $data ), $flags );
	}

	/**
	 * Return URL target
	 *
	 * @param [type] $data
	 * @param [type] $code
	 * @param [type] $match_data
	 * @return string
	 */
	private function target( $action, $data, $code, $match_data ) {
		$target = 'action_' . $action;

		if ( method_exists( $this, $target ) ) {
			return $this->$target( $data, $code, $match_data );
		}

		return '';
	}

	/**
	 * Generate the .htaccess file in memory
	 *
	 * @return string
	 */
	private function generate() {
		$version = red_get_plugin_data( dirname( dirname( __FILE__ ) ) . '/redirection.php' );

		if ( count( $this->items ) === 0 ) {
			return '';
		}

		$text = [
			'# Created by Redirection',
			'# ' . date( 'r' ),
			'# Redirection ' . trim( $version['Version'] ) . ' - https://redirection.me',
			'',
			'<IfModule mod_rewrite.c>',
		];

		// Add http => https option
		$options = red_get_options();
		if ( $options['https'] ) {
			$text[] = 'RewriteCond %{HTTPS} off';
			$text[] = 'RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]';
		}

		// Add redirects
		$text = array_merge( $text, array_filter( array_map( [ $this, 'sanitize_redirect' ], $this->items ) ) );

		// End of mod_rewrite
		$text[] = '</IfModule>';
		$text[] = '';

		// End of redirection section
		$text[] = '# End of Redirection';

		$text = implode( "\n", $text );
		return "\n" . $text . "\n";
	}

	/**
	 * Add a redirect to the file
	 *
	 * @param Red_Item $item Redirect.
	 * @return void
	 */
	public function add( $item ) {
		$target = 'add_' . $item->get_match_type();

		if ( method_exists( $this, $target ) && $item->is_enabled() ) {
			$this->$target( $item, $item->match );
		}
	}

	/**
	 * Get the .htaccess file
	 *
	 * @param boolean $existing Existing .htaccess data.
	 * @return string
	 */
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

	/**
	 * Sanitize the redirect
	 *
	 * @param string $text Text.
	 * @return string
	 */
	public function sanitize_redirect( $text ) {
		return str_replace( [ '<?', '>' ], '', $text );
	}

	/**
	 * Sanitize the filename
	 *
	 * @param string $filename Filename.
	 * @return string
	 */
	public function sanitize_filename( $filename ) {
		return str_replace( '.php', '', $filename );
	}

	/**
	 * Save the .htaccess to a file
	 *
	 * @param string  $filename Filename to save.
	 * @param boolean $content_to_save Content to save.
	 * @return bool
	 */
	public function save( $filename, $content_to_save = false ) {
		$existing = false;
		$filename = $this->sanitize_filename( $filename );

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
