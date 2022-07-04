<?php

/**
 * @api {get} /redirection/v1/import/file/:group_id Import redirects
 * @apiName Import
 * @apiDescription Import redirects from CSV, JSON, or Apache .htaccess
 * @apiGroup Import/Export
 *
 * @apiParam (URL) {Integer} :group_id The group ID to import into
 * @apiParam (File) {File} file The multipart form upload containing the file to import
 *
 * @apiSuccess {Integer} imported Number of items imported
 *
 * @apiUse 401Error
 * @apiUse 404Error
 * @apiError (Error 400) redirect_import_invalid_group Invalid group
 * @apiErrorExample {json} 404 Error Response:
 *     HTTP/1.1 400 Bad Request
 *     {
 *       "code": "redirect_import_invalid_group",
 *       "message": "Invalid group"
 *     }
 * @apiError (Error 400) redirect_import_invalid_file Invalid file upload
 * @apiErrorExample {json} 404 Error Response:
 *     HTTP/1.1 400 Bad Request
 *     {
 *       "code": "redirect_import_invalid_file",
 *       "message": "Invalid file upload"
 *     }
 */
class Redirection_Api_Import extends Redirection_Api_Route {
	public function __construct( $namespace ) {
		register_rest_route( $namespace, '/import/file/(?P<group_id>\d+)', array(
			$this->get_route( WP_REST_Server::EDITABLE, 'route_import_file', [ $this, 'permission_callback_manage' ] ),
		) );

		register_rest_route( $namespace, '/import/plugin', array(
			$this->get_route( WP_REST_Server::READABLE, 'route_plugin_import_list', [ $this, 'permission_callback_manage' ] ),
		) );

		register_rest_route( $namespace, '/import/plugin', array(
			$this->get_route( WP_REST_Server::EDITABLE, 'route_plugin_import', [ $this, 'permission_callback_manage' ] ),
		) );
	}

	public function permission_callback_manage( WP_REST_Request $request ) {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_IO_MANAGE );
	}

	public function route_plugin_import_list( WP_REST_Request $request ) {
		include_once dirname( __DIR__ ) . '/models/importer.php';

		return array( 'importers' => Red_Plugin_Importer::get_plugins() );
	}

	public function route_plugin_import( WP_REST_Request $request ) {
		include_once dirname( __DIR__ ) . '/models/importer.php';

		$params = $request->get_params( $request );
		$groups = Red_Group::get_all();
		$plugins = is_array( $request['plugin'] ) ? $request['plugin'] : [ $request['plugin'] ];
		$total = 0;

		foreach ( $plugins as $plugin ) {
			$total += Red_Plugin_Importer::import( $plugin, $groups[0]['id'] );
		}

		return [ 'imported' => $total ];
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

			return $this->add_error_details( new WP_Error( 'redirect_import_invalid_group', 'Invalid group' ), __LINE__ );
		}

		return $this->add_error_details( new WP_Error( 'redirect_import_invalid_file', 'Invalid file' ), __LINE__ );
	}

}
