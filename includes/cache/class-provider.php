<?php

namespace Redirection\Cache;

/**
 * Cache provider interface.
 */
abstract class Provider {
	/**
	 * Get the provider ID.
	 */
	abstract public function get_id(): string;

	/**
	 * Get the display name.
	 */
	abstract public function get_name(): string;

	/**
	 * Check whether the provider is active.
	 */
	abstract public function is_active(): bool;

	/**
	 * Purge all cached content.
	 */
	abstract public function purge_all(): bool;

	/**
	 * Check whether individual URLs can be purged.
	 */
	public function supports_url_purge(): bool {
		return false;
	}

	/**
	 * Purge specific URLs.
	 *
	 * @param string[] $urls Absolute URLs.
	 */
	public function purge_urls( array $urls ): bool {
		return false;
	}
}
