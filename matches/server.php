<?php

/**
 * Match the server URL. Used to match requests for another domain.
 */
class Server_Match extends Red_Match {
	use FromNotFrom_Match;

	/**
	 * Server URL.
	 *
	 * @var String
	 */
	public $server = '';

	public function name() {
		return __( 'URL and server', 'redirection' );
	}

	public function save( array $details, $no_target_url = false ) {
		$data = array( 'server' => isset( $details['server'] ) ? $this->sanitize_server( $details['server'] ) : '' );

		return $this->save_data( $details, $no_target_url, $data );
	}

	private function sanitize_server( $server ) {
		if ( strpos( $server, 'http' ) === false ) {
			$server = ( is_ssl() ? 'https://' : 'http://' ) . $server;
		}

		$parts = wp_parse_url( $server );

		if ( isset( $parts['host'] ) ) {
			return $parts['scheme'] . '://' . $parts['host'];
		}

		return '';
	}

	public function is_match( $url ) {
		$server = wp_parse_url( $this->server, PHP_URL_HOST );

		return $server === Redirection_Request::get_server_name();
	}

	public function get_data() {
		return array_merge( array(
			'server' => $this->server,
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
		$this->server = isset( $values['server'] ) ? $values['server'] : '';
	}
}
