<?php

require_once __DIR__ . '/database-upgrade.php';

abstract class Red_Database_Upgrader {
	/**
	 * @var list<string>
	 */
	private $queries = [];

	/**
	 * @var bool
	 */
	private $live = true;

	/**
	 * Return an array of all the stages for an upgrade
	 *
	 * @return array<string, string> stage name => reason
	 */
	abstract public function get_stages();

	/**
	 * @param string $stage
	 * @return string
	 */
	public function get_reason( string $stage ): string {
		$stages = $this->get_stages();

		if ( isset( $stages[ $stage ] ) ) {
			return $stages[ $stage ];
		}

		return 'Unknown';
	}

	/**
	 * Run a particular stage on the current upgrader
	 *
	 * @param Red_Database_Status $status
	 * @return void
	 */
	public function perform_stage( Red_Database_Status $status ): void {
		global $wpdb;

		$stage = $status->get_current_stage();
		if ( is_string( $stage ) && $this->has_stage( $stage ) && method_exists( $this, $stage ) ) {
			try {
				$this->invoke_stage( $stage, $wpdb, true );
				$status->set_ok( $this->get_reason( $stage ) );
			} catch ( Exception $e ) {
				$status->set_error( $e->getMessage() );
			}
		} else {
			$status->set_error( 'No stage found for upgrade ' . $stage );
		}
	}

	/**
	 * @param string $stage
	 * @return list<string>
	 */
	public function get_queries_for_stage( string $stage ): array {
		global $wpdb;

		$this->queries = [];
		$this->live = false;
		$this->invoke_stage( $stage, $wpdb, false );
		$this->live = true;

		return $this->queries;
	}

	/**
	 * Returns the current database charset
	 *
	 * @return string Database charset
	 */
	public function get_charset(): string {
		global $wpdb;

		$charset_collate = '';
		if ( ! empty( $wpdb->charset ) ) {
			// Fix some common invalid charset values
			$fixes = [
				'utf-8',
				'utf',
			];

			$charset = $wpdb->charset;
			if ( in_array( strtolower( $charset ), $fixes, true ) ) {
				$charset = 'utf8';
			}

			$charset_collate = "DEFAULT CHARACTER SET $charset";
		}

		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE=$wpdb->collate";
		}

		return $charset_collate;
	}

	/**
	 * Performs a $wpdb->query, and throws an exception if an error occurs
	 *
	 * @param wpdb   $wpdb WordPress database instance.
	 * @param string $sql SQL query.
	 * @return bool true if query is performed ok, otherwise an exception is thrown
	 */
	protected function do_query( wpdb $wpdb, string $sql ): bool {
		if ( ! $this->live ) {
			$this->queries[] = $sql;
			return true;
		}

		// These are known queries without user input
		// phpcs:ignore
		$result = $wpdb->query( $sql );

		if ( $result === false ) {
			/* translators: 1: SQL string */
			throw new Exception( sprintf( 'Failed to perform query "%s"', $sql ) ); // phpcs:ignore
		}

		return true;
	}

	/**
	 * Load a database upgrader class
	 *
	 * @param Red_Database_Upgrade $version
	 * @return Red_Database_Upgrader Database upgrader
	 */
	public static function get( Red_Database_Upgrade $version ): Red_Database_Upgrader {
		include_once __DIR__ . '/schema/' . str_replace( [ '..', '/' ], '', $version->get_file() );

		$class = $version->get_class();

		return new $class();
	}

	/**
	 * @param string $stage
	 * @return bool
	 */
	private function has_stage( string $stage ): bool {
		return in_array( $stage, array_keys( $this->get_stages() ), true );
	}

	/**
	 * @param string $stage
	 * @param wpdb   $wpdb
	 * @param bool   $live
	 * @return void
	 */
	private function invoke_stage( string $stage, wpdb $wpdb, bool $live ): void {
		if ( ! method_exists( $this, $stage ) ) {
			return;
		}

		// Methods that accept both $wpdb and $live parameters
		// Most stage methods only accept $wpdb, but some (like create_groups) accept both
		$two_param_methods = [ 'create_groups' ];

		if ( in_array( $stage, $two_param_methods, true ) ) {
			/** @var callable(wpdb, bool): void $callable */
			$callable = [ $this, $stage ];
			call_user_func( $callable, $wpdb, $live );
		} else {
			/** @var callable(wpdb): void $callable */
			$callable = [ $this, $stage ];
			call_user_func( $callable, $wpdb );
		}
	}
}
