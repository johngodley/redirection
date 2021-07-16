<?php

/**
 * The 'do nothing' action. This really does nothing, and is used to short-circuit Redirection so that it doesn't trigger other redirects.
 */
class Nothing_Action extends Red_Action {
	/**
	 * Issue an action when nothing happens. This stops further processing.
	 *
	 * @return void
	 */
	public function run() {
		do_action( 'redirection_do_nothing', $this->get_target() );
	}

	public function name() {
		return __( 'Do nothing (ignore)', 'redirection' );
	}
}
