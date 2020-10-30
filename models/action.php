<?php

/**
 * A redirect action - what happens after a URL is matched.
 */
abstract class Red_Action {
	/**
	 * The action code (i.e. HTTP code)
	 *
	 * @var integer
	 */
	protected $code;

	/**
	 * The action type
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Constructor
	 *
	 * @param array $values Values.
	 */
	public function __construct( $values ) {
		if ( is_array( $values ) ) {
			foreach ( $values as $key => $value ) {
				$this->$key = $value;
			}
		}
	}

	/**
	 * Create an action object
	 *
	 * @param string $name Action type.
	 * @param string $code Action code.
	 * @return Red_Action|false
	 */
	public static function create( $name, $code ) {
		$avail = self::available();

		if ( isset( $avail[ $name ] ) ) {
			if ( ! class_exists( strtolower( $avail[ $name ][1] ) ) ) {
				include_once dirname( __FILE__ ) . '/../actions/' . $avail[ $name ][0];
			}

			$obj = new $avail[ $name ][1]( array( 'code' => $code ) );
			$obj->type = $name;
			return $obj;
		}

		return false;
	}

	/**
	 * Get list of available actions
	 *
	 * @return array
	 */
	public static function available() {
		return array(
			'url'     => array( 'url.php', 'Url_Action' ),
			'error'   => array( 'error.php', 'Error_Action' ),
			'nothing' => array( 'nothing.php', 'Nothing_Action' ),
			'random'  => array( 'random.php', 'Random_Action' ),
			'pass'    => array( 'pass.php', 'Pass_Action' ),
		);
	}

	/**
	 * Perform any processing before the action
	 *
	 * @param integer $code Target HTTP code.
	 * @param string  $target Target URL.
	 * @return string
	 */
	public function process_before( $code, $target ) {
		return $target;
	}

	/**
	 * Perform any processing after the action
	 *
	 * @param integer $code Target HTTP code.
	 * @param string  $target Target URL.
	 * @return boolean
	 */
	public function process_after( $code, $target ) {
		return true;
	}

	/**
	 * Get the action code
	 *
	 * @return integer
	 */
	public function get_code() {
		return $this->code;
	}

	/**
	 * Get action type
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Does this action need a target?
	 *
	 * @return boolean
	 */
	abstract public function needs_target();
}
