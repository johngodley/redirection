<?php

class Url_Action extends Red_Action {
	protected function redirect_to( $code, $target ) {
		add_filter( 'x_redirect_by', [ $this, 'x_redirect_by' ] );

		$redirect = wp_redirect( $target, $code );

		if ( $redirect ) {
			die();
		}
	}

	public function process_after( $code, $target ) {
		$this->redirect_to( $code, $target );
	}

	public function needs_target() {
		return true;
	}

	public function x_redirect_by() {
		return 'redirection';
	}
}
