<?php

namespace Redirection\ImportExport\Importer;

/**
 * @phpstan-import-type ImporterInfo from Plugin
 */
class Eps301Redirects extends Plugin {
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
	 * @return list<object{type: string, url_to: string, status: string, url_from: string}>
	 */
	private function get_redirects() {
		global $wpdb;

		if ( ! $this->table_exists() ) {
			return [];
		}

		$table = $wpdb->prefix . 'redirects';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results( "SELECT * FROM {$table} WHERE `status` != 'inactive'" );
	}

	/**
	 * @return bool
	 */
	private function table_exists() {
		global $wpdb;

		$table = $wpdb->prefix . 'redirects';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = %s', $table ) );

		return intval( $result, 10 ) === 1;
	}

	/**
	 * @param object{type: string, url_to: string, status: string, url_from: string} $redirect Redirect row.
	 * @return array<string, mixed>|false
	 */
	private function get_item_for_redirect( $redirect ) {
		$target = $redirect->type === 'post' ? get_permalink( intval( $redirect->url_to, 10 ) ) : $redirect->url_to;
		$code = intval( $redirect->status, 10 );

		return $this->mapper->eps301( $redirect->url_from, $target, $code );
	}

	/**
	 * @return ImporterInfo|false
	 */
	public function get_data() {
		global $wpdb;

		if ( ! $this->table_exists() ) {
			return false;
		}

		$table = $wpdb->prefix . 'redirects';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE `status` != 'inactive'" );

		if ( $total !== null && intval( $total, 10 ) > 0 ) {
			return array(
				'id' => 'eps-301-redirects',
				'name' => '301 Redirects',
				'description' => __( 'Redirects stored by 301 Redirects.', 'redirection' ),
				'source' => __( 'Database tables', 'redirection' ),
				'total' => intval( $total, 10 ),
			);
		}

		return false;
	}
}
