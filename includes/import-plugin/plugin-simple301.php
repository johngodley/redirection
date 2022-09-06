<?php

namespace Redirection\Importer\Plugin;

use Redirection\Importer;
use Redirection\Redirect;

class Simple_301 extends Importer\Plugin_Importer {
	public function import_plugin( $group_id ) {
		$redirects = get_option( '301_redirects' );
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
			'url'         => str_replace( '*', '(.*?)', $source ),
			'action_data' => array( 'url' => str_replace( '*', '$1', trim( $target ) ) ),
			'regex'       => strpos( $source, '*' ) === false ? false : true,
			'group_id'    => $group_id,
			'match_type'  => 'url',
			'action_type' => 'url',
			'action_code' => 301,
		);

		return Redirect\Redirect::create( $item );
	}

	public function get_data() {
		$data = get_option( '301_redirects' );

		if ( $data ) {
			return array(
				'id' => 'wp-simple-redirect',
				'name' => 'Simple 301 Redirects',
				'total' => count( $data ),
			);
		}

		return false;
	}
}
