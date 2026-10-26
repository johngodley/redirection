<?php

/**
 * Transform URL shortcodes
 *
 * @phpstan-type ShortcodeAttrs array<string, string>|string
 */
class Red_Url_Transform {
	/**
	 * Placeholders in the URL being transformed, and the text they replace.
	 *
	 * @var array<string, string>
	 */
	private $placeholders = [];

	/**
	 * Replace special tags in the target URL.
	 *
	 * From the distant Redirection past. Undecided whether to keep
	 *
	 * Text that must not be used as a shortcode, such as text from the requested URL, can be given placeholders. These
	 * are restored after the shortcodes have been run, and in any shortcode content and attributes.
	 *
	 * @param string $url Target URL.
	 * @param array<string, string> $placeholders Placeholders in the URL, and the text they replace.
	 * @return string
	 */
	public function transform( $url, array $placeholders = [] ) {
		// Deprecated number post ID
		if ( is_numeric( $url ) ) {
			$permalink = get_permalink( intval( $url, 10 ) );

			if ( $permalink !== false ) {
				return $permalink;
			}
		}

		$this->placeholders = $placeholders;

		global $shortcode_tags;

		$shortcode_copy = array_merge( [], $shortcode_tags );

		remove_all_shortcodes();

		$shortcodes = apply_filters(
			'redirection_shortcodes',
			[
				'userid',
				'userlogin',
				'unixtime',  // Also replaces $dec$

			// These require content
				'md5',
				'upper',
				'lower',
				'dashes',
				'underscores',
			]
		);

		foreach ( $shortcodes as $code ) {
			add_shortcode( $code, [ $this, 'do_shortcode' ] );
		}

		$url = do_shortcode( $url );

		// Restore shortcodes
		// phpcs:ignore
		$shortcode_tags = array_merge( [], $shortcode_copy );

		$url = $this->restore( $url );
		$this->placeholders = [];

		return $url;
	}

	/**
	 * Restore any placeholders in the text.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	private function restore( $text ) {
		return strtr( $text, $this->placeholders );
	}

	/**
	 * Perform a shortcode
	 *
	 * @param ShortcodeAttrs $attrs Shortcode attributes.
	 * @param string|null $content Shortcode content.
	 * @param string $tag Shortcode tag.
	 * @return string
	 */
	public function do_shortcode( $attrs, $content, $tag ) {
		$content = $this->restore( $content ?? '' );
		$attrs = is_array( $attrs ) ? array_map( [ $this, 'restore' ], $attrs ) : $this->restore( $attrs );
		$user = wp_get_current_user();

		switch ( $tag ) {
			case 'userid':
				return (string) $user->ID;

			case 'userlogin':
				return $user->ID > 0 ? $user->user_login : '';

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
}
