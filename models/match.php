<?php

abstract class Red_Match {
	public function __construct( $values = '' ) {
		if ( $values ) {
			$this->load( $values );
		}
	}

	abstract public function save( array $details, $no_target_url = false );
	abstract public function name();
	abstract public function get_target( $url, $matched_url, $regex );
	abstract public function get_data();
	abstract public function load( $values );

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

			if ( ! class_exists( strtolower( $classname ) ) ) {
				include( dirname( __FILE__ ).'/../matches/'.$avail[ strtolower( $name ) ] );
			}

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
