<?php

namespace Redirection\ImportExport\Parser;

/**
 * Parse Redirection JSON import files.
 */
class JsonParser {
	/**
	 * @param string $data
	 * @return array{
	 *   groups: array<int, array<string, mixed>>,
	 *   redirects: array<int, array<string, mixed>>,
	 *   settings: array<string, mixed>|null,
	 *   logs: array<int, array<string, mixed>>,
	 *   errors_404: array<int, array<string, mixed>>
	 * }|false
	 */
	public function parse( $data ) {
		/** @var array<string, mixed>|false $json */
		$json = json_decode( $data, true );
		if ( ! is_array( $json ) ) {
			return false;
		}

		$groups = [];

		if ( isset( $json['groups'] ) && is_array( $json['groups'] ) ) {
			foreach ( $json['groups'] as $json_group ) {
				if ( ! is_array( $json_group ) || ! isset( $json_group['id'] ) ) {
					continue;
				}

				$groups[ intval( $json_group['id'], 10 ) ] = $json_group;
			}
		}

		$redirects = [];

		if ( isset( $json['redirects'] ) && is_array( $json['redirects'] ) ) {
			foreach ( $json['redirects'] as $item ) {
				if ( ! is_array( $item ) ) {
					continue;
				}

				if ( isset( $item['match_type'] ) && $item['match_type'] === 'url' && isset( $item['action_data'] ) && ! is_array( $item['action_data'] ) ) {
					$item['action_data'] = [ 'url' => $item['action_data'] ];
				}

				$redirects[] = $item;
			}
		}

		$settings = null;
		if ( isset( $json['settings'] ) && is_array( $json['settings'] ) ) {
			$settings = $json['settings'];
		}

		$logs = [];
		if ( isset( $json['logs'] ) && is_array( $json['logs'] ) ) {
			foreach ( $json['logs'] as $item ) {
				if ( is_array( $item ) ) {
					$logs[] = $item;
				}
			}
		}

		$errors_404 = [];
		if ( isset( $json['errors_404'] ) && is_array( $json['errors_404'] ) ) {
			foreach ( $json['errors_404'] as $item ) {
				if ( is_array( $item ) ) {
					$errors_404[] = $item;
				}
			}
		}

		return [
			'groups' => $groups,
			'redirects' => $redirects,
			'settings' => $settings,
			'logs' => $logs,
			'errors_404' => $errors_404,
		];
	}
}
