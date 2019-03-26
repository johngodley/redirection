<?php

abstract class Red_Database_Upgrader {
	private $queries = [];
	private $live = true;

	/**
	 * Return an array of all the stages for an upgrade
	 *
	 * @return array stage name => reason
	 */
	abstract public function get_stages();

	public function get_reason( $stage ) {
		$stages = $this->get_stages();

		if ( isset( $stages[ $stage ] ) ) {
			return $stages[ $stage ];
		}

		return 'Unknown';
	}

	/**
	 * Run a particular stage on the current upgrader
	 *
	 * @return Red_Database_Status
	 */
	public function perform_stage( Red_Database_Status $status ) {
		global $wpdb;

		$stage = $status->get_current_stage();
		if ( $this->has_stage( $stage ) && method_exists( $this, $stage ) ) {
			try {
				$this->$stage( $wpdb );
				$status->set_ok( $this->get_reason( $stage ) );
			} catch ( Exception $e ) {
				$status->set_error( $e->getMessage() );
			}
		} else {
			$status->set_error( 'No stage found for upgrade ' . $stage );
		}
	}

	public function get_queries_for_stage( $stage ) {
		global $wpdb;

		$this->queries = [];
		$this->live = false;
		$this->$stage( $wpdb );
		$this->live = true;

		return $this->queries;
	}

	/**
	 * Returns the current database charset
	 *
	 * @return string Database charset
	 */
	public function get_charset() {
		global $wpdb;

		$charset_collate = '';
		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}

		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE=$wpdb->collate";
		}

		return $charset_collate;
	}

	/**
	 * Performs a $wpdb->query, and throws an exception if an error occurs
	 *
	 * @return bool true if query is performed ok, otherwise an exception is thrown
	 */
	protected function do_query( $wpdb, $sql ) {
		if ( ! $this->live ) {
			$this->queries[] = $sql;
			return true;
		}

		// These are known queries without user input
		// phpcs:ignore
		$result = $wpdb->query( $sql );

		if ( $result === false ) {
			/* translators: 1: SQL string */
			throw new Exception( sprintf( __( 'Failed to perform query "%s"' ), $sql ) );
		}

		return true;
	}

	/**
	 * Load a database upgrader class
	 *
	 * @return object Database upgrader
	 */
	public static function get( $version ) {
		include_once dirname( __FILE__ ) . '/schema/' . str_replace( [ '..', '/' ], '', $version['file'] );

		return new $version['class'];
	}

	private function has_stage( $stage ) {
		return in_array( $stage, array_keys( $this->get_stages() ), true );
	}
}
