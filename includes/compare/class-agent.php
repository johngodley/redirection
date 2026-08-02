<?php

namespace Redirection\Compare;

use Redirection\Core\Regex;
use Redirection\Request\Request;

/**
 * @phpstan-type AgentMap array{
 *    regex?: bool,
 *    agent?: string,
 *    url_from?: string,
 *    url_notfrom?: string
 * }
 * @phpstan-type AgentResult array{
 *    regex: bool,
 *    agent: string,
 *    url_from?: string,
 *    url_notfrom?: string
 * }
 * @phpstan-type AgentData array{
 *    regex: bool,
 *    agent: string,
 *    url_from: string,
 *    url_notfrom: string
 * }
 *
 * Match the user agent
 *
 * @phpstan-extends Compare<AgentMap, AgentResult>
 */
class Agent extends Compare {
	use FromNotFrom;

	/**
	 * User agent.
	 *
	 * @var string
	 */
	public $agent = '';

	/**
	 * Is this a regex match?
	 *
	 * @var boolean
	 */
	public $regex = false;

	public function name() {
		return __( 'URL and user agent', 'redirection' );
	}

	/**
	 * @phpstan-param AgentMap $details
	 * @param array<string, mixed> $details Match details.
	 * @param bool $no_target_url Does the action have a target URL.
	 * @return AgentResult
	 */
	public function save( array $details, $no_target_url = false ) {
		$data = [
			'regex' => isset( $details['regex'] ) && $details['regex'] ? true : false,
			'agent' => isset( $details['agent'] ) ? $this->sanitize_agent( $details['agent'] ) : '',
		];

		$result = $this->save_data( $details, $no_target_url, $data );
		return $result; // @phpstan-ignore-line
	}

	/**
	 * @param string $agent User agent string.
	 * @return string
	 */
	private function sanitize_agent( $agent ) {
		return $this->sanitize_url( $agent );
	}

	public function is_match( $url ) {
		if ( $this->regex ) {
			$regex = new Regex( $this->agent, true );
			return $regex->is_match( Request::get_user_agent() );
		}

		return $this->agent === Request::get_user_agent();
	}

	/**
	 * @return AgentData
	 */
	public function get_data() {
		return array_merge(
			[
				'regex' => $this->regex,
				'agent' => $this->agent,
			],
			$this->get_from_data()
		);
	}

	/**
	 * Load the match data into this instance.
	 *
	 * @param string|AgentMap $values Match values, as read from the database (plain text, serialized PHP, or parsed array).
	 * @return void
	 */
	public function load( $values ) {
		$data = $this->load_data( $values );
		$this->regex = $data['regex'] ?? false; // @phpstan-ignore-line
		$this->agent = $data['agent'] ?? ''; // @phpstan-ignore-line
	}
}
