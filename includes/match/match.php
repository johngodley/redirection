<?php

namespace Redirection\Match;

use Redirection\Site;
use Redirection\Url as Redirect_Url;

require_once __DIR__ . '/from-notfrom.php';
require_once __DIR__ . '/from-url.php';

/**
 * Matches a URL and some other condition
 */
abstract class Match {
	/**
	 * Match type
	 *
	 * @var string
	 */
	protected $type = '';

	/**
	 * Constructor
	 *
	 * @param string $values Initial values.
	 */
	public function __construct( $values = '' ) {
		if ( $values ) {
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
	 * @param array   $details Details to save.
	 * @param boolean $no_target_url The URL when no target.
	 * @return array|null
	 */
	abstract public function save( array $details, $no_target_url = false );

	/**
	 * Get the match name
	 *
	 * @return String
	 */
	abstract public function name();

	/**
	 * Match the URL against the specific matcher conditions
	 *
	 * @param String $url Requested URL.
	 * @return boolean
	 */
	abstract public function is_match( $url );

	/**
	 * Get the target URL for this match. Some matches may have a matched/unmatched target.
	 *
	 * @param String           $original_url The client URL (not decoded).
	 * @param String           $matched_url The URL in the redirect.
	 * @param Redirect_Url\Source_Flags $flag Source flags.
	 * @param boolean          $is_matched Was the match successful.
	 * @return String|false
	 */
	abstract public function get_target_url( $original_url, $matched_url, Redirect_Url\Source_Flags $flag, $is_matched );

	/**
	 * Get the match data
	 *
	 * @return array|null
	 */
	abstract public function get_data();

	/**
	 * Load the match data into this instance.
	 *
	 * @param String $values Match values, as read from the database (plain text or serialized PHP).
	 * @return void
	 */
	abstract public function load( $values );

	/**
	 * Sanitize a match URL
	 *
	 * @param String $url URL.
	 * @return String
	 */
	public function sanitize_url( $url ) {
		// No new lines
		$url = preg_replace( "/[\r\n\t].*?$/s", '', $url );

		// Clean control codes
		$url = preg_replace( '/[^\PC\s]/u', '', $url );

		return $url;
	}

	/**
	 * Apply a regular expression to the target URL, replacing any values.
	 *
	 * @param string           $source_url Redirect source URL.
	 * @param string           $target_url Target URL.
	 * @param string           $requested_url The URL being requested (decoded).
	 * @param Redirect_Url\Source_Flags $flags Source URL flags.
	 * @return string
	 */
	protected function get_target_regex_url( $source_url, $target_url, $requested_url, Redirect_Url\Source_Flags $flags ) {
		$regex = new Site\Regex( $source_url, $flags->is_ignore_case() );

		return $regex->replace( $target_url, $requested_url );
	}

	/**
	 * Create a Match\Match object, given a type
	 *
	 * @param string $name Match type.
	 * @param string $data Match data.
	 * @return Match|null
	 */
	public static function create( $name, $data = '' ) {
		$avail = self::available();
		if ( isset( $avail[ strtolower( $name ) ] ) ) {
			require_once __DIR__ . '/' . $avail[ strtolower( $name ) ];

			switch ( $name ) {
				case 'url':
					$class = new Url_Only();
					break;

				case 'referrer':
					$class = new Referrer();
					break;

				case 'agent':
					$class = new User_Agent();
					break;

				case 'login':
					$class = new Login();
					break;

				case 'header':
					$class = new Header();
					break;

				case 'custom':
					$class = new Custom();
					break;

				case 'cookie':
					$class = new Cookie();
					break;

				case 'role':
					$class = new Role();
					break;

				case 'server':
					$class = new Server();
					break;

				case 'ip':
					$class = new Ip();
					break;

				case 'page':
					$class = new Page();
					break;

				case 'language':
					$class = new Language();
					break;
			}

			$class->type = $name;
			return $class;
		}

		return null;
	}

	/**
	 * Get all Match\Match objects
	 *
	 * @return String[]
	 */
	public static function all() {
		$data = [];

		$avail = self::available();
		foreach ( array_keys( $avail ) as $name ) {
			/**
			 * @var Match\Match
			 */
			$obj = self::create( $name );
			$data[ $name ] = $obj->name();
		}

		return $data;
	}

	/**
	 * Get list of available matches
	 *
	 * @return array
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
