<?php

namespace Redirection\Importer\Plugin;

use Redirection\Importer;
use Redirection\Redirect;

class Rank_Math extends Importer\Plugin_Importer {
	public function import_plugin( $group_id ) {
		global $wpdb;

		$count = 0;
		$redirects = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}rank_math_redirections" );

		foreach ( $redirects as $redirect ) {
			$created = $this->create_for_item( $group_id, $redirect );
			$count += $created;
		}

		return $count;
	}

	private function create_for_item( $group_id, $redirect ) {
		// phpcs:ignore
		$sources = unserialize( $redirect->sources );
		$items = [];

		foreach ( $sources as $source ) {
			$url = $source['pattern'];
			if ( substr( $url, 0, 1 ) !== '/' ) {
				$url = '/' . $url;
			}

			$data = array(
				'url'         => $url,
				'action_data' => array( 'url' => str_replace( '\\\\', '\\', $redirect->url_to ) ),
				'regex'       => $source['comparison'] === 'regex' ? true : false,
				'group_id'    => $group_id,
				'match_type'  => 'url',
				'action_type' => 'url',
				'action_code' => $redirect->header_code,
			);

			$items[] = Redirect\Redirect::create( $data );
		}

		return count( $items );
	}

	public function get_data() {
		global $wpdb;

		if ( defined( 'REDIRECTION_TESTS' ) && REDIRECTION_TESTS ) {
			return 0;
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$total = 0;
		if ( is_plugin_active( 'seo-by-rank-math/rank-math.php' ) ) {
			$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}rank_math_redirections" );
		}

		if ( $total ) {
			return array(
				'id' => 'rank-math',
				'name' => 'RankMath',
				'total' => intval( $total, 10 ),
			);
		}

		return 0;
	}
}
