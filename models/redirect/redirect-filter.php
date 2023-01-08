<?php

/**
 * Filter the redirects
 */
class Red_Item_Filters {
	/**
	 * List of filters
	 *
	 * @var array
	 */
	private $filters = [];

	/**
	 * Constructor
	 *
	 * @param Array $filter_params Filters.
	 */
	public function __construct( $filter_params ) {
		global $wpdb;

		foreach ( $filter_params as $filter_by => $filter ) {
			$filter = trim( sanitize_text_field( $filter ) );
			$filter_by = sanitize_text_field( $filter_by );

			if ( $filter_by === 'status' ) {
				if ( $filter === 'enabled' ) {
					$this->filters[] = "status='enabled'";
				} else {
					$this->filters[] = "status='disabled'";
				}
			} elseif ( $filter_by === 'url-match' ) {
				if ( $filter === 'regular' ) {
					$this->filters[] = 'regex=1';
				} else {
					$this->filters[] = 'regex=0';
				}
			} elseif ( $filter_by === 'id' ) {
				$this->filters[] = $wpdb->prepare( 'id=%d', intval( $filter, 10 ) );
			} elseif ( $filter_by === 'match' && in_array( $filter, array_keys( Red_Match::available() ), true ) ) {
				$this->filters[] = $wpdb->prepare( 'match_type=%s', $filter );
			} elseif ( $filter_by === 'action' && in_array( $filter, array_keys( Red_Action::available() ), true ) ) {
				$this->filters[] = $wpdb->prepare( 'action_type=%s', $filter );
			} elseif ( $filter_by === 'http' ) {
				$sanitizer = new Red_Item_Sanitize();
				$filter = intval( $filter, 10 );

				if ( $sanitizer->is_valid_error_code( $filter ) || $sanitizer->is_valid_redirect_code( $filter ) ) {
					$this->filters[] = $wpdb->prepare( 'action_code=%d', $filter );
				}
			} elseif ( $filter_by === 'access' ) {
				if ( $filter === 'year' ) {
					$this->filters[] = 'last_access < DATE_SUB(NOW(),INTERVAL 1 YEAR)';
				} elseif ( $filter === 'month' ) {
					$this->filters[] = 'last_access < DATE_SUB(NOW(),INTERVAL 1 MONTH)';
				} else {
					$this->filters[] = "( last_access < '1970-01-01 00:00:01' )";
				}
			} elseif ( $filter_by === 'url' ) {
				$this->filters[] = $wpdb->prepare( 'url LIKE %s', '%' . $wpdb->esc_like( $filter ) . '%' );
			} elseif ( $filter_by === 'target' ) {
				$this->filters[] = $wpdb->prepare( 'action_data LIKE %s', '%' . $wpdb->esc_like( $filter ) . '%' );
			} elseif ( $filter_by === 'title' ) {
				$this->filters[] = $wpdb->prepare( 'title LIKE %s', '%' . $wpdb->esc_like( $filter ) . '%' );
			} elseif ( $filter_by === 'group' ) {
				$this->filters[] = $wpdb->prepare( 'group_id=%d', intval( $filter, 10 ) );
			}
		}
	}

	/**
	 * Get the filters as sanitized SQL.
	 *
	 * @return string
	 */
	public function get_as_sql() {
		if ( count( $this->filters ) > 0 ) {
			return 'WHERE ' . implode( ' AND ', $this->filters );
		}

		return '';
	}
}
