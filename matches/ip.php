<?php

class IP_Match extends Red_Match {
	use FromNotFrom_Match;

	public $ip;

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
			return array_filter( array_unique( $ips ) );
		}

		return array();
	}

	public function get_target( $url, $matched_url, $regex ) {
		$current_ip = @inet_pton( Redirection_Request::get_ip() );

		$matched = array_filter( $this->ip, function( $ip ) use ( $current_ip ) {
			return @inet_pton( $ip ) === $current_ip;
		} );

		$target = $this->get_matched_target( count( $matched ) > 0 );

		if ( $regex && $target ) {
			return $this->get_target_regex_url( $matched_url, $target, $url );
		}

		return $target;
	}

	public function get_data() {
		return array_merge( array(
			'ip' => $this->ip,
		), $this->get_from_data() );
	}

	public function load( $values ) {
		$values = $this->load_data( $values );
		$this->ip = $values['ip'];
	}
}
