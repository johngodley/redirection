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
/**
 * @phpstan-type ImportPluginPayload array{
 *    plugin?: string|list<string>
 * }
 * @phpstan-type ImportFileParams array{
 *  file?: array{
 *      tmp_name: string,
 *      name: string,
 *      size: int,
 *      type: string,
 *      error: int
 *  }
 * }
 */
class Redirection_Api_Import extends Redirection_Api_Route {
	/**
	 * @param string $api_namespace REST namespace.
	 */
	public function __construct( $api_namespace ) {
		// POST /import/file/:group_id - Import from file upload
		register_rest_route(
			$api_namespace,
			'/import/file/(?P<group_id>\d+)',
			[
				[
					'methods' => WP_REST_Server::EDITABLE,
					'callback' => [ $this, 'route_import_file' ],
					'permission_callback' => [ $this, 'permission_callback_manage' ],
				],
			]
		);

		// GET/POST /import/plugin - List or import from plugins
		register_rest_route(
			$api_namespace,
			'/import/plugin',
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'route_plugin_import_list' ],
					'permission_callback' => [ $this, 'permission_callback_manage' ],
				],
				[
					'methods' => WP_REST_Server::EDITABLE,
					'callback' => [ $this, 'route_plugin_import' ],
					'permission_callback' => [ $this, 'permission_callback_manage' ],
				],
			]
		);
	}

	/**
	 * Permission callback used for import routes.
	 *
	 * @param WP_REST_Request $_request Request (unused).
	 * @phpstan-param WP_REST_Request<array<string, mixed>> $_request
	 * @return bool
	 */
	public function permission_callback_manage( WP_REST_Request $_request ) {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_IO_MANAGE );
	}

	/**
	 * List available plugin importers.
	 *
	 * @param WP_REST_Request $request Request.
	 * @phpstan-param WP_REST_Request<array<string, mixed>> $request
	 * @phpstan-return array{importers: array<int, mixed>}
	 * @return array{importers: array}
	 */
	public function route_plugin_import_list( WP_REST_Request $request ) {
		include_once dirname( __DIR__ ) . '/models/importer.php';

		return array( 'importers' => Red_Plugin_Importer::get_plugins() );
	}

	/**
	 * Import redirects using selected plugin importers.
	 *
	 * @param WP_REST_Request $request Request.
	 * @phpstan-param WP_REST_Request<array<string, mixed>> $request
	 * @phpstan-return array{imported: int}
	 * @return array{imported: int}
	 */
	public function route_plugin_import( WP_REST_Request $request ) {
		include_once dirname( __DIR__ ) . '/models/importer.php';

		$params = $request->get_params();
		/** @var ImportPluginPayload $params */
		$groups = Red_Group::get_all();
		/** @var array<array{id: int, name: string, redirects: int, module_id: int, moduleName: string, enabled: bool, default?: bool}> $groups */
		$plugin_param = $params['plugin'] ?? $request->get_param( 'plugin' );
		if ( is_array( $plugin_param ) ) {
			$plugins = array_map( 'strval', $plugin_param );
		} elseif ( $plugin_param === null ) {
			$plugins = [];
		} else {
			$plugins = [ (string) $plugin_param ];
		}
		/** @var list<string> $plugins */
		$plugins = array_map( 'sanitize_text_field', $plugins );
		$total = 0;

		foreach ( $plugins as $plugin ) {
			$total += Red_Plugin_Importer::import( $plugin, $groups[0]['id'] );
		}

		return [ 'imported' => $total ];
	}

	/**
	 * Import redirects from an uploaded file.
	 *
	 * @param WP_REST_Request $request Request.
	 * @phpstan-param WP_REST_Request<array<string, mixed>> $request
	 * @phpstan-return array{imported: int}|WP_Error
	 * @return array{imported: int}|WP_Error
	 */
	public function route_import_file( WP_REST_Request $request ) {
		$file_params = $request->get_file_params();
		/** @var ImportFileParams $file_params */
		$group_id = intval( $request['group_id'], 10 );

		if ( isset( $file_params['file'] ) ) {
			$upload = $file_params['file'];

			if ( is_uploaded_file( $upload['tmp_name'] ) ) {
				$count = Red_FileIO::import( $group_id, $upload );

				if ( $count > 0 ) {
					return array(
						'imported' => $count,
					);
				}

				return $this->add_error_details( new WP_Error( 'redirect_import_invalid_group', 'Invalid group' ), __LINE__ );
			}
		}

		return $this->add_error_details( new WP_Error( 'redirect_import_invalid_file', 'Invalid file' ), __LINE__ );
	}
}
