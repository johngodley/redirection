<?php

class Red_Action {
	function __construct( $values ) {
		if ( is_array( $values ) ) {
			foreach ( $values as $key => $value ) {
			 	$this->$key = $value;
			}
		}
	}

	static function create( $name, $code ) {
		$avail = self::available();

		if ( isset( $avail[ $name ] ) ) {
			if ( ! class_exists( strtolower( $avail[ $name ][1] ) ) ) {
				include_once dirname( __FILE__ ).'/../actions/'.$avail[ $name ][0];
			}

			$obj = new $avail[ $name ][1]( array( 'action_code' => $code ) );
			$obj->type = $name;
			return $obj;
		}

		return false;
	}

	static function available() {
	 	return array(
			'url'     => array( 'url.php',     'Url_Action' ),
			'error'   => array( 'error.php',   'Error_Action' ),
			'nothing' => array( 'nothing.php', 'Nothing_Action' ),
			'random'  => array( 'random.php',  'Random_Action' ),
			'pass'    => array( 'pass.php',    'Pass_Action' ),
		);
	}

	public function process_before( $code, $target ) {
		return $target;
	}

	public function process_after( $code, $target ) {
		return true;
	}
}
