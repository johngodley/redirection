<?php

require_once dirname( __DIR__ ) . '/matches/from-notfrom.php';
require_once dirname( __DIR__ ) . '/matches/from-url.php';

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
abstract class Red_Match {
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
	 * @param Red_Source_Flags $flag Source flags.
	 * @param boolean          $is_matched Was the match successful.
	 * @return string|false
	 */
	abstract public function get_target_url( $original_url, $matched_url, Red_Source_Flags $flag, $is_matched );

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
	 * @param Red_Source_Flags $flags Source URL flags.
	 * @return string
	 */
	protected function get_target_regex_url( $source_url, $target_url, $requested_url, Red_Source_Flags $flags ) {
		$regex = new Red_Regex( $source_url, $flags->is_ignore_case() );

		return $this->keep_target_relative( $target_url, $regex->replace( $target_url, $requested_url ) );
	}

	/**
	 * Stop a regex replacement turning a site relative target into one that points at another site.
	 *
	 * A target of `/$1` is relative to this site. If the captured value itself begins with a slash then
	 * the replaced target becomes `//example.com/path`, which a browser treats as a protocol relative URL
	 * pointing at `example.com`. Collapse the leading separators so the target stays on this site.
	 *
	 * @param string $target_url Target URL, before the replacement.
	 * @param string $replaced Target URL, after the replacement.
	 * @return string
	 */
	private function keep_target_relative( $target_url, $replaced ) {
		// Only applies to a target the user has written as relative to this site
		if ( substr( $target_url, 0, 1 ) !== '/' || substr( $target_url, 0, 2 ) === '//' ) {
			return $replaced;
		}

		// A browser treats a backslash as a slash, and both WordPress and the browser remove control
		// characters, so check the target as it will finally be seen
		$check = (string) preg_replace( '/[\x00-\x20\x7F]/', '', str_replace( '\\', '/', $replaced ) );

		if ( substr( $check, 0, 2 ) !== '//' ) {
			return $replaced;
		}

		return (string) preg_replace( '@^[/\\\\\x00-\x20\x7F]+@', '/', $replaced );
	}

	/**
	 * Create a Red_Match object, given a type
	 *
	 * @param string $name Match type.
	 * @param RedMatchData|string $data Match data.
	 * @return Red_Match<array<string, mixed>, (array<string, mixed>|string)>|null
	 */
	public static function create( $name, $data = '' ) {
		$avail = self::available();
		if ( isset( $avail[ strtolower( $name ) ] ) ) {
			$classname = $name . '_match';

			/** @var class-string<Red_Match<array<string, mixed>, (array<string, mixed>|string)>> $classname */
			if ( ! class_exists( strtolower( $classname ) ) ) {
				include __DIR__ . '/../matches/' . $avail[ strtolower( $name ) ];
			}

			/**
			 * @var Red_Match<array<string, mixed>, (array<string, mixed>|string)>
			 */
			$class = new $classname( $data );
			$class->type = $name;
			return $class;
		}

		return null;
	}

	/**
	 * Get all Red_Match objects
	 *
	 * @return array<string, string>
	 */
	public static function all() {
		$data = [];

		$avail = self::available();
		foreach ( array_keys( $avail ) as $name ) {
			/** @var Red_Match<array<string, mixed>, (array<string, mixed>|string)>|null $obj */
			$obj = self::create( $name );
			if ( $obj === null ) {
				continue;
			}
			$data[ $name ] = $obj->name();
		}

		return $data;
	}

	/**
	 * Get list of available matches
	 *
	 * @return array<string, string>
	 */
	public static function available() {
		return [
			'url'      => 'url.php',
			'referrer' => 'referrer.php',
			'agent'    => 'user-agent.php',
			'login'    => 'login.php',
			'header'   => 'http-header.php',
			'custom'   => 'custom-filter.php',
			'cookie'   => 'cookie.php',
			'role'     => 'user-role.php',
			'server'   => 'server.php',
			'ip'       => 'ip.php',
			'page'     => 'page.php',
			'language' => 'language.php',
		];
	}
}
