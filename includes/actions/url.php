<?php

/**
 * URL action - redirect to a URL
 */
class Url_Action extends Red_Action {
	/**
	 * Redirect to a URL
	 *
	 * @param string $target Target URL.
	 * @return void
	 */
	protected function redirect_to( $target ) {
		// This is a known redirect, possibly extenal
		// phpcs:ignore
		$redirect = wp_redirect( $target, $this->get_code(), 'redirection' );

		if ( $redirect ) {
			/** @psalm-suppress InvalidGlobal */
			global $wp_version;

			if ( version_compare( $wp_version, '5.1', '<' ) ) {
				header( 'X-Redirect-Agent: redirection' );
			}

			die();
		}
	}

	/**
	 * Run this action. May not return from this function.
	 *
	 * @return void
	 */
	public function run() {
		$target = $this->get_target();

		if ( $target !== null ) {
			$this->redirect_to( $target );
		}
	}

	/**
	 * Does this action need a target?
	 *
	 * @return boolean
	 */
	public function needs_target() {
		return true;
	}

	public function name() {
		return __( 'Redirect to URL', 'redirection' );
	}
}
