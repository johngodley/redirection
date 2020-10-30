<?php

/**
 * Matches a URL and some other condition
 */
abstract class Red_Match {
	/**
	 * Match type
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Constructor
	 *
	 * @param array|string $values Initial values.
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
	 * @return array
	 */
	abstract public function save( array $details, $no_target_url = false );

	/**
	 * Get the match name
	 *
	 * @return String
	 */
	abstract public function name();

	/**
	 * Get the target URL
	 *
	 * @param String           $url URL of this redirect.
	 * @param String           $matched_url URL that was matched from the client.
	 * @param Red_Source_Flags $flag Flags.
	 * @param boolean          $is_matched Is this matched.
	 * @return String
	 */
	abstract public function get_target_url( $url, $matched_url, Red_Source_Flags $flag, $is_matched );
	abstract public function is_match( $url );
	abstract public function get_data();
	abstract public function load( $values );

	public function sanitize_url( $url ) {
		// No new lines
		$url = preg_replace( "/[\r\n\t].*?$/s", '', $url );

		// Clean control codes
		$url = preg_replace( '/[^\PC\s]/u', '', $url );

		return $url;
	}

	/**
	 * Get a regular expression target URL
	 *
	 * @param [type] $source_url
	 * @param [type] $target_url
	 * @param [type] $requested_url
	 * @param Red_Source_Flags $flags
	 * @return void
	 */
	protected function get_target_regex_url( $source_url, $target_url, $requested_url, Red_Source_Flags $flags ) {
		$regex = new Red_Regex( $source_url, $flags->is_ignore_case() );

		return $regex->replace( $target_url, $requested_url );
	}

	/**
	 * Create a Red_Match object, given a type
	 *
	 * @param string $name Match type.
	 * @param string $data Match data.
	 * @return Red_Match|false
	 */
	public static function create( $name, $data = '' ) {
		$avail = self::available();
		if ( isset( $avail[ strtolower( $name ) ] ) ) {
			$classname = $name . '_match';

			if ( ! class_exists( strtolower( $classname ) ) ) {
				include dirname( __FILE__ ) . '/../matches/' . $avail[ strtolower( $name ) ];
			}

			$class = new $classname( $data );
			$class->type = $name;
			return $class;
		}

		return false;
	}

	/**
	 * Get all Red_Match objects
	 *
	 * @return Red_Match[]
	 */
	public static function all() {
		$data = array();

		$avail = self::available();
		foreach ( $avail as $name => $file ) {
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
		return array(
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
		);
	}
}

/**
 * Trait to add redirect matching
 */
trait FromUrl_Match {
	/**
	 * URL to match against
	 *
	 * @var string
	 */
	public $url;

	/**
	 * Save match data
	 *
	 * @param array  $details Match data.
	 * @param string $no_target_url
	 * @param array  $data
	 * @return array
	 */
	private function save_data( array $details, $no_target_url, array $data ) {
		if ( $no_target_url === false ) {
			return array_merge( array(
				'url' => isset( $details['url'] ) ? $this->sanitize_url( $details['url'] ) : '',
			), $data );
		}

		return $data;
	}

	/**
	 * Get target URL for this match, depending on whether we match or not
	 *
	 * @param string           $requested_url Request URL.
	 * @param string           $source_url Redirect source URL.
	 * @param Red_Source_Flags $flags Redirect flags.
	 * @param [type] $matched
	 * @return string
	 */
	public function get_target_url( $requested_url, $source_url, Red_Source_Flags $flags, $matched ) {
		$target = $this->get_matched_target( $matched );

		if ( $flags->is_regex() && $target ) {
			return $this->get_target_regex_url( $source_url, $target, $requested_url, $flags );
		}

		return $target;
	}

	private function get_matched_target( $matched ) {
		if ( $matched ) {
			return $this->url;
		}

		return false;
	}

	private function load_data( $values ) {
		$values = unserialize( $values );

		if ( isset( $values['url'] ) ) {
			$this->url = $values['url'];
		}

		return $values;
	}

	private function get_from_data() {
		return array(
			'url' => $this->url,
		);
	}
}

trait FromNotFrom_Match {
	public $url_from = '';
	public $url_notfrom = '';

	private function save_data( array $details, $no_target_url, array $data ) {
		if ( $no_target_url === false ) {
			return array_merge( array(
				'url_from' => isset( $details['url_from'] ) ? $this->sanitize_url( $details['url_from'] ) : '',
				'url_notfrom' => isset( $details['url_notfrom'] ) ? $this->sanitize_url( $details['url_notfrom'] ) : '',
			), $data );
		}

		return $data;
	}

	public function get_target_url( $requested_url, $source_url, Red_Source_Flags $flags, $matched ) {
		// Action needs a target URL based on whether we matched or not
		$target = $this->get_matched_target( $matched );

		if ( $flags->is_regex() && $target ) {
			return $this->get_target_regex_url( $source_url, $target, $requested_url, $flags );
		}

		return $target;
	}

	private function get_matched_target( $matched ) {
		if ( $this->url_from !== '' && $matched ) {
			return $this->url_from;
		}

		if ( $this->url_notfrom !== '' && ! $matched ) {
			return $this->url_notfrom;
		}

		return false;
	}

	private function load_data( $values ) {
		$values = @unserialize( $values );

		if ( isset( $values['url_from'] ) ) {
			$this->url_from = $values['url_from'];
		}

		if ( isset( $values['url_notfrom'] ) ) {
			$this->url_notfrom = $values['url_notfrom'];
		}

		return $values;
	}

	/**
	 * Get the match data
	 *
	 * @return array<url_from: string, url_notfrom: string>
	 */
	private function get_from_data() {
		return array(
			'url_from' => $this->url_from,
			'url_notfrom' => $this->url_notfrom,
		);
	}
}
