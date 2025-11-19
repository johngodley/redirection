<?php

/**
 * @phpstan-type PageMap array{
 *    page?: string,
 *    url?: string
 * }
 * @phpstan-type PageResult array{
 *    page: string,
 *    url?: string
 * }
 * @phpstan-type PageData array{
 *    page: string,
 *    url: string
 * }
 *
 * Match the WordPress page type
 *
 * @phpstan-extends Red_Match<PageMap, PageResult>
 */
class Page_Match extends Red_Match {
	use FromUrl_Match;

	/**
	 * Page type
	 *
	 * @var string
	 */
	public $page = '404';

	public function name() {
		return __( 'URL and WordPress page type', 'redirection' );
	}

	/**
	 * Save data to an array, ready for serializing.
	 *
	 * @param PageMap $details New match data.
	 * @param bool $no_target_url Does the action have a target URL.
	 * @return PageResult
	 */
	public function save( array $details, $no_target_url = false ) {
		$data = array( 'page' => isset( $details['page'] ) ? $this->sanitize_page( $details['page'] ) : '404' );

		$result = $this->save_data( $details, $no_target_url, $data );
		return $result; // @phpstan-ignore-line
	}

	/**
	 * @param string $page
	 * @return string
	 */
	private function sanitize_page( $page ) {
		return '404';
	}

	public function is_match( $url ) {
		return is_404();
	}

	/**
	 * Get the match data for persistence.
	 *
	 * @return PageData
	 */
	public function get_data() {
		return array_merge(
			array(
				'page' => $this->page,
			),
			$this->get_from_data()
		);
	}

	/**
	 * Load the match data into this instance.
	 *
	 * @param string|PageMap $values Match values, as read from the database (plain text, serialized PHP, or parsed array).
	 * @return void
	 */
	public function load( $values ) {
		$data = $this->load_data( $values );
		$this->page = isset( $data['page'] ) ? $data['page'] : '404'; // @phpstan-ignore-line
	}
}
