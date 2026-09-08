<?php

require_once PLUGIN_PATH . '/includes/cache/class-provider.php';

use Redirection\Cache\Provider;

/**
 * Cache provider test double.
 */
class Redirection_Cache_Fake_Provider extends Provider {
	/** @var string[]|null */
	public $purged_urls = null;

	/** @var bool */
	public $purged_all = false;

	private $id;
	private $active;
	private $url_purge;
	private $throws;
	private $succeeds;

	public function __construct( string $id, bool $active = true, bool $url_purge = true, bool $throws = false, bool $succeeds = true ) {
		$this->id = $id;
		$this->active = $active;
		$this->url_purge = $url_purge;
		$this->throws = $throws;
		$this->succeeds = $succeeds;
	}

	public function get_id(): string {
		return $this->id;
	}

	public function get_name(): string {
		return 'Fake ' . $this->id;
	}

	public function is_active(): bool {
		return $this->active;
	}

	public function supports_url_purge(): bool {
		return $this->url_purge;
	}

	public function purge_urls( array $urls ): bool {
		if ( $this->throws ) {
			throw new \RuntimeException( 'boom' );
		}

		$this->purged_urls = $urls;

		return $this->succeeds;
	}

	public function purge_all(): bool {
		if ( $this->throws ) {
			throw new \RuntimeException( 'boom' );
		}

		$this->purged_all = true;

		return $this->succeeds;
	}
}
