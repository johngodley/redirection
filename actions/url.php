<?php

class Url_Action extends Red_Action {
	protected function redirect_to( $code, $target ) {
		$redirect = wp_redirect( $target, $code );

		if ( $redirect ) {
			header( 'X-Redirect-Agent: redirection' );
			die();
		}
	}

	public function process_after( $code, $target ) {
		$this->redirect_to( $code, $target );
	}

	public function needs_target() {
		return true;
	}
}
