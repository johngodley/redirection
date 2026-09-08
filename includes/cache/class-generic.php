<?php

namespace Redirection\Cache;

/**
 * Trigger generic cache invalidation.
 */
class Generic {
	const OPTION = 'redirection_flush';

	/**
	 * @param string[] $urls Affected URLs, empty for a full flush.
	 * @param boolean  $full Whether everything is being invalidated.
	 */
	public function flush( array $urls, bool $full ): void {
		// Ensure every call updates the option.
		update_option( self::OPTION, uniqid( '', true ), false );

		do_action( 'redirection_flush_caches', $urls, $full );
	}
}
