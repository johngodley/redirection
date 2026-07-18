<?php

namespace Redirection\Compare;

use Redirection\Core\Regex;

require_once __DIR__ . '/trait-fromnotfrom.php';
require_once __DIR__ . '/trait-fromurl.php';

/**
 * Matches a URL and some other condition
 *
 * @phpstan-template TSaveDetails of array<string, mixed>
 * @phpstan-template-covariant TSaveResult of (array<string, mixed>|string)
 *
 * @phpstan-type RedMatchUrlData array{
 *     url: string
 * }
 * @phpstan-type RedMatchAgentData array{
 *     agent: string,
 *     regex: bool,
 *     url_from: string,
 *     url_notfrom: string
 * }
 * @phpstan-type RedMatchReferrerData array{
 *     referrer: string,
 *     regex: bool,
 *     url_from: string,
 *     url_notfrom: string
 * }
 * @phpstan-type RedMatchHeaderData array{
 *     name: string,
 *     value: string,
 *     regex: bool,
 *     url_from: string,
 *     url_notfrom: string
 * }
 * @phpstan-type RedMatchCookieData array{
 *     name: string,
 *     value: string,
 *     regex: bool,
 *     url_from: string,
 *     url_notfrom: string
 * }
 * @phpstan-type RedMatchCustomData array{
 *     filter: string,
 *     url_from: string,
 *     url_notfrom: string
 * }
 * @phpstan-type RedMatchRoleData array{
 *     role: string,
 *     url_from: string,
 *     url_notfrom: string
 * }
 * @phpstan-type RedMatchServerData array{
 *     server: string,
 *     url_from: string,
 *     url_notfrom: string
 * }
 * @phpstan-type RedMatchIpData array{
 *     ip: string[],
 *     url_from: string,
 *     url_notfrom: string
 * }
 * @phpstan-type RedMatchPageData array{
 *     page: string,
 *     url: string
 * }
 * @phpstan-type RedMatchLanguageData array{
 *     language: string,
 *     url_from: string,
 *     url_notfrom: string
 * }
 * @phpstan-type RedMatchLoginData array{
 *     logged_in: string,
 *     logged_out: string
 * }
 * @phpstan-type RedMatchData RedMatchUrlData|RedMatchAgentData|RedMatchReferrerData|RedMatchHeaderData|RedMatchCookieData|RedMatchCustomData|RedMatchRoleData|RedMatchServerData|RedMatchIpData|RedMatchPageData|RedMatchLanguageData|RedMatchLoginData
 */
abstract class Compare {
	/**
	 * Match type
	 *
	 * @var string
	 */
	protected $type = '';

	/**
	 * Constructor
	 *
	 * @param RedMatchData|string $values Initial values.
	 */
	public function __construct( $values = '' ) {
		if ( $values !== '' ) {
			$this->load( $values );
		}
	}

	/**
	 * Get match type
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Set match type
	 *
	 * @param string $type Match type.
	 * @return void
	 */
	public function set_type( $type ) {
		$this->type = $type;
	}

	/**
	 * Save the match
	 *
	 * @phpstan-param TSaveDetails $details Details to save.
	 * @param array<string, mixed> $details Details to save.
	 * @param boolean $no_target_url The URL when no target.
	 * @phpstan-return TSaveResult|null
	 * @return array<string, mixed>|string|null
	 */
	abstract public function save( array $details, $no_target_url = false );

	/**
	 * Get the match name
	 *
	 * @return string
	 */
	abstract public function name();

	/**
	 * Match the URL against the specific matcher conditions
	 *
	 * @param string $url Requested URL.
	 * @return boolean
	 */
	abstract public function is_match( $url );

	/**
	 * Get the target URL for this match. Some matches may have a matched/unmatched target.
	 *
	 * @param string           $original_url The client URL (not decoded).
	 * @param string           $matched_url The URL in the redirect.
	 * @param \Redirection\Url\SourceFlags $flag Source flags.
	 * @param boolean          $is_matched Was the match successful.
	 * @return string|false
	 */
	abstract public function get_target_url( $original_url, $matched_url, \Redirection\Url\SourceFlags $flag, $is_matched );

	/**
	 * Get the match data
	 *
	 * @return RedMatchData|null
	 */
	abstract public function get_data();

	/**
	 * Load the match data into this instance.
	 *
	 * @param RedMatchData|string $values Match values, as read from the database (plain text or serialized PHP).
	 * @return void
	 */
	abstract public function load( $values );

	/**
	 * Sanitize a match URL
	 *
	 * @param string $url URL.
	 * @return string
	 */
	public function sanitize_url( $url ) {
		// No new lines
		$url = (string) preg_replace( "/[\r\n\t].*?$/s", '', $url );

		// Clean control codes
		$url = (string) preg_replace( '/[^\PC\s]/u', '', $url );

		return $url;
	}

	/**
	 * Apply a regular expression to the target URL, replacing any values.
	 *
	 * @param string           $source_url Redirect source URL.
	 * @param string           $target_url Target URL.
	 * @param string           $requested_url The URL being requested (decoded).
	 * @param \Redirection\Url\SourceFlags $flags Source URL flags.
	 * @return string
	 */
	protected function get_target_regex_url( $source_url, $target_url, $requested_url, \Redirection\Url\SourceFlags $flags ) {
		$regex = new Regex( $source_url, $flags->is_ignore_case() );

		return $regex->replace( $target_url, $requested_url );
	}

	/**
	 * Create a Compare object, given a type
	 *
	 * @param string $name Match type.
	 * @param RedMatchData|string $data Match data.
	 * @return Compare<array<string, mixed>, (array<string, mixed>|string)>|null
	 */
	public static function create( $name, $data = '' ) {
		switch ( strtolower( $name ) ) {
			case 'url':
				$class = new Url( $data );
				break;
			case 'referrer':
				$class = new Referrer( $data );
				break;
			case 'agent':
				$class = new Agent( $data );
				break;
			case 'login':
				$class = new Login( $data );
				break;
			case 'header':
				$class = new Header( $data );
				break;
			case 'custom':
				$class = new Custom( $data );
				break;
			case 'cookie':
				$class = new Cookie( $data );
				break;
			case 'role':
				$class = new Role( $data );
				break;
			case 'server':
				$class = new Server( $data );
				break;
			case 'ip':
				$class = new Ip( $data );
				break;
			case 'page':
				$class = new Page( $data );
				break;
			case 'language':
				$class = new Language( $data );
				break;
			default:
				$class = null;
		}

		if ( $class !== null ) {
			$class->set_type( $name );
		}

		// @phpstan-ignore-next-line
		return $class;
	}

	/**
	 * Get list of available match types
	 *
	 * @return string[]
	 */
	public static function types() {
		return [ 'url', 'referrer', 'agent', 'login', 'header', 'custom', 'cookie', 'role', 'server', 'ip', 'page', 'language' ];
	}

	/**
	 * Get all Compare objects
	 *
	 * @return array<string, string>
	 */
	public static function all() {
		$data = [];

		foreach ( self::types() as $name ) {
			$obj = self::create( $name );
			if ( $obj !== null ) {
				$data[ $name ] = $obj->name();
			}
		}

		return $data;
	}
}
