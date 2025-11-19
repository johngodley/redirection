<?php

/**
 * @phpstan-type IpMap array{
 *    ip?: string[],
 *    url_from?: string,
 *    url_notfrom?: string
 * }
 * @phpstan-type IpResult array{
 *    ip: string[],
 *    url_from?: string,
 *    url_notfrom?: string
 * }
 * @phpstan-type IpData array{
 *    ip: string[],
 *    url_from: string,
 *    url_notfrom: string
 * }
 *
 * Check the request IP
 *
 * @phpstan-extends Red_Match<IpMap, IpResult>
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

	/**
	 * @param IpMap $details
	 * @return IpResult
	 */
	public function save( array $details, $no_target_url = false ) {
		$data = array( 'ip' => isset( $details['ip'] ) && is_array( $details['ip'] ) ? $this->sanitize_ips( $details['ip'] ) : [] ); // @phpstan-ignore-line

		$result = $this->save_data( $details, $no_target_url, $data );
		return $result; // @phpstan-ignore-line
	}

	/**
	 * Sanitize a single IP
	 *
	 * @param string $ip IP.
	 * @return string|false
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
	 * @param string $match_ip IP to match.
	 * @return string[]
	 */
	private function get_matching_ips( $match_ip ) {
		$current_ip = @inet_pton( $match_ip );

		return array_filter(
			$this->ip,
			function ( $ip ) use ( $current_ip ) {
				return @inet_pton( $ip ) === $current_ip;
			}
		);
	}

	public function is_match( $url ) {
		$matched = $this->get_matching_ips( Redirection_Request::get_ip() );

		return count( $matched ) > 0;
	}

	/**
	 * @return IpData
	 */
	public function get_data() {
		return array_merge(
			array(
				'ip' => $this->ip,
			),
			$this->get_from_data()
		);
	}

	/**
	 * Load the match data into this instance.
	 *
	 * @param string|IpMap $values Match values, as read from the database (plain text, serialized PHP, or parsed array).
	 * @return void
	 */
	public function load( $values ) {
		$data = $this->load_data( $values );
		$this->ip = isset( $data['ip'] ) ? $data['ip'] : []; // @phpstan-ignore-line
	}
}
