<?php

namespace Redirection\Compare;

use Redirection\Request\Request;

/**
 * @phpstan-type ServerMap array{
 *    server?: string,
 *    url_from?: string,
 *    url_notfrom?: string
 * }
 * @phpstan-type ServerResult array{
 *    server: string,
 *    url_from?: string,
 *    url_notfrom?: string
 * }
 * @phpstan-type ServerData array{
 *    server: string,
 *    url_from: string,
 *    url_notfrom: string
 * }
 *
 * Match the server URL. Used to match requests for another domain.
 *
 * @phpstan-extends Compare<ServerMap, ServerResult>
 */
class Server extends Compare {
	use FromNotFrom;

	/**
	 * Server URL.
	 *
	 * @var string
	 */
	public $server = '';

	/**
	 * @return string
	 */
	public function name() {
		return __( 'URL and server', 'redirection' );
	}

	/**
	 * @param array $details Match details.
	 * @param bool  $no_target_url Whether the action has a target URL.
	 * @phpstan-param ServerMap $details
	 * @return ServerResult
	 */
	public function save( array $details, $no_target_url = false ) {
		$data = [ 'server' => isset( $details['server'] ) ? $this->sanitize_server( $details['server'] ) : '' ];

		$result = $this->save_data( $details, $no_target_url, $data );
		return $result; // @phpstan-ignore-line
	}

	/**
	 * @param string $server
	 * @return string
	 */
	private function sanitize_server( $server ) {
		if ( strpos( $server, 'http' ) === false ) {
			$server = ( is_ssl() ? 'https://' : 'http://' ) . $server;
		}

		$parts = wp_parse_url( $server );

		if ( isset( $parts['host'] ) && isset( $parts['scheme'] ) ) {
			return $parts['scheme'] . '://' . $parts['host'];
		}

		return '';
	}

	/**
	 * @param string $url
	 * @return boolean
	 */
	public function is_match( $url ) {
		$server = wp_parse_url( $this->server, PHP_URL_HOST );

		return $server === Request::get_server_name();
	}

	/**
	 * @return ServerData
	 */
	public function get_data() {
		return array_merge(
			[
				'server' => $this->server,
			],
			$this->get_from_data()
		);
	}

	/**
	 * Load the match data into this instance.
	 *
	 * @param string|ServerMap $values Match values, as read from the database (plain text, serialized PHP, or parsed array).
	 * @return void
	 */
	public function load( $values ) {
		$data = $this->load_data( $values );
		$this->server = $data['server'] ?? ''; // @phpstan-ignore-line
	}
}
