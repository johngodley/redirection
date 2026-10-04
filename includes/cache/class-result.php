<?php

namespace Redirection\Cache;

/**
 * Cache invalidation result.
 */
class Result {
	const PURGED_URLS = 'purged-urls';
	const PURGED_ALL = 'purged-all';
	const FAILED = 'failed';

	/**
	 * @var Provider
	 */
	private $provider;

	/**
	 * @var string
	 */
	private $status;

	/**
	 * @var string[]
	 */
	private $urls;

	/**
	 * @param Provider $provider The provider this describes.
	 * @param string   $status One of the class constants.
	 * @param string[] $urls URLs purged, for PURGED_URLS.
	 */
	public function __construct( Provider $provider, string $status, array $urls = [] ) {
		$this->provider = $provider;
		$this->status = $status;
		$this->urls = $urls;
	}

	public function get_status(): string {
		return $this->status;
	}

	public function get_id(): string {
		return $this->provider->get_id();
	}

	/**
	 * @return array<string, mixed>
	 */
	public function to_json(): array {
		return [
			'id' => $this->provider->get_id(),
			'name' => $this->provider->get_name(),
			'status' => $this->status,
			'urls' => $this->urls,
		];
	}
}
