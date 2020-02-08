<?php

class Redirection_Canonical {
	private $aliases = [];
	private $force_https = false;
	private $preferred_domain = '';
	private $actual_domain = '';

	public function __construct( $force_https, $preferred_domain, $aliases, $configured_domain ) {
		$this->force_https = $force_https;
		$this->aliases = $aliases;
		$this->preferred_domain = $preferred_domain;
		$this->actual_domain = $configured_domain;
	}

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

	private function get_preferred_aliases( $server ) {
		if ( $this->need_force_www( $server ) || $this->need_remove_www( $server ) ) {
			return [ $server ];
		}

		return [];
	}

	// A final check to prevent obvious site errors.
	private function is_configured_domain( $server ) {
		return $server === $this->actual_domain;
	}

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

	private function need_force_www( $server ) {
		$has_www = substr( $server, 0, 4 ) === 'www.';

		return $this->preferred_domain === 'www' && ! $has_www;
	}

	private function need_remove_www( $server ) {
		$has_www = substr( $server, 0, 4 ) === 'www.';

		return $this->preferred_domain === 'nowww' && $has_www;
	}

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
