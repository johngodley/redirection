<?php

/**
 * Transform URL shortcodes
 */
class Red_Url_Transform {
	/**
	 * Replace special tags in the target URL.
	 *
	 * From the distant Redirection past. Undecided whether to keep
	 *
	 * @param String $url Target URL.
	 * @return String
	 */
	public function transform( $url ) {
		// Deprecated number post ID
		if ( is_numeric( $url ) ) {
			$permalink = get_permalink( intval( $url, 10 ) );

			if ( $permalink ) {
				return $permalink;
			}
		}

		global $shortcode_tags;

		$shortcode_copy = array_merge( [], $shortcode_tags );

		remove_all_shortcodes();

		$shortcodes = [
			'userid',
			'userlogin',
			'unixtime',  // Also replaces $dec$

			// These require content
			'md5',
			'upper',
			'lower',
			'dashes',
			'underscores',
		];

		foreach ( $shortcodes as $code ) {
			add_shortcode( $code, [ $this, 'do_shortcode' ] );
		}

		// Support deprecated tags
		$url = $this->transform_deprecated( $url );
		$url = do_shortcode( $url );

		// Restore shortcodes
		// phpcs:ignore
		$shortcode_tags = array_merge( [], $shortcode_copy );

		return $url;
	}

	/**
	 * Peform a shortcode
	 *
	 * @param array  $attrs Shortcode attributes.
	 * @param string $content Shortcode content.
	 * @param string $tag Shortcode tag.
	 * @return string
	 */
	public function do_shortcode( $attrs, $content, $tag ) {
		$user = wp_get_current_user();

		switch ( $tag ) {
			case 'userid':
				return (string) ( $user->ID || 0 );

			case 'userlogin':
				return $user->ID ? $user->user_login : '';

			case 'unixtime':
				return (string) time();

			case 'md5':
				return md5( $content );

			case 'upper':
				return strtoupper( $content );

			case 'lower':
				return strtolower( $content );

			case 'dashes':
				return str_replace( [ '_', ' ' ], '-', $content );

			case 'underscores':
				return str_replace( [ '-', ' ' ], '_', $content );
		}

		return apply_filters( 'redirection_url_transform', '', $tag, $attrs, $content );
	}

	/**
	 * Convert deprecated inline tags to shortcodes.
	 *
	 * @param string $url URL.
	 * @return string
	 */
	private function transform_deprecated( $url ) {
		$url = str_replace( '%userid%', '[userid]', $url );
		$url = str_replace( '%userlogin%', '[userlogin]', $url );
		$url = str_replace( '%userurl%', '[userurl]', $url );

		return $url;
	}
}
