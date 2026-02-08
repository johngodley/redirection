<?php

/**
 * @phpstan-type RoleMap array{
 *    role?: string,
 *    url_from?: string,
 *    url_notfrom?: string
 * }
 * @phpstan-type RoleResult array{
 *    role: string,
 *    url_from?: string,
 *    url_notfrom?: string
 * }
 * @phpstan-type RoleData array{
 *    role: string,
 *    url_from: string,
 *    url_notfrom: string
 * }
 *
 * Match a particular role or capability
 *
 * @phpstan-extends Red_Match<RoleMap, RoleResult>
 */
class Role_Match extends Red_Match {
	use FromNotFrom_Match;

	/**
	 * WordPress role or capability
	 *
	 * @var string
	 */
	public $role = '';

	public function name() {
		return __( 'URL and role/capability', 'redirection' );
	}

	/**
	 * @param RoleMap $details
	 * @return RoleResult
	 */
	public function save( array $details, $no_target_url = false ) {
		$data = array( 'role' => isset( $details['role'] ) ? $details['role'] : '' );

		/** @var RoleResult $result */
		$result = $this->save_data( $details, $no_target_url, $data );
		return $result;
	}

	public function is_match( $url ) {
		return current_user_can( $this->role );
	}

	/**
	 * @return RoleData
	 */
	public function get_data() {
		return array_merge(
			array(
				'role' => $this->role,
			),
			$this->get_from_data()
		);
	}

	/**
	 * Load the match data into this instance.
	 *
	 * @param string|RoleMap $values Match values, as read from the database (plain text, serialized PHP, or parsed array).
	 * @return void
	 */
	public function load( $values ) {
		$values = $this->load_data( $values );
		/** @var RoleMap $values */
		$this->role = isset( $values['role'] ) ? $values['role'] : '';
	}
}
