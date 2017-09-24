<?php

class Url_Action extends Red_Action {
	protected function redirect_to( $code, $target ) {
		header( 'x-redirect-agent: redirection' );

		$redirect = wp_redirect( $target, $code );

		if ( $redirect ) {
			die();
		}
	}

	public function process_after( $code, $target ) {
		$this->redirect_to( $code, $target );
	}
}
