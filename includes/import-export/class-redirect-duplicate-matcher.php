<?php

namespace Redirection\ImportExport;

/**
 * Find an existing redirect for an imported redirect payload.
 */
class RedirectDuplicateMatcher {
	/**
	 * @var RedirectRepository
	 */
	private $redirects;

	/**
	 * @param RedirectRepository|null $redirects
	 */
	public function __construct( ?RedirectRepository $redirects = null ) {
		$this->redirects = $redirects ? $redirects : new RedirectRepository();
	}

	/**
	 * @param array<string, mixed> $redirect Redirect data being imported.
	 * @param int $file_redirect_id Redirect ID referenced by the file.
	 * @return \Red_Item|false
	 */
	public function get_existing_redirect( array $redirect, $file_redirect_id ) {
		if ( $file_redirect_id > 0 ) {
			$existing = $this->redirects->get( $file_redirect_id );
			if ( $existing !== false && $this->matches_redirect_data( $existing, $redirect ) ) {
				return $existing;
			}
		}

		if ( ! isset( $redirect['url'] ) || ! is_string( $redirect['url'] ) ) {
			return false;
		}

		return $this->redirects->get_for_url(
			$redirect['url'],
			isset( $redirect['regex'] ) ? $redirect['regex'] === true : false
		);
	}

	/**
	 * @param \Red_Item $existing Existing redirect.
	 * @param array<string, mixed> $redirect Redirect data being imported.
	 * @return bool
	 */
	private function matches_redirect_data( \Red_Item $existing, array $redirect ) {
		if ( ! isset( $redirect['url'] ) || ! is_string( $redirect['url'] ) ) {
			return false;
		}

		$data = $existing->to_json();
		if ( ! is_array( $data ) || ! isset( $data['url'] ) || ! is_string( $data['url'] ) ) {
			return false;
		}

		return $data['url'] === $redirect['url']
			&& ( isset( $data['regex'] ) ? $data['regex'] === true : false ) === ( isset( $redirect['regex'] ) ? $redirect['regex'] === true : false );
	}
}
