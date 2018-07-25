<?php

class Server_Match extends Red_Match {
	use FromNotFrom_Match;

	public $server;

	function name() {
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

		$parts = parse_url( $server );

		if ( isset( $parts['host'] ) ) {
			return $parts['scheme'] . '://' . $parts['host'];
		}

		return '';
	}

	function get_target( $url, $matched_url, $regex ) {
		$server = parse_url( $this->server, PHP_URL_HOST );

		$matched = false;
		if ( $server === Redirection_Request::get_server_name() ) {
			$matched = true;
		}

		return $this->get_matched_target( $matched );
	}

	public function get_data() {
		return array_merge( array(
			'server' => $this->server,
		), $this->get_from_data() );
	}

	public function load( $values ) {
		$values = $this->load_data( $values );
		$this->server = $values['server'];
	}
}
