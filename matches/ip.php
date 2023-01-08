<?php

/**
 * Check the request IP
 */
class IP_Match extends Red_Match {
	use FromNotFrom_Match;

	/**
	 * Array of IP addresses
	 *
	 * @var string[]
	 */
	public $ip = [];

	public function name() {
		return __( 'URL and IP', 'redirection' );
	}

	public function save( array $details, $no_target_url = false ) {
		$data = array( 'ip' => isset( $details['ip'] ) && is_array( $details['ip'] ) ? $this->sanitize_ips( $details['ip'] ) : [] );

		return $this->save_data( $details, $no_target_url, $data );
	}

	/**
	 * Sanitize a single IP
	 *
	 * @param String $ip IP.
	 * @return String|false
	 */
	private function sanitize_single_ip( $ip ) {
		$ip = @inet_pton( trim( sanitize_text_field( $ip ) ) );
		if ( $ip !== false ) {
			return @inet_ntop( $ip );  // Convert back to string
		}

		return false;
	}

	/**
	 * Sanitize a list of IPs
	 *
	 * @param string[] $ips List of IPs.
	 * @return string[]
	 */
	private function sanitize_ips( array $ips ) {
		$ips = array_map( array( $this, 'sanitize_single_ip' ), $ips );
		return array_values( array_filter( array_unique( $ips ) ) );
	}

	/**
	 * Get a list of IPs that match.
	 *
	 * @param String $match_ip IP to match.
	 * @return string[]
	 */
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
