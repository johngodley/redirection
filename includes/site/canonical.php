<?php

/**
 * Canonical redirects.
 */
class Redirection_Canonical {
	/**
	 * Aliased domains. These are domains that should be redirected to the WP domain.
	 *
	 * @var string[]
	 */
	private $aliases = [];

	/**
	 * Force HTTPS.
	 *
	 * @var boolean
	 */
	private $force_https = false;

	/**
	 * Preferred domain. WWW or no WWW.
	 *
	 * @var string
	 */
	private $preferred_domain = '';

	/**
	 * Current WP domain.
	 *
	 * @var string
	 */
	private $actual_domain = '';

	/**
	 * Constructor
	 *
	 * @param boolean  $force_https `true` to force https, `false` otherwise.
	 * @param string   $preferred_domain `www`, `nowww`, or empty string.
	 * @param string[] $aliases Array of domain aliases.
	 * @param string   $configured_domain Current domain.
	 */
	public function __construct( $force_https, $preferred_domain, $aliases, $configured_domain ) {
		$this->force_https = $force_https;
		$this->aliases = $aliases;
		$this->preferred_domain = $preferred_domain;
		$this->actual_domain = $configured_domain;
	}

	/**
	 * Get the canonical redirect.
	 *
	 * @param string $server Current server URL.
	 * @param string $request Current request.
	 * @return string|false
	 */
	public function get_redirect( $server, $request ) {
		$aliases = array_merge(
			$this->get_preferred_aliases( $server ),
			$this->aliases
		);

		if ( $this->force_https && ! is_ssl() ) {
			$aliases[] = $server;
		}

		$aliases = array_unique( $aliases );
		if ( count( $aliases ) > 0 ) {
			foreach ( $aliases as $alias ) {
				if ( $server === $alias ) {
					// Redirect this to the WP url
					$target = $this->get_canonical_target( get_bloginfo( 'url' ) );
					if ( ! $target ) {
						return false;
					}

					$target = esc_url_raw( $target ) . $request;

					return apply_filters( 'redirect_canonical_target', $target );
				}
			}
		}

		return false;
	}

	/**
	 * Get the preferred alias
	 *
	 * @param string $server Current server.
	 * @return string[]
	 */
	private function get_preferred_aliases( $server ) {
		if ( $this->need_force_www( $server ) || $this->need_remove_www( $server ) ) {
			return [ $server ];
		}

		return [];
	}

	/**
	 * A final check to prevent obvious site errors.
	 *
	 * @param string $server Current server.
	 * @return boolean
	 */
	private function is_configured_domain( $server ) {
		return $server === $this->actual_domain;
	}

	/**
	 * Get the canonical target
	 *
	 * @param string $server Current server.
	 * @return string|false
	 */
	private function get_canonical_target( $server ) {
		$canonical = rtrim( red_parse_domain_only( $server ), '/' );

		if ( $this->need_force_www( $server ) ) {
			$canonical = 'www.' . ltrim( $canonical, 'www.' );
		} elseif ( $this->need_remove_www( $server ) ) {
			$canonical = ltrim( $canonical, 'www.' );
		}

		$canonical = ( is_ssl() ? 'https://' : 'http://' ) . $canonical;

		if ( $this->force_https ) {
			$canonical = str_replace( 'http://', 'https://', $canonical );
		}

		if ( $this->is_configured_domain( $canonical ) ) {
			return $canonical;
		}

		return false;
	}

	/**
	 * Do we need to force WWW?
	 *
	 * @param string $server Current server.
	 * @return boolean
	 */
	private function need_force_www( $server ) {
		$has_www = substr( $server, 0, 4 ) === 'www.';

		return $this->preferred_domain === 'www' && ! $has_www;
	}

	/**
	 * Do we need to remove WWW?
	 *
	 * @param string $server Current server.
	 * @return boolean
	 */
	private function need_remove_www( $server ) {
		$has_www = substr( $server, 0, 4 ) === 'www.';

		return $this->preferred_domain === 'nowww' && $has_www;
	}

	/**
	 * Return the full URL relocated to another domain. Certain URLs are protected from this.
	 *
	 * @param string $relocate Target domain.
	 * @param string $domain Current domain.
	 * @param string $request Current request.
	 * @return string|false
	 */
	public function relocate_request( $relocate, $domain, $request ) {
		$relocate = rtrim( $relocate, '/' );

		$protected = apply_filters( 'redirect_relocate_protected', [
			'/wp-admin',
			'/wp-login.php',
			'/wp-json/',
		] );

		$not_protected = array_filter( $protected, function( $base ) use ( $request ) {
			if ( substr( $request, 0, strlen( $base ) ) === $base ) {
				return true;
			}

			return false;
		} );

		if ( $domain !== red_parse_domain_only( $relocate ) && count( $not_protected ) === 0 ) {
			return apply_filters( 'redirect_relocate_target', $relocate . $request );
		}

		return false;
	}
}
