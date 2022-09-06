<?php

namespace Redirection\Importer\Plugin;

use Redirection\Importer;
use Redirection\Redirect;

class Quick_Redirect extends Importer\Plugin_Importer {
	public function import_plugin( $group_id ) {
		$redirects = get_option( 'quickppr_redirects' );
		$count = 0;

		foreach ( $redirects as $source => $target ) {
			$item = $this->create_for_item( $group_id, $source, $target );

			if ( $item ) {
				$count++;
			}
		}

		return $count;
	}

	private function create_for_item( $group_id, $source, $target ) {
		$item = array(
			'url'         => $source,
			'action_data' => array( 'url' => $target ),
			'regex'       => false,
			'group_id'    => $group_id,
			'match_type'  => 'url',
			'action_type' => 'url',
			'action_code' => 301,
		);

		return Redirect\Redirect::create( $item );
	}

	public function get_data() {
		$data = get_option( 'quickppr_redirects' );

		if ( $data ) {
			return array(
				'id' => 'quick-redirects',
				'name' => 'Quick Page/Post Redirects',
				'total' => count( $data ),
			);
		}

		return false;
	}
}
