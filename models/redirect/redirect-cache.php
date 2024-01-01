<?php

/**
 * Redirect caching.
 *
 * This is based on server requests and not database requests.
 *
 * The client requests a URL. We use the requested URL, the `cache_key` setting, and the plugin version, to look for a cache entry.
 *
 * The `cache_key` is updated each time *any* redirect is updated. This is because a URL can be affected by other redirects, such as regular expressions
 * and redirects with dynamic conditions (i.e. cookie, login status etc).
 *
 * We include the plugin version as data can change between plugin versions, and it is safest to use new cache entries.
 *
 * If we have a cache hit then the data is used to perform the redirect.php
 *
 * If we do not have a cache hit then we request the URL from the database and perform redirect matches.
 *
 * After matching has been performed we then try and update the cache:
 *   - if no match was found, cache an empty result
 *   - if a match was found and no dynamic redirects were encountered, then cache that redirect only
 *   - if a match was found and dynamic redirects were involved then cache all redirects
 *
 * We have a maximum number of redirects that can be cached to avoid saturating the cache.
 */
class Redirect_Cache {
	const EMPTY_VALUE = 'empty';
	const CACHE_MAX = 10;

	/**
	 * Singleton
	 *
	 * @var Redirect_Cache|null
	 */
	private static $instance = null;

	/**
	 * Array of URLs that have been cached
	 *
	 * @var array
	 */
	private $cached = [];

	/**
	 * Cache key. Changed to current time whenever a redirect is updated.
	 *
	 * @var integer
	 */
	private $key = 0;

	/**
	 * Initialiser
	 *
	 * @return Redirect_Cache
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Redirect_Cache();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->reset();
	}

	public function reset() {
		$settings = red_get_options();
		$this->key = $settings['cache_key'];
		$this->cached = [];
	}

	/**
	 * Is the cache enabled?
	 *
	 * @return boolean
	 */
	public function can_cache() {
		return $this->key > 0;
	}

	/**
	 * Get the current cache key
	 *
	 * @param string $url URL we are looking at.
	 * @return string
	 */
	private function get_key( $url ) {
		return apply_filters( 'redirection_cache_key', md5( $url ) . '-' . (string) $this->key . '-' . REDIRECTION_VERSION );
	}

	/**
	 * Get the cache entry for a URL
	 *
	 * @param string $url Requested URL.
	 * @return Red_Item[]|bool
	 */
	public function get( $url ) {
		if ( ! $this->can_cache() ) {
			return false;
		}

		$cache_key = $this->get_key( $url );

		// Look in cache
		$false = false;
		$result = wp_cache_get( $cache_key, 'redirection', false, $false );

		// If a result was found then remember we are using the cache so we don't need to re-save it later
		if ( $result !== false ) {
			$this->cached[ $url ] = true;
		}

		// Empty value is a special case. Storing [] in the cache doesn't work, so we store the special EMPTY_VALUE to represent []
		if ( $result === self::EMPTY_VALUE ) {
			return [];
		}

		return $result;
	}

	/**
	 * Set the cache for a URL
	 *
	 * @param string         $url URL to cache.
	 * @param Red_Item|false $matched The matched redirect.
	 * @param Red_Item[]     $redirects All of the redirects the match the URL.
	 * @return boolean
	 */
	public function set( $url, $matched, $redirects ) {
		if ( ! $this->can_cache() || isset( $this->cached[ $url ] ) ) {
			return false;
		}

		$cache_key = $this->get_key( $url );

		// Default store the match redirect
		$rows = [];
		if ( $matched ) {
			$rows[] = $matched;
		}

		// Are any of the redirects before, and including, the match a dynamic redirect?
		$dynamic = $this->get_dynamic_matched( $redirects, $matched );
		if ( count( $dynamic ) > 0 ) {
			// Store all dynamic redirects
			$rows = $dynamic;
		}

		// Have we exceeded our limit?
		if ( count( $rows ) > self::CACHE_MAX ) {
			return false;
		}

		$converted = $this->convert_to_rows( $rows );
		$value = count( $converted ) === 0 ? self::EMPTY_VALUE : $converted;

		wp_cache_set( $cache_key, $value, 'redirection' );

		return true;
	}

	/**
	 * Convert a Red_Item to a format suitable for storing in the cache
	 *
	 * @param Red_Item[] $rows Redirects.
	 * @return array
	 */
	private function convert_to_rows( array $rows ) {
		$converted = [];

		foreach ( $rows as $row ) {
			$converted[] = $row->to_sql();
		}

		return $converted;
	}

	/**
	 * If there are dynamic redirects before the matched redirect then return all dynamic redirects (including the matched one), otherwise return nothing.
	 *
	 * If the matched redirect is a static redirect then we include it in the list, but don't include any redirects after.
	 *
	 * @param Red_Item[]     $redirects Array of redirects.
	 * @param Red_Item|false $matched The matched item.
	 * @return Red_Item[]
	 */
	private function get_dynamic_matched( array $redirects, $matched ) {
		$dynamic = [];

		foreach ( $redirects as $redirect ) {
			if ( $redirect->is_dynamic() ) {
				$dynamic[] = $redirect;
			}

			// Is this the matched redirect?
			if ( $matched === $redirect ) {
				// Yes. Do we have any dynamic redirects so far?
				if ( count( $dynamic ) === 0 ) {
					// No. Just return an empty array
					return [];
				}

				if ( ! $matched->is_dynamic() ) {
					// We need to include the non-dynamic redirect in the list
					return array_merge( $dynamic, [ $matched ] );
				}
			}
		}

		return $dynamic;
	}
}
