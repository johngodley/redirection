<?php

namespace Redirection\Action;

/**
 * A redirect action - what happens after a URL is matched.
 */
abstract class Action {
	/**
	 * The action code (i.e. HTTP code)
	 *
	 * @var integer
	 */
	protected $code = 0;

	/**
	 * The action type
	 *
	 * @var string
	 */
	protected $type = '';

	/**
	 * Target URL, if any
	 *
	 * @var String|null
	 */
	protected $target = null;

	/**
	 * Constructor
	 *
	 * @param array $values Values.
	 */
	public function __construct( $values = [] ) {
		if ( is_array( $values ) ) {
			foreach ( $values as $key => $value ) {
				$this->$key = $value;
			}
		}
	}

	abstract public function name();

	/**
	 * Create an action object
	 *
	 * @param string  $name Action type.
	 * @param integer $code Action code.
	 * @return Action|null
	 */
	public static function create( $name, $code ) {
		$avail = self::available();
		$code = [ 'code' => intval( $code, 10 ) ];

		if ( isset( $avail[ $name ] ) ) {
			require_once __DIR__ . '/action-' . $avail[ $name ];

			switch ( $name ) {
				case 'url':
					$class = new Url( $code );
					break;

				case 'error':
					$class = new Error( $code );
					break;

				case 'nothing':
					$class = new Nothing( $code );
					break;

				case 'random':
					$class = new Random( $code );
					break;

				case 'pass':
					$class = new Pass( $code );
					break;
			}

			$class->type = $name;
			return $class;
		}

		return null;
	}

	/**
	 * Get list of available actions
	 *
	 * @return array
	 */
	public static function available() {
		return [
			'url'     => 'url.php',
			'error'   => 'error.php',
			'nothing' => 'nothing.php',
			'random'  => 'random.php',
			'pass'    => 'pass.php',
		];
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
	 * Set the target for this action
	 *
	 * @param String $target_url The original URL from the client.
	 * @return void
	 */
	public function set_target( $target_url ) {
		$this->target = $target_url;
	}

	/**
	 * Get the target for this action
	 *
	 * @return String|null
	 */
	public function get_target() {
		return $this->target;
	}

	/**
	 * Does this action need a target?
	 *
	 * @return boolean
	 */
	public function needs_target() {
		return false;
	}

	/**
	 * Run this action. May not return from this function.
	 *
	 * @return void
	 */
	abstract public function run();
}
