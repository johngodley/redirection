<?php

namespace Redirection\Compare;

use Redirection\Core\Regex;
use Redirection\Request\Request;

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
 * @phpstan-extends Compare<ReferrerMap, ReferrerResult>
 */
class Referrer extends Compare {
	use FromNotFrom;

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
	 * @param array $details Match details.
	 * @param bool  $no_target_url Whether the action has a target URL.
	 * @phpstan-param ReferrerMap $details
	 * @return ReferrerResult
	 */
	public function save( array $details, $no_target_url = false ) {
		$data = [
			'regex'    => isset( $details['regex'] ) && $details['regex'] ? true : false,
			'referrer' => isset( $details['referrer'] ) ? $this->sanitize_referrer( $details['referrer'] ) : '',
		];

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
			$regex = new Regex( $this->referrer, true );
			return $regex->is_match( Request::get_referrer() );
		}

		return Request::get_referrer() === $this->referrer;
	}

	/**
	 * @return ReferrerData
	 */
	public function get_data() {
		return array_merge(
			[
				'regex' => $this->regex,
				'referrer' => $this->referrer,
			],
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
		$this->regex = $data['regex'] ?? false; // @phpstan-ignore-line
		$this->referrer = $data['referrer'] ?? ''; // @phpstan-ignore-line
	}
}
