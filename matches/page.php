<?php

class Page_Match extends Red_Match {
	use FromUrl_Match;

	public $page;

	public function name() {
		return __( 'URL and WordPress page type', 'redirection' );
	}

	public function save( array $details, $no_target_url = false ) {
		$data = array( 'page' => isset( $details['page'] ) ? $this->sanitize_page( $details['page'] ) : '404' );

		return $this->save_data( $details, $no_target_url, $data );
	}

	private function sanitize_page( $page ) {
		return '404';
	}

	public function is_match( $url ) {
		return is_404();
	}

	public function get_data() {
		return array_merge( array(
			'page' => $this->page,
		), $this->get_from_data() );
	}

	public function load( $values ) {
		$values = $this->load_data( $values );
		$this->page = isset( $values['page'] ) ? $values['page'] : '404';
	}
}
