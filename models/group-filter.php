<?php

/**
 * Filter builder for group queries
 */
class Red_Group_Filters {
	/**
	 * SQL filter conditions
	 *
	 * @var string[]
	 */
	private $filters = [];

	/**
	 * Constructor
	 *
	 * @param array<string, string> $filter_params Filter parameters.
	 */
	public function __construct( $filter_params ) {
		global $wpdb;

		foreach ( $filter_params as $filter_by => $filter ) {
			$filter_by = sanitize_text_field( $filter_by );
			$filter = sanitize_text_field( $filter );

			if ( $filter_by === 'status' ) {
				$this->filters[] = $filter === 'enabled' ? "status='enabled'" : "status='disabled'";
			} elseif ( $filter_by === 'module' ) {
				$this->filters[] = $wpdb->prepare( 'module_id=%d', intval( $filter, 10 ) );
			} elseif ( $filter_by === 'name' ) {
				$this->filters[] = $wpdb->prepare( 'name LIKE %s', '%' . $wpdb->esc_like( trim( $filter ) ) . '%' );
			}
		}
	}

	/**
	 * Get filters as SQL WHERE clause
	 *
	 * @return string SQL WHERE clause or empty string.
	 */
	public function get_as_sql() {
		if ( count( $this->filters ) > 0 ) {
			return ' WHERE ' . implode( ' AND ', $this->filters );
		}

		return '';
	}
}
