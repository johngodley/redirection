<?php

class Red_Match {
	var $url;

	function __construct( $values = '' ) {
		if ( $values ) {
			$this->url = $values;

			$obj = maybe_unserialize( $values );

			if ( is_array( $obj ) ) {
				foreach ( $obj AS $key => $value ) {
					$this->$key = $value;
				}
			}
		}
	}

	function data( $details ) {
		$data = $this->save( $details );
		if ( count( $data ) == 1 && !is_array( current( $data ) ) )
			$data = current( $data );
		else
			$data = serialize( $data );
		return $data;
	}

	function save( $details ) {
		return array();
	}

	function name() {
		return '';
	}

	function show() {
	}

	function wants_it() {
		return true;
	}

	function get_target( $url, $matched_url, $regex ) {
		return false;
	}

	static function create( $name, $data = '' ) {
		$avail = self::available();
		if ( isset( $avail[strtolower( $name )] ) ) {
			$classname = $name.'_match';

			if ( !class_exists( strtolower( $classname ) ) )
				include( dirname( __FILE__ ).'/../matches/'.$avail[strtolower( $name )] );
			return new $classname( $data );
		}

		return false;
	}

	static function all() {
		$data = array();

		$avail = self::available();
		foreach ( $avail AS $name => $file ) {
			$obj = self::create( $name );
			$data[$name] = $obj->name();
		}

		return $data;
	}

	static function available() {
	 	return array (
			'url'      => 'url.php',
			'referrer' => 'referrer.php',
			'agent'    => 'user-agent.php',
			'login'    => 'login.php',
		 );
	}

	function match_name() {
		return '';
	}
}
