<?php

class Role_Match extends Red_Match {
	public $role;
	public $url_from;
	public $url_notfrom;

	function name() {
		return __( 'URL and role/capability', 'redirection' );
	}

	public function save( array $details, $no_target_url = false ) {
		$data = array( 'role' => isset( $details['role'] ) ? $details['role'] : '' );

		if ( $no_target_url === false ) {
			$data['url_from'] = isset( $details['url_from'] ) ? $this->sanitize_url( $details['url_from'] ) : '';
			$data['url_notfrom'] = isset( $details['url_notfrom'] ) ? $this->sanitize_url( $details['url_notfrom'] ) : '';
		}

		return $data;
	}

	function get_target( $url, $matched_url, $regex ) {
		// Check if referrer matches
		$matched = current_user_can( $this->role );

		$target = false;
		if ( $this->url_from !== '' && $matched ) {
			$target = $this->url_from;
		} elseif ( $this->url_notfrom !== '' && ! $matched ) {
			$target = $this->url_notfrom;
		}

		return $target;
	}

	public function get_data() {
		return array(
			'url_from' => $this->url_from,
			'url_notfrom' => $this->url_notfrom,
			'role' => $this->role,
		);
	}

	public function load( $values ) {
		$values = unserialize( $values );

		if ( isset( $values['url_from'] ) ) {
			$this->url_from = $values['url_from'];
			$this->url_notfrom = $values['url_notfrom'];
		}

		$this->role = $values['role'];
	}
}
