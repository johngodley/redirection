<?php

namespace Redirection\Importer\Plugin;

use Redirection\Importer;
use Redirection\Redirect;

class Seo_Redirection extends Importer\Plugin_Importer {
	public function import_plugin( $group_id ) {
		global $wpdb;

		if ( defined( 'REDIRECTION_TESTS' ) && REDIRECTION_TESTS ) {
			return 0;
		}

		$count = 0;
		$redirects = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}WP_SEO_Redirection" );

		foreach ( $redirects as $redirect ) {
			$item = $this->create_for_item( $group_id, $redirect );

			if ( $item ) {
				$count++;
			}
		}

		return $count;
	}

	private function create_for_item( $group_id, $seo ) {
		if ( intval( $seo->enabled, 10 ) === 0 ) {
			return false;
		}

		$data = array(
			'url'         => $seo->regex ? $seo->regex : $seo->redirect_from,
			'action_data' => array( 'url' => $seo->redirect_to ),
			'regex'       => $seo->regex ? true : false,
			'group_id'    => $group_id,
			'match_type'  => 'url',
			'action_type' => 'url',
			'action_code' => intval( $seo->redirect_type, 10 ),
		);

		return Redirect\Redirect::create( $data );
	}

	public function get_data() {
		global $wpdb;

		$plugins = get_option( 'active_plugins', array() );
		$found = false;

		foreach ( $plugins as $plugin ) {
			if ( strpos( $plugin, 'seo-redirection.php' ) !== false ) {
				$found = true;
				break;
			}
		}

		if ( $found ) {
			$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}WP_SEO_Redirection" );

			return array(
				'id' => 'seo-redirection',
				'name' => 'SEO Redirection',
				'total' => $total,
			);
		}

		return false;
	}
}
