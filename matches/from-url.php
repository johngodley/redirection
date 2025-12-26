<?php

/**
 * @phpstan-type FromUrlMap (array{
 *    url?: string
 * } & array<string, mixed>)
 * @phpstan-type FromUrlData array{
 *    url: string
 * }
 *
 * Trait to add redirect matching that adds a matched target
 */
trait FromUrl_Match {
	/**
	 * URL to match against
	 *
	 * @var string
	 */
	public $url = '';

	/**
	 * Save data to an array, ready for serializing.
	 *
	 * @phpstan-template TData of array<string, mixed>
	 * @param FromUrlMap $details New match data.
	 * @param bool $no_target_url Does the action have a target URL.
	 * @phpstan-param TData $data Existing match data.
	 * @param array<string, mixed> $data Existing match data.
	 * @phpstan-return TData&FromUrlMap
	 * @return array<string, mixed>
	 */
	private function save_data( array $details, $no_target_url, array $data ) {
		if ( $no_target_url === false ) {
			return array_merge(
				[
					'url' => isset( $details['url'] ) ? $this->sanitize_url( $details['url'] ) : '',
				],
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
	 * @param boolean          $matched Is the URL matched.
	 * @return string|false
	 */
	public function get_target_url( $requested_url, $source_url, Red_Source_Flags $flags, $matched ) {
		$target = $this->get_matched_target( $matched );

		if ( $flags->is_regex() && $target !== false ) {
			return $this->get_target_regex_url( $source_url, $target, $requested_url, $flags );
		}

		return $target;
	}

	/**
	 * Return the matched target, if one exists.
	 *
	 * @param boolean $matched Is it matched.
	 * @return false|string
	 */
	private function get_matched_target( $matched ) {
		if ( $matched ) {
			return $this->url;
		}

		return false;
	}

	/**
	 * Load the data into the instance.
	 *
	 * @phpstan-template TValues of array<string, mixed>
	 * @param string|TValues $values Serialized PHP or parsed array.
	 * @phpstan-return TValues&FromUrlMap
	 * @return array<string, mixed>&FromUrlMap
	 */
	private function load_data( $values ) {
		if ( is_string( $values ) ) {
			$values = unserialize( $values ); // phpcs:ignore
		}

		if ( isset( $values['url'] ) ) {
			$this->url = $values['url'];
		}

		return is_array( $values ) ? $values : [];
	}

	/**
	 * Get the loaded data as an array.
	 *
	 * @return FromUrlData
	 */
	private function get_from_data(): array {
		return [
			'url' => $this->url,
		];
	}
}
