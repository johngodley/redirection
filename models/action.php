<?php

/**
 * A redirect action - what happens after a URL is matched.
 *
 * @phpstan-type UrlActionData array{
 *     code: int,
 *     target: string,
 *     type: string
 * }
 * @phpstan-type ErrorActionData array{
 *     code: int,
 *     type: string
 * }
 * @phpstan-type NothingActionData array{
 *     code: int,
 *     type: string
 * }
 * @phpstan-type RandomActionData array{
 *     code: int,
 *     type: string
 * }
 * @phpstan-type PassActionData array{
 *     code: int,
 *     target: string,
 *     type: string
 * }
 * @phpstan-type RedActionData UrlActionData|ErrorActionData|NothingActionData|RandomActionData|PassActionData
 */
abstract class Red_Action {
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
	 * @var string|null
	 */
	protected $target = null;

	/**
	 * Constructor
	 *
	 * @param RedActionData|array{} $values Values.
	 */
	public function __construct( $values = [] ) {
		if ( isset( $values['code'] ) ) {
			$this->code = $values['code'];
		}

		if ( isset( $values['target'] ) ) {
			$this->target = $values['target'];
		}

		if ( isset( $values['type'] ) ) {
			$this->type = $values['type'];
		}
	}

	/**
	 * @return string
	 */
	abstract public function name();

	/**
	 * Create an action object
	 *
	 * @param string  $name Action type.
	 * @param integer $code Action code.
	 * @return Red_Action|null
	 */
	public static function create( $name, $code ) {
		$avail = self::available();

		if ( isset( $avail[ $name ] ) ) {
			if ( ! class_exists( strtolower( $avail[ $name ][1] ) ) ) {
				include_once __DIR__ . '/../actions/' . $avail[ $name ][0];
			}

			/**
			 * @var Red_Action
			 */
			$obj = new $avail[ $name ][1]( [ 'code' => $code ] );
			$obj->type = $name;
			return $obj;
		}

		return null;
	}

	/**
	 * Get list of available actions
	 *
	 * @return array<string, array{string, string}>
	 */
	public static function available() {
		return [
			'url'     => [ 'url.php', 'Url_Action' ],
			'error'   => [ 'error.php', 'Error_Action' ],
			'nothing' => [ 'nothing.php', 'Nothing_Action' ],
			'random'  => [ 'random.php', 'Random_Action' ],
			'pass'    => [ 'pass.php', 'Pass_Action' ],
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
	 * @param string $target_url The original URL from the client.
	 * @return void
	 */
	public function set_target( $target_url ) {
		$this->target = $target_url;
	}

	/**
	 * Get the target for this action
	 *
	 * @return string|null
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
