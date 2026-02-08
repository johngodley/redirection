<?php

/**
 * @phpstan-type FromNotFromMap (array{
 *    url_from?: string,
 *    url_notfrom?: string
 * } & array<string, mixed>)
 * @phpstan-type FromNotFromData array{
 *    url_from: string,
 *    url_notfrom: string
 * }
 *
 * Trait to add redirect matching that adds a matched target
 */
trait FromNotFrom_Match {
	/**
	 * Target URL if matched
	 *
	 * @var string
	 */
	public $url_from = '';

	/**
	 * Target URL if not matched
	 *
	 * @var string
	 */
	public $url_notfrom = '';

	/**
	 * Save data to an array, ready for serializing.
	 *
	 * @phpstan-template TData of array<string, mixed>
	 * @param FromNotFromMap $details New match data.
	 * @param bool $no_target_url Does the action have a target URL.
	 * @phpstan-param TData $data Existing match data.
	 * @param array<string, mixed> $data Existing match data.
	 * @phpstan-return TData&FromNotFromMap
	 * @return array<string, mixed>
	 */
	private function save_data( array $details, $no_target_url, array $data ) {
		if ( $no_target_url === false ) {
			return array_merge(
				array(
					'url_from' => isset( $details['url_from'] ) ? $this->sanitize_url( $details['url_from'] ) : '',
					'url_notfrom' => isset( $details['url_notfrom'] ) ? $this->sanitize_url( $details['url_notfrom'] ) : '',
				),
				$data
			);
		}

		return $data;
	}

	/**
	 * Get target URL for this match, depending on whether we match or not
	 *
	 * @param string           $requested_url Request URL.
	 * @param string           $source_url Redirect source URL.
	 * @param Red_Source_Flags $flags Redirect flags.
	 * @param boolean          $matched Has the source been matched.
	 * @return string|false
	 */
	public function get_target_url( $requested_url, $source_url, Red_Source_Flags $flags, $matched ) {
		// Action needs a target URL based on whether we matched or not
		$target = $this->get_matched_target( $matched );

		if ( $flags->is_regex() && $target !== false ) {
			return $this->get_target_regex_url( $source_url, $target, $requested_url, $flags );
		}

		return $target;
	}

	/**
	 * Return the matched target if we have matched and one exists, or return the unmatched target if not matched.
	 *
	 * @param boolean $matched Is it matched.
	 * @return false|string
	 */
	private function get_matched_target( $matched ) {
		if ( $this->url_from !== '' && $matched ) {
			return $this->url_from;
		}

		if ( $this->url_notfrom !== '' && ! $matched ) {
			return $this->url_notfrom;
		}

		return false;
	}

	/**
	 * Load the data into the instance.
	 *
	 * @phpstan-template TValues of array<string, mixed>
	 * @param string|TValues $values Serialized PHP data or parsed array.
	 * @phpstan-return TValues&FromNotFromMap
	 * @return array<string, mixed>&FromNotFromMap
	 */
	private function load_data( $values ) {
		if ( is_string( $values ) ) {
			$values = @unserialize( $values ); // phpcs:ignore
		}

		if ( isset( $values['url_from'] ) ) {
			$this->url_from = $values['url_from'];
		}

		if ( isset( $values['url_notfrom'] ) ) {
			$this->url_notfrom = $values['url_notfrom'];
		}

		return is_array( $values ) ? $values : [];
	}

	/**
	 * Get the match data
	 *
	 * @return FromNotFromData
	 */
	private function get_from_data(): array {
		return [
			'url_from' => $this->url_from,
			'url_notfrom' => $this->url_notfrom,
		];
	}
}
