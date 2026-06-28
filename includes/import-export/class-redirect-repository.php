<?php

namespace Redirection\ImportExport;

/**
 * Repository wrapper for redirect lookups and persistence.
 */
class RedirectRepository {
	/**
	 * @param int $redirect_id
	 * @return \Red_Item|false
	 */
	public function get( $redirect_id ) {
		return \Red_Item::get_by_id( intval( $redirect_id, 10 ) );
	}

	/**
	 * @param string $url
	 * @param bool $is_regex
	 * @return \Red_Item|false
	 */
	public function get_for_url( $url, $is_regex ) {
		global $wpdb;

		$id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$wpdb->prefix}redirection_items WHERE url=%s AND regex=%d ORDER BY id ASC LIMIT 1",
				$url,
				$is_regex ? 1 : 0
			)
		);

		if ( $id === null ) {
			return false;
		}

		return $this->get( intval( $id, 10 ) );
	}

	/**
	 * @param array<string, mixed> $redirect
	 * @return \Red_Item|\WP_Error|false
	 */
	public function create( array $redirect ) {
		return \Red_Item::create( $redirect );
	}

	/**
	 * @param \Red_Item $existing
	 * @param array<string, mixed> $redirect
	 * @return bool
	 */
	public function update( \Red_Item $existing, array $redirect ) {
		$result = $existing->update( $redirect );

		return ! is_wp_error( $result );
	}

	/**
	 * @param \Red_Item $existing
	 * @param bool $enabled
	 * @return void
	 */
	public function set_enabled( \Red_Item $existing, $enabled ) {
		if ( $enabled ) {
			$existing->enable();
		} else {
			$existing->disable();
		}
	}

	/**
	 * @return array<int, \Red_Item>|false
	 */
	public function get_all() {
		return \Red_Item::get_all();
	}

	/**
	 * @param int $module_id
	 * @return array<int, \Red_Item>|false
	 */
	public function get_all_for_module( $module_id ) {
		return \Red_Item::get_all_for_module( intval( $module_id, 10 ) );
	}
}
