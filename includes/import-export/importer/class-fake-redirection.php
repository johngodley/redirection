<?php

namespace Redirection\ImportExport\Importer;

/**
 * @phpstan-import-type ImporterInfo from Plugin
 */
class FakeRedirection extends Plugin {
	/**
	 * @var RedirectItemMapper
	 */
	private $mapper;

	public function __construct( ?RedirectItemMapper $mapper = null ) {
		$this->mapper = $mapper ? $mapper : new RedirectItemMapper();
	}

	/**
	 * @return bool
	 */
	public function supports_preview() {
		return true;
	}

	/**
	 * @return array<int, array<string, mixed>|false>
	 */
	protected function get_redirect_items() {
		$items = array();

		foreach ( $this->get_redirects() as $redirect ) {
			$items[] = $this->get_item_for_redirect( $redirect );
		}

		return $items;
	}

	/**
	 * @return list<object{match: string, to: string, redirect_code?: string}>
	 */
	private function get_redirects() {
		global $wpdb;

		if ( ! $this->table_exists( 'irrp_redirections' ) ) {
			return [];
		}

		$table = $wpdb->prefix . 'irrp_redirections';
		$meta_table = $wpdb->prefix . 'irrp_redirectionmeta';
		$join = '';

		if ( $this->table_exists( 'irrp_redirectionmeta' ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$join = $wpdb->prepare( " LEFT JOIN {$meta_table} AS meta ON meta.redirect_id = redirects.id AND meta.meta_key = %s", 'redirect_code' );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results( "SELECT redirects.*, COALESCE( meta.meta_value, '301' ) AS redirect_code FROM {$table} AS redirects{$join} WHERE redirects.`status` = 1 AND redirects.`type` = 'redirection'" );
	}

	/**
	 * @param string $table_name Table name without prefix.
	 * @return bool
	 */
	private function table_exists( $table_name ) {
		global $wpdb;

		$table = $wpdb->prefix . $table_name;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = %s', $table ) );

		return intval( $result, 10 ) === 1;
	}

	/**
	 * @param object{match: string, to: string, redirect_code?: string} $redirect Redirect row.
	 * @return array<string, mixed>|false
	 */
	private function get_item_for_redirect( $redirect ) {
		return $this->mapper->fake_redirection( $redirect );
	}

	/**
	 * @return ImporterInfo|false
	 */
	public function get_data() {
		global $wpdb;

		if ( ! $this->table_exists( 'irrp_redirections' ) ) {
			return false;
		}

		$table = $wpdb->prefix . 'irrp_redirections';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE `status` = 1 AND `type` = 'redirection'" );

		if ( $total !== null && intval( $total, 10 ) > 0 ) {
			return array(
				'id' => 'fake-redirection',
				'name' => 'Fake Redirection',
				'description' => __( 'Redirects stored by Redirect Redirection.', 'redirection' ),
				'source' => __( 'Database tables', 'redirection' ),
				'total' => intval( $total, 10 ),
			);
		}

		return false;
	}
}
