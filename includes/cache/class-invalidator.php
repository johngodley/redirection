<?php

namespace Redirection\Cache;

use Redirection\Settings\Settings;

/**
 * Queue and dispatch cache invalidations.
 */
class Invalidator {
	/**
	 * Maximum URLs before a full purge.
	 */
	const MAX_URLS = 20;

	/**
	 * @var Invalidator|null
	 */
	private static $instance = null;

	/**
	 * @var string[]
	 */
	private $urls = [];

	/**
	 * @var boolean
	 */
	private $full = false;

	/**
	 * @var boolean
	 */
	private $queued = false;

	/**
	 * @var boolean|null
	 */
	private $enabled = null;

	public static function init(): Invalidator {
		if ( self::$instance === null ) {
			self::$instance = new Invalidator();
		}

		return self::$instance;
	}

	public static function reset(): void {
		self::$instance = null;
	}

	/**
	 * @param boolean|null $enabled Override, or null to read the setting.
	 */
	public function set_enabled( $enabled ): void {
		$this->enabled = $enabled;
	}

	public function queue_url( string $source ): void {
		$this->queued = true;

		if ( $this->full ) {
			return;
		}

		$variants = $this->get_url_variants( $source );

		if ( count( $variants ) === 0 ) {
			$this->full = true;

			return;
		}

		foreach ( $variants as $url ) {
			$this->urls[] = $url;
		}

		if ( count( $this->urls ) > self::MAX_URLS ) {
			$this->full = true;
		}
	}

	public function queue_full(): void {
		$this->queued = true;
		$this->full = true;
	}

	/** @return string[] */
	private function get_url_variants( string $source ): array {
		if ( trim( $source ) === '' ) {
			return [];
		}

		$host = wp_parse_url( $source, PHP_URL_HOST );
		$absolute = is_string( $host ) && $host !== '' ? $source : home_url( '/' . ltrim( $source, '/' ) );

		if ( strpos( $absolute, '?' ) !== false || strpos( $absolute, '#' ) !== false ) {
			return [ $absolute ];
		}

		if ( substr( $absolute, -1 ) === '/' ) {
			return [ $absolute, rtrim( $absolute, '/' ) ];
		}

		return [ $absolute, $absolute . '/' ];
	}

	private function is_enabled(): bool {
		if ( $this->enabled !== null ) {
			return $this->enabled;
		}

		$settings = Settings::get();

		return ! empty( $settings['cache_purge'] );
	}

	/** @return Result[] */
	public function flush(): array {
		if ( ! $this->queued ) {
			return [];
		}

		$urls = array_values( array_unique( $this->urls ) );
		$full = $this->full || count( $urls ) === 0 || count( $urls ) > self::MAX_URLS;

		$this->urls = [];
		$this->full = false;
		$this->queued = false;

		if ( ! $this->is_enabled() ) {
			return [];
		}

		$results = [];

		try {
			foreach ( Registry::get_active() as $provider ) {
				$results[] = $this->purge( $provider, $urls, $full );
			}
		} catch ( \Throwable $e ) {
			unset( $e );
		}

		try {
			( new Generic() )->flush( $full ? [] : $urls, $full );
		} catch ( \Throwable $e ) {
			unset( $e );
		}

		return $results;
	}

	/**
	 * @param string[] $urls URLs to purge.
	 */
	private function purge( Provider $provider, array $urls, bool $full ): Result {
		try {
			if ( $full || ! $provider->supports_url_purge() ) {
				return new Result( $provider, $provider->purge_all() ? Result::PURGED_ALL : Result::FAILED );
			}

			return new Result(
				$provider,
				$provider->purge_urls( $urls ) ? Result::PURGED_URLS : Result::FAILED,
				$urls
			);
		} catch ( \Throwable $e ) {
			return new Result( $provider, Result::FAILED );
		}
	}
}
