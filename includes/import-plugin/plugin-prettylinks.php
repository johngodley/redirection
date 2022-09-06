<?php

namespace Redirection\Importer\Plugin;

use Redirection\Importer;
use Redirection\Redirect;

class PrettyLinks extends Importer\Plugin_Importer {
	public function import_plugin( $group_id ) {
		global $wpdb;

		$count = 0;
		$redirects = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}prli_links" );

		foreach ( $redirects as $redirect ) {
			$created = $this->create_for_item( $group_id, $redirect );

			if ( $created ) {
				$count++;
			}
		}

		return $count;
	}

	private function create_for_item( $group_id, $link ) {
		$item = array(
			'url'         => '/' . $link->slug,
			'action_data' => array( 'url' => $link->url ),
			'regex'       => false,
			'group_id'    => $group_id,
			'match_type'  => 'url',
			'action_type' => 'url',
			'title'       => $link->name,
			'action_code' => $link->redirect_type,
		);

		return Redirect\Redirect::create( $item );
	}

	public function get_data() {
		$data = get_option( 'prli_db_version' );

		if ( $data ) {
			global $wpdb;

			return [
				'id' => 'pretty-links',
				'name' => 'PrettyLinks',
				'total' => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}prli_links" ),
			];
		}

		return false;
	}
}
