<?php

/**
 * @phpstan-type CustomFilterMap array{
 *  filter?: string,
 *  url_from?: string,
 *  url_notfrom?: string
 * }
 * @phpstan-type CustomFilterResult array{
 *  filter: string,
 *  url_from?: string,
 *  url_notfrom?: string
 * }
 * @phpstan-type CustomFilterData array{
 *  filter: string,
 *  url_from: string,
 *  url_notfrom: string
 * }
 * @phpstan-extends Red_Match<CustomFilterMap, CustomFilterResult>
 *
 * Perform a check against the results of a custom filter
 *
 */
class Custom_Match extends Red_Match {
	use FromNotFrom_Match;

	/**
	 * Filter name
	 *
	 * @var string
	 */
	public $filter = '';

	/**
	 * Name of this match used in UI.
	 *
	 * @return string
	 */
	public function name() {
		return __( 'URL and custom filter', 'redirection' );
	}

	/**
	 * Save data to an array, ready for serializing.
	 *
	 * @param CustomFilterMap $details New match data.
	 * @param boolean $no_target_url Does the action have a target URL.
	 * @return CustomFilterResult
	 */
	public function save( array $details, $no_target_url = false ) {
		$data = [
			'filter' => isset( $details['filter'] ) ? $this->sanitize_filter( $details['filter'] ) : '',
		];

		/** @var CustomFilterResult $result */
		$result = $this->save_data( $details, $no_target_url, $data );
		return $result;
	}

	/**
	 * Sanitize filter name to allow only alphanumeric, dash and underscore.
	 *
	 * @param string $name Filter name.
	 * @return string
	 */
	public function sanitize_filter( $name ) {
		$name = (string) preg_replace( '/[^A-Za-z0-9\-_]/', '', sanitize_text_field( $name ) );

		return trim( $name );
	}

	/**
	 * Determine if the current request matches using the custom filter.
	 *
	 * @param string $url Requested URL.
	 * @return boolean
	 */
	public function is_match( $url ) {
		return apply_filters( $this->filter, false, $url );
	}

	/**
	 * Get the match data for persistence.
	 *
	 * @return CustomFilterData
	 */
	public function get_data() {
		return array_merge(
			[
				'filter' => $this->filter,
			],
			$this->get_from_data()
		);
	}

	/**
	 * Load the match data into this instance.
	 *
	 * @param string|CustomFilterMap $values Match values from database (serialized PHP or parsed array).
	 * @return void
	 */
	public function load( $values ) {
		$values = $this->load_data( $values );
		/** @var CustomFilterMap $values */
		$this->filter = isset( $values['filter'] ) ? $values['filter'] : '';
	}
}
