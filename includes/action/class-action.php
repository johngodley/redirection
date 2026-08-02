<?php

namespace Redirection\Action;

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
	 * @return Action|null
	 */
	public static function create( $name, $code ) {
		$params = [
			'code' => $code,
			'type' => $name,
		];
		switch ( $name ) {
			case 'url':
				return new Url( $params );
			case 'error':
				return new Error( $params );
			case 'nothing':
				return new Nothing( $params );
			case 'random':
				return new Random( $params );
			case 'pass':
				return new Pass( $params );
			default:
				return null;
		}
	}

	/**
	 * Get list of available action types
	 *
	 * @return string[]
	 */
	public static function types() {
		return [ 'url', 'error', 'nothing', 'random', 'pass' ];
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
