<?php

namespace Redirection\Cache;

/**
 * Manage cache providers.
 */
class Registry {
	/**
	 * @var Provider[]|null
	 */
	private static $providers = null;

	/**
	 * Get all providers.
	 *
	 * @return Provider[]
	 */
	public static function get_all(): array {
		if ( self::$providers === null ) {
			$providers = apply_filters( 'redirection_cache_providers', [] );

			self::$providers = is_array( $providers )
				? array_values(
					array_filter(
						$providers,
						function ( $provider ) {
							return $provider instanceof Provider;
						}
					)
				)
				: [];
		}

		return self::$providers;
	}

	/**
	 * Get active providers.
	 *
	 * @return Provider[]
	 */
	public static function get_active(): array {
		$active = [];

		foreach ( self::get_all() as $provider ) {
			try {
				if ( $provider->is_active() ) {
					$active[] = $provider;
				}
			} catch ( \Throwable $e ) {
				unset( $e );
			}
		}

		return $active;
	}

	/**
	 * Set resolved providers.
	 *
	 * @param Provider[]|null $providers Providers.
	 */
	public static function set_providers( ?array $providers ): void {
		self::$providers = $providers;
	}
}
