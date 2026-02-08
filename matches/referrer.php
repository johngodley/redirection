<?php

/**
 * @phpstan-type ReferrerMap array{
 *    regex?: bool,
 *    referrer?: string,
 *    url_from?: string,
 *    url_notfrom?: string
 * }
 * @phpstan-type ReferrerResult array{
 *    regex: bool,
 *    referrer: string,
 *    url_from?: string,
 *    url_notfrom?: string
 * }
 * @phpstan-type ReferrerData array{
 *    regex: bool,
 *    referrer: string,
 *    url_from: string,
 *    url_notfrom: string
 * }
 *
 * Match the referrer
 *
 * @phpstan-extends Red_Match<ReferrerMap, ReferrerResult>
 */
class Referrer_Match extends Red_Match {
	use FromNotFrom_Match;

	/**
	 * Referrer
	 *
	 * @var string
	 */
	public $referrer = '';

	/**
	 * Regex match?
	 *
	 * @var boolean
	 */
	public $regex = false;

	public function name() {
		return __( 'URL and referrer', 'redirection' );
	}

	/**
	 * @param ReferrerMap $details
	 * @return ReferrerResult
	 */
	public function save( array $details, $no_target_url = false ) {
		$data = array(
			'regex'    => isset( $details['regex'] ) && $details['regex'] ? true : false,
			'referrer' => isset( $details['referrer'] ) ? $this->sanitize_referrer( $details['referrer'] ) : '',
		);

		$result = $this->save_data( $details, $no_target_url, $data );
		return $result; // @phpstan-ignore-line
	}

	/**
	 * @param string $agent
	 * @return string
	 */
	public function sanitize_referrer( $agent ) {
		return $this->sanitize_url( $agent );
	}

	public function is_match( $url ) {
		if ( $this->regex ) {
			$regex = new Red_Regex( $this->referrer, true );
			return $regex->is_match( Redirection_Request::get_referrer() );
		}

		return Redirection_Request::get_referrer() === $this->referrer;
	}

	/**
	 * @return ReferrerData
	 */
	public function get_data() {
		return array_merge(
			array(
				'regex' => $this->regex,
				'referrer' => $this->referrer,
			),
			$this->get_from_data()
		);
	}

	/**
	 * Load the match data into this instance.
	 *
	 * @param string|ReferrerMap $values Match values, as read from the database (plain text, serialized PHP, or parsed array).
	 * @return void
	 */
	public function load( $values ) {
		$data = $this->load_data( $values );
		$this->regex = isset( $data['regex'] ) ? $data['regex'] : false; // @phpstan-ignore-line
		$this->referrer = isset( $data['referrer'] ) ? $data['referrer'] : ''; // @phpstan-ignore-line
	}
}
