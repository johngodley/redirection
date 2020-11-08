<?php

/**
 * Match a particular role or capability
 */
class Role_Match extends Red_Match {
	use FromNotFrom_Match;

	/**
	 * WordPress role or capability
	 *
	 * @var String
	 */
	public $role = '';

	public function name() {
		return __( 'URL and role/capability', 'redirection' );
	}

	public function save( array $details, $no_target_url = false ) {
		$data = array( 'role' => isset( $details['role'] ) ? $details['role'] : '' );

		return $this->save_data( $details, $no_target_url, $data );
	}

	public function is_match( $url ) {
		return current_user_can( $this->role );
	}

	public function get_data() {
		return array_merge( array(
			'role' => $this->role,
		), $this->get_from_data() );
	}

	/**
	 * Load the match data into this instance.
	 *
	 * @param String $values Match values, as read from the database (plain text or serialized PHP).
	 * @return void
	 */
	public function load( $values ) {
		$values = $this->load_data( $values );
		$this->role = isset( $values['role'] ) ? $values['role'] : '';
	}
}
