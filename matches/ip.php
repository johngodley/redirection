<?php

class IP_Match extends Red_Match {
	use FromNotFrom_Match;

	public $ip = [];

	public function name() {
		return __( 'URL and IP', 'redirection' );
	}

	public function save( array $details, $no_target_url = false ) {
		$data = array( 'ip' => isset( $details['ip'] ) && is_array( $details['ip'] ) ? $this->sanitize_ips( $details['ip'] ) : [] );

		return $this->save_data( $details, $no_target_url, $data );
	}

	private function sanitize_single_ip( $ip ) {
		$ip = @inet_pton( trim( $ip ) );
		if ( $ip !== false ) {
			return @inet_ntop( $ip );  // Convert back to string
		}

		return false;
	}

	private function sanitize_ips( $ips ) {
		if ( is_array( $ips ) ) {
			$ips = array_map( array( $this, 'sanitize_single_ip' ), $ips );
			return array_values( array_filter( array_unique( $ips ) ) );
		}

		return array();
	}

	private function get_matching_ips( $match_ip ) {
		$current_ip = @inet_pton( $match_ip );

		return array_filter( $this->ip, function( $ip ) use ( $current_ip ) {
			return @inet_pton( $ip ) === $current_ip;
		} );
	}

	public function is_match( $url ) {
		$matched = $this->get_matching_ips( Redirection_Request::get_ip() );

		return count( $matched ) > 0;
	}

	public function get_data() {
		return array_merge( array(
			'ip' => $this->ip,
		), $this->get_from_data() );
	}

	public function load( $values ) {
		$values = $this->load_data( $values );
		$this->ip = isset( $values['ip'] ) ? $values['ip'] : [];
	}
}
