<?php

/**
 * 'Plugin' functions for Redirection
 */
class Redirection_Api_Plugin extends Redirection_Api_Route {
	public function __construct( $namespace ) {
		register_rest_route( $namespace, '/plugin', array(
			$this->get_route( WP_REST_Server::READABLE, 'route_status' ),
		) );

		register_rest_route( $namespace, '/plugin', array(
			$this->get_route( WP_REST_Server::EDITABLE, 'route_fixit' ),
			'args' => [
				'name' => array(
					'description' => 'Name',
					'type' => 'string',
				),
				'value' => array(
					'description' => 'Value',
					'type' => 'string',
				),
			],
		) );

		register_rest_route( $namespace, '/plugin/delete', array(
			$this->get_route( WP_REST_Server::EDITABLE, 'route_delete' ),
		) );

		register_rest_route( $namespace, '/plugin/test', array(
			$this->get_route( WP_REST_Server::ALLMETHODS, 'route_test' ),
		) );

		register_rest_route( $namespace, '/plugin/post', array(
			$this->get_route( WP_REST_Server::READABLE, 'route_match_post' ),
			'args' => [
				'text' => [
					'description' => 'Text to match',
					'type' => 'string',
				],
			],
		) );

		register_rest_route( $namespace, '/plugin/database', array(
			$this->get_route( WP_REST_Server::EDITABLE, 'route_database' ),
			'args' => array(
				'description' => 'Upgrade parameter',
				'type' => 'enum',
				'enum' => array(
					'stop',
					'skip',
				),
			),
		) );
	}

	public function route_match_post( WP_REST_Request $request ) {
		$params = $request->get_params();
		$search = isset( $params['text'] ) ? $params['text'] : false;
		$results = [];

		if ( $search ) {
			global $wpdb;

			$posts = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT ID,post_title,post_name FROM $wpdb->posts WHERE post_status='publish' AND (post_title LIKE %s OR post_name LIKE %s)",
					'%' . $wpdb->esc_like( $search ) . '%', '%' . $wpdb->esc_like( $search ) . '%'
				)
			);

			foreach ( (array) $posts as $post ) {
				$results[] = [
					'title' => $post->post_title,
					'slug' => $post->post_name,
					'url' => get_permalink( $post->ID ),
				];
			}
		}

		return $results;
	}

	public function route_status( WP_REST_Request $request ) {
		include_once dirname( REDIRECTION_FILE ) . '/models/fixer.php';

		$fixer = new Red_Fixer();
		return $fixer->get_json();
	}

	public function route_fixit( WP_REST_Request $request ) {
		include_once dirname( REDIRECTION_FILE ) . '/models/fixer.php';

		$params = $request->get_params();
		$fixer = new Red_Fixer();

		if ( isset( $params['name'] ) && isset( $params['value'] ) ) {
			$fixer->save_debug( $params['name'], $params['value'] );
		} else {
			$fixer->fix( $fixer->get_status() );
		}

		return $fixer->get_json();
	}

	public function route_delete() {
		if ( is_multisite() ) {
			return $this->getError( 'Multisite installations must delete the plugin from the network admin', __LINE__ );
		}

		$plugin = Redirection_Admin::init();
		$plugin->plugin_uninstall();

		$current = get_option( 'active_plugins' );
		array_splice( $current, array_search( basename( dirname( REDIRECTION_FILE ) ) . '/' . basename( REDIRECTION_FILE ), $current ), 1 );
		update_option( 'active_plugins', $current );

		return array( 'location' => admin_url() . 'plugins.php' );
	}

	public function route_test( WP_REST_Request $request ) {
		return array(
			'success' => true,
		);
	}

	public function route_database( WP_REST_Request $request ) {
		$params = $request->get_params();
		$status = new Red_Database_Status();
		$upgrade = false;

		if ( isset( $params['upgrade'] ) && in_array( $params['upgrade'], [ 'stop', 'skip' ], true ) ) {
			$upgrade = $params['upgrade'];
		}

		// Check upgrade
		if ( ! $status->needs_updating() && ! $status->needs_installing() ) {
			/* translators: version number */
			$status->set_error( sprintf( __( 'Your database does not need updating to %s.', 'redirection' ), REDIRECTION_DB_VERSION ) );

			return $status->get_json();
		}

		if ( $upgrade === 'stop' ) {
			$status->stop_update();
		} elseif ( $upgrade === 'skip' ) {
			$status->set_next_stage();
		}

		if ( $upgrade === false || $status->get_current_stage() ) {
			$database = new Red_Database();
			$database->apply_upgrade( $status );
		}

		return $status->get_json();
	}
}
