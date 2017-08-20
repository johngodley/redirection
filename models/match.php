<?php

abstract class Red_Match {
	public $url;

	public function __construct( $values = '' ) {
		if ( $values ) {
			$this->url = $values;

			$obj = maybe_unserialize( $values );

			if ( is_array( $obj ) ) {
				foreach ( $obj as $key => $value ) {
					$this->$key = $value;
				}
			}
		}
	}

	abstract public function save( array $details, $no_target_url = false );
	abstract public function name();
	abstract public function get_target( $url, $matched_url, $regex );

	public function sanitize_url( $url ) {
		// No new lines
		$url = preg_replace( "/[\r\n\t].*?$/s", '', $url );

		// Clean control codes
		$url = preg_replace( '/[^\PC\s]/u', '', $url );

		return $url;
	}

	protected function get_target_regex_url( $matched_url, $target, $url ) {
		return preg_replace( '@'.str_replace( '@', '\\@', $matched_url ).'@', $target, $url );
	}

	static function create( $name, $data = '' ) {
		$avail = self::available();
		if ( isset( $avail[ strtolower( $name ) ] ) ) {
			$classname = $name.'_match';

			if ( ! class_exists( strtolower( $classname ) ) )
				include( dirname( __FILE__ ).'/../matches/'.$avail[ strtolower( $name ) ] );
			return new $classname( $data );
		}

		return false;
	}

	static function all() {
		$data = array();

		$avail = self::available();
		foreach ( $avail as $name => $file ) {
			$obj = self::create( $name );
			$data[ $name ] = $obj->name();
		}

		return $data;
	}

	static function available() {
	 	return array(
			'url'      => 'url.php',
			'referrer' => 'referrer.php',
			'agent'    => 'user-agent.php',
			'login'    => 'login.php',
		 );
	}
}
