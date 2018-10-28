<?php

class Redirection_Api_Import extends Redirection_Api_Route {
	public function __construct( $namespace ) {
		register_rest_route( $namespace, '/import/file/(?P<group_id>\d+)', array(
			$this->get_route( WP_REST_Server::EDITABLE, 'route_import_file' ),
		) );

		register_rest_route( $namespace, '/import/plugin', array(
			$this->get_route( WP_REST_Server::READABLE, 'route_plugin_import_list' ),
		) );

		register_rest_route( $namespace, '/import/plugin/(?P<plugin>.*?)', array(
			$this->get_route( WP_REST_Server::EDITABLE, 'route_plugin_import' ),
		) );
	}

	public function route_plugin_import_list( WP_REST_Request $request ) {
		include_once dirname( dirname( __FILE__ ) ) . '/models/importer.php';

		return array( 'importers' => Red_Plugin_Importer::get_plugins() );
	}

	public function route_plugin_import( WP_REST_Request $request ) {
		include_once dirname( dirname( __FILE__ ) ) . '/models/importer.php';

		$groups = Red_Group::get_all();

		return array( 'imported' => Red_Plugin_Importer::import( $request['plugin'], $groups[0]['id'] ) );
	}

	public function route_import_file( WP_REST_Request $request ) {
		$upload = $request->get_file_params();
		$upload = isset( $upload['file'] ) ? $upload['file'] : false;
		$group_id = $request['group_id'];

		if ( $upload && is_uploaded_file( $upload['tmp_name'] ) ) {
			$count = Red_FileIO::import( $group_id, $upload );

			if ( $count !== false ) {
				return array(
					'imported' => $count,
				);
			}

			return $this->add_error_details( new WP_Error( 'redirect', 'Invalid group' ), __LINE__ );
		}

		return $this->add_error_details( new WP_Error( 'redirect', 'Invalid file' ), __LINE__ );
	}

}
