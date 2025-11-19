<?php

/**
 * @phpstan-type HeaderMap array{
 *    name?: string,
 *    value?: string,
 *    regex?: bool,
 *    url_from?: string,
 *    url_notfrom?: string
 * }
 * @phpstan-type HeaderResult array{
 *    name: string,
 *    value: string,
 *    regex: bool,
 *    url_from?: string,
 *    url_notfrom?: string
 * }
 * @phpstan-type HeaderData array{
 *    name: string,
 *    value: string,
 *    regex: bool,
 *    url_from: string,
 *    url_notfrom: string
 * }
 *
 * Check a HTTP request header
 *
 * @phpstan-extends Red_Match<HeaderMap, HeaderResult>
 */
class Header_Match extends Red_Match {
	use FromNotFrom_Match;

	/**
	 * HTTP header name
	 *
	 * @var string
	 */
	public $name = '';

	/**
	 * HTTP header value
	 *
	 * @var string
	 */
	public $value = '';

	/**
	 * Is this a regex?
	 *
	 * @var boolean
	 */
	public $regex = false;

	/**
	 * @return string
	 */
	public function name() {
		return __( 'URL and HTTP header', 'redirection' );
	}

	/**
	 * @param HeaderMap $details
	 * @param bool $no_target_url
	 * @return HeaderResult
	 */
	public function save( array $details, $no_target_url = false ) {
		$data = array(
			'regex' => isset( $details['regex'] ) && $details['regex'] ? true : false,
			'name'  => isset( $details['name'] ) ? $this->sanitize_name( $details['name'] ) : '',
			'value' => isset( $details['value'] ) ? $this->sanitize_value( $details['value'] ) : '',
		);

		$result = $this->save_data( $details, $no_target_url, $data );
		return $result; // @phpstan-ignore-line
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public function sanitize_name( $name ) {
		$name = $this->sanitize_url( sanitize_text_field( $name ) );
		$name = str_replace( ' ', '', $name );
		$name = (string) preg_replace( '/[^A-Za-z0-9\-_]/', '', $name );

		return trim( trim( $name, ':' ) );
	}

	/**
	 * @param string $value
	 * @return string
	 */
	public function sanitize_value( $value ) {
		return $this->sanitize_url( sanitize_text_field( $value ) );
	}

	/**
	 * @param string $url
	 * @return boolean
	 */
	public function is_match( $url ) {
		if ( $this->regex ) {
			$regex = new Red_Regex( $this->value, true );
			$header = Redirection_Request::get_header( $this->name );

			if ( ! is_string( $header ) ) {
				return false;
			}

			return $regex->is_match( $header );
		}

		return Redirection_Request::get_header( $this->name ) === $this->value;
	}

	/**
	 * @return HeaderData
	 */
	public function get_data() {
		return array_merge(
			array(
				'regex' => $this->regex,
				'name' => $this->name,
				'value' => $this->value,
			),
			$this->get_from_data()
		);
	}

	/**
	 * Load the match data into this instance.
	 *
	 * @param string|array<string, mixed> $values Match values, as read from the database (plain text, serialized PHP, or parsed array).
	 * @return void
	 */
	public function load( $values ) {
		$data = $this->load_data( $values );
		$this->regex = isset( $data['regex'] ) ? (bool) $data['regex'] : false; // @phpstan-ignore-line
		$this->name = isset( $data['name'] ) ? (string) $data['name'] : ''; // @phpstan-ignore-line
		$this->value = isset( $data['value'] ) ? (string) $data['value'] : ''; // @phpstan-ignore-line
	}
}
