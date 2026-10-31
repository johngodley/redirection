<?php

namespace Redirection\Compare;

use Redirection\Core\Regex;
use Redirection\Url\Transform;

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
	 * Regex target, after replacement, or `null` if there isn't one.
	 *
	 * @var string|null
	 */
	private $regex_target = null;

	/**
	 * Regex target, with placeholders for any brackets in text from the request.
	 *
	 * @var string|null
	 */
	private $masked_target = null;

	/**
	 * Placeholders used in `$masked_target`, for each bracket.
	 *
	 * @var array<string, string>
	 */
	private $placeholders = [];

	/**
	 * Configured target for a regex target that must stay relative to this site, or `null` if there isn't one.
	 *
	 * @var string|null
	 */
	private $target_template = null;

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
		$has_origin = $this->has_origin( $target_url );

		// Brackets in text from the request are given placeholders that can't be guessed, so that only the target the user
		// has written can use a shortcode. The target itself is unchanged. A placeholder starts with a letter that isn't hex,
		// so that it can't follow a `%0` from the request and be removed by WordPress as a `%0a` or `%0d`
		$this->placeholders = [
			'[' => 'z' . bin2hex( random_bytes( 16 ) ),
			']' => 'z' . bin2hex( random_bytes( 16 ) ),
		];

		// Text from the request before the match is dropped from a target with its own scheme or host, so it can't come
		// before that scheme or host
		$replaced = $regex->replace( $target_url, $requested_url, ! $has_origin );
		$masked = $regex->replace( $target_url, $requested_url, ! $has_origin, $this->placeholders );

		$this->target_template = null;

		// A target the user has written with its own scheme or host keeps that host
		if ( $has_origin ) {
			$this->regex_target = $this->keep_target_host( $target_url, $replaced );
			$this->masked_target = $this->keep_target_host( $target_url, $masked );

			return $this->regex_target;
		}

		$this->target_template = $target_url;

		// The target is checked as it will finally be seen, but only changed if needed
		if ( $this->has_origin( $replaced ) ) {
			$replaced = $this->make_relative( $replaced );
			$masked = $this->make_relative( $masked );
		}

		$this->regex_target = $replaced;
		$this->masked_target = $masked;

		return $this->regex_target;
	}

	/**
	 * Transform the shortcodes in a target URL.
	 *
	 * Text from the request in a regex target can't be used as a shortcode. A site relative regex target is checked again
	 * after it has been transformed, as a shortcode around captured data, such as `[lower]$1[/lower]`, may only become an
	 * absolute URL once it has been transformed.
	 *
	 * @param string $target_url Target URL.
	 * @param Transform $transformer Target transformer.
	 * @return string
	 */
	public function transform_target_url( $target_url, Transform $transformer ) {
		if ( $this->regex_target === null || $this->masked_target === null || $this->regex_target === '' ) {
			return $transformer->transform( $target_url );
		}

		// An earlier filter may have added to our regex target, so it is replaced wherever it is
		$masked = str_replace( $this->regex_target, $this->masked_target, $target_url );
		$transformed = $transformer->transform( $masked, array_flip( $this->placeholders ) );

		// Only recheck a site relative target, and not one that an earlier filter has changed
		if ( $this->target_template === null || $target_url !== $this->regex_target ) {
			return $transformed;
		}

		// The configured target may itself transform into an absolute URL, such as a post ID
		if ( ! $this->has_origin( $transformed ) || $this->has_origin( $transformer->transform( $this->target_template ) ) ) {
			return $transformed;
		}

		// A captured post ID is transformed into its permalink, which is on this site
		if ( is_numeric( $target_url ) ) {
			return $transformed;
		}

		return $this->make_relative( $transformed );
	}

	/**
	 * Stop a regex replacement turning a site relative target into one that points at another site.
	 *
	 * A target of `/$1` is relative to this site. If the captured value itself begins with a slash then the replaced
	 * target becomes `//example.com/path`, which a browser treats as a protocol relative URL pointing at `example.com`.
	 * Similarly a target of `$1` can be given a scheme. Remove these so the target stays on this site.
	 *
	 * @param string $url Target URL, with a scheme or host.
	 * @return string
	 */
	private function make_relative( $url ) {
		// A browser treats a backslash as a slash, and WordPress removes other characters
		return '/' . ltrim( wp_sanitize_redirect( $url ), '/' );
	}

	/**
	 * Stop a regex replacement changing the host of a target that has one.
	 *
	 * Text from the request straight after the host always starts the path. This can be a capture, such as in
	 * `https://example.com$1`, or text after the match, such as with a source of `^/old` and a target of
	 * `https://example.com`. Without this a value of `@other.example` would make `other.example` the host.
	 *
	 * A browser finds the host of these schemes even without the `//`, such as in `https:example.com`, and treats a
	 * backslash as a slash. Any other scheme has a host only after a `//`.
	 *
	 * @param string $target_url Target URL, before the replacement.
	 * @param string $replaced Target URL, after the replacement.
	 * @return string
	 */
	private function keep_target_host( $target_url, $replaced ) {
		if ( preg_match( '@^(?:(?:file|ftp|https?|wss?):[/\\\\]*|(?:[a-z][a-z0-9+.-]*:)?[/\\\\]{2,})[^/\\\\?#$]+@i', $target_url, $matches ) !== 1 ) {
			return $replaced;
		}

		// The replacement turns an escaped backslash into a single one
		$host = (string) preg_replace( '@\\\\([\\\\$])@', '$1', $matches[0] );
		$path = substr( $replaced, strlen( $host ) );

		if ( strpos( $replaced, $host ) !== 0 || $path === '' || in_array( $path[0], [ '/', '?', '#' ], true ) ) {
			return $replaced;
		}

		return $host . '/' . $path;
	}

	/**
	 * Does the URL have a scheme or host? This is checked as the browser will see it, both before and after WordPress has
	 * sanitized it. A browser treats a backslash as a slash and ignores control characters, but WordPress removes them.
	 *
	 * @param string $url URL.
	 * @return bool
	 */
	private function has_origin( $url ) {
		$unsanitized = (string) preg_replace( '/[\x00-\x20\x7F]/', '', str_replace( '\\', '/', $url ) );

		foreach ( [ $unsanitized, wp_sanitize_redirect( $url ) ] as $check ) {
			if ( preg_match( '@^(?:[a-z][a-z0-9+.-]*:|//)@i', $check ) === 1 ) {
				return true;
			}
		}

		return false;
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
