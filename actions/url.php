<?php

/**
 * URL action - redirect to a URL
 */
class Url_Action extends Red_Action {
	/**
	 * Redirect to a URL
	 *
	 * @param integer $code HTTP status code.
	 * @param string  $target Target URL.
	 * @return void
	 */
	protected function redirect_to( $code, $target ) {
		// This is a known redirect, possibly extenal
		// phpcs:ignore
		$redirect = wp_redirect( $target, $code, 'redirection' );

		if ( $redirect ) {
			/** @psalm-suppress InvalidGlobal */
			global $wp_version;

			if ( version_compare( $wp_version, '5.1', '<' ) ) {
				header( 'X-Redirect-Agent: redirection' );
			}

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
