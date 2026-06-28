<?php

use Redirection\ImportExport\ImportService;

/**
 * @api {get} /redirection/v1/import/file/:group_id Import redirects
 * @apiName Import
 * @apiDescription Import redirects from CSV, JSON, or Apache .htaccess
 * @apiGroup Import/Export
 *
 * @apiParam (URL) {Integer} :group_id The group ID to import into
 * @apiParam (File) {File} file The multipart form upload containing the file to import
 *
 * @apiSuccess {Integer} created Number of new redirects created
 * @apiSuccess {Integer} updated Number of existing redirects updated
 * @apiSuccess {Integer} ignored Number of duplicate redirects ignored
 * @apiSuccess {Integer} groups_created Number of groups created during import
 * @apiSuccess {Object[]} preview First 20 preview rows from the imported file
 * @apiSuccess {String} preview.source Source URL
 * @apiSuccess {String} preview.target Target URL
 * @apiSuccess {Integer} preview.code HTTP code
 * @apiSuccess {Boolean} preview.regex Whether the redirect is regex-based
 * @apiSuccess {String} preview.group Target group name
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
 *    plugin?: string|list<string>,
 *    group_id?: int|string,
 *    dry_run?: bool|string|int,
 *    duplicate_mode?: string,
 *    delete_source?: bool|string|int
 * }
 * @phpstan-type ImportFileParams array{
 *  dry_run?: bool|string|int,
 *  duplicate_mode?: string,
 *  deduplicate?: bool|string|int,
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
	 * @param non-falsy-string $api_namespace REST namespace.
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
					'args' => [
						'dry_run' => [
							'sanitize_callback' => [ $this, 'sanitize_boolean_param' ],
							'validate_callback' => [ $this, 'validate_boolean_param' ],
						],
						'duplicate_mode' => [
							'sanitize_callback' => [ $this, 'sanitize_duplicate_mode_param' ],
							'validate_callback' => [ $this, 'validate_duplicate_mode_param' ],
						],
						'deduplicate' => [
							'sanitize_callback' => [ $this, 'sanitize_boolean_param' ],
							'validate_callback' => [ $this, 'validate_boolean_param' ],
						],
					],
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
					'args' => [
						'delete_source' => [
							'sanitize_callback' => [ $this, 'sanitize_boolean_param' ],
							'validate_callback' => [ $this, 'validate_boolean_param' ],
						],
						'duplicate_mode' => [
							'sanitize_callback' => [ $this, 'sanitize_duplicate_mode_param' ],
							'validate_callback' => [ $this, 'validate_duplicate_mode_param' ],
						],
					],
				],
			]
		);

		register_rest_route(
			$api_namespace,
			'/import/plugin/(?P<plugin>[a-z0-9-]+)/preview',
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'route_plugin_preview' ],
					'permission_callback' => [ $this, 'permission_callback_manage' ],
					'args' => [
						'delete_source' => [
							'sanitize_callback' => [ $this, 'sanitize_boolean_param' ],
							'validate_callback' => [ $this, 'validate_boolean_param' ],
						],
						'duplicate_mode' => [
							'sanitize_callback' => [ $this, 'sanitize_duplicate_mode_param' ],
							'validate_callback' => [ $this, 'validate_duplicate_mode_param' ],
						],
					],
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
	 * @phpstan-return array{
	 *   created: int,
	 *   updated: int,
	 *   ignored: int,
	 *   groups_created: int,
	 *   preview: array<int, array{
	 *     source: string,
	 *     target: string,
	 *     code: int,
	 *     regex: bool,
	 *     group: string,
	 *     result: 'created'|'updated'|'ignored',
	 *     redirect_id?: int
	 *   }>
	 * }|WP_Error
	 */
	public function route_plugin_import( WP_REST_Request $request ) {
		include_once dirname( __DIR__ ) . '/models/importer.php';

		$params = $request->get_params();
		/** @var ImportPluginPayload $params */
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
		$group_id = isset( $params['group_id'] ) ? intval( $params['group_id'], 10 ) : 0;
		$options = [
			'duplicate_mode' => isset( $params['duplicate_mode'] ) ? $this->sanitize_duplicate_mode_param( $params['duplicate_mode'] ) : 'import',
			'delete_source' => isset( $params['delete_source'] ) ? $this->sanitize_boolean_param( $params['delete_source'] ) : false,
		];
		$total = [
			'created' => 0,
			'updated' => 0,
			'ignored' => 0,
			'groups_created' => 0,
			'preview' => [],
		];

		$group = Red_Group::get( $group_id );
		if ( $group === false ) {
			return $this->add_error_details(
				new WP_Error( 'redirect_import_invalid_group', 'Invalid group' ),
				__LINE__
			);
		}

		foreach ( $plugins as $plugin ) {
			$result = Red_Plugin_Importer::import( $plugin, $group_id, $options );
			$total['created'] += $result['created'];
			$total['updated'] += $result['updated'];
			$total['ignored'] += $result['ignored'];
			$total['groups_created'] += $result['groups_created'];
		}

		return $total;
	}

	/**
	 * Preview redirects using a selected plugin importer.
	 *
	 * @param WP_REST_Request $request Request.
	 * @phpstan-param WP_REST_Request<array<string, mixed>> $request
	 * @phpstan-return array{
	 *   created: int,
	 *   updated: int,
	 *   ignored: int,
	 *   groups_created: int,
	 *   preview: array<int, array{
	 *     source: string,
	 *     target: string,
	 *     code: int,
	 *     regex: bool,
	 *     group: string,
	 *     result: 'created'|'updated'|'ignored',
	 *     redirect_id?: int
	 *   }>
	 * }|WP_Error
	 */
	public function route_plugin_preview( WP_REST_Request $request ) {
		include_once dirname( __DIR__ ) . '/models/importer.php';

		$params = $request->get_params();
		$plugin = sanitize_text_field( strval( $request->get_param( 'plugin' ) ) );
		$group_id = isset( $params['group_id'] ) ? intval( $params['group_id'], 10 ) : 0;
		$options = [
			'duplicate_mode' => isset( $params['duplicate_mode'] ) ? $this->sanitize_duplicate_mode_param( $params['duplicate_mode'] ) : 'import',
			'delete_source' => isset( $params['delete_source'] ) ? $this->sanitize_boolean_param( $params['delete_source'] ) : false,
			'dry_run' => true,
		];

		$group = Red_Group::get( $group_id );
		if ( $group === false ) {
			return $this->add_error_details(
				new WP_Error( 'redirect_import_invalid_group', 'Invalid group' ),
				__LINE__
			);
		}

		return Red_Plugin_Importer::preview( $plugin, $group_id, $options );
	}

	/**
	 * Import redirects from an uploaded file.
	 *
	 * @param WP_REST_Request $request Request.
	 * @phpstan-param WP_REST_Request<array<string, mixed>> $request
	 * @phpstan-return array{
	 *   created: int,
	 *   updated: int,
	 *   ignored: int,
	 *   groups_created: int,
	 *   preview: array<int, array{
	 *     source: string,
	 *     target: string,
	 *     code: int,
	 *     regex: bool,
	 *     group: string,
	 *     result: 'created'|'updated'|'ignored',
	 *     redirect_id?: int
	 *   }>
	 * }|WP_Error
	 */
	public function route_import_file( WP_REST_Request $request ) {
		$file_params = $request->get_file_params();
		/** @var ImportFileParams $file_params */
		$params = $request->get_params();
		/** @var ImportFileParams $params */
		$group_id = intval( $request['group_id'], 10 );
		$options = [
			'dry_run' => isset( $params['dry_run'] ) ? $this->sanitize_boolean_param( $params['dry_run'] ) : false,
			'duplicate_mode' => isset( $params['duplicate_mode'] ) ? $this->sanitize_duplicate_mode_param( $params['duplicate_mode'] ) : 'import',
		];

		if ( ! isset( $params['duplicate_mode'] ) && isset( $params['deduplicate'] ) && $this->sanitize_boolean_param( $params['deduplicate'] ) ) {
			$options['duplicate_mode'] = 'update';
		}

		if ( ! isset( $file_params['file'] ) || ! is_uploaded_file( $file_params['file']['tmp_name'] ) ) {
			return $this->add_error_details( new WP_Error( 'redirect_import_invalid_file', 'Invalid file upload' ), __LINE__ );
		}

		$upload = $file_params['file'];
		$parts = pathinfo( $upload['name'] );
		$extension = isset( $parts['extension'] ) ? strtolower( $parts['extension'] ) : '';

		// JSON imports don't need a group, but all other formats do
		if ( $extension !== 'json' ) {
			$group = Red_Group::get( $group_id );
			if ( $group === false ) {
				return $this->add_error_details( new WP_Error( 'redirect_import_invalid_group', 'Invalid group' ), __LINE__ );
			}
		}

		$result = ( new ImportService() )->import( $group_id, $upload, $options );

		// Import failure returns 0, but 0 can also mean no valid redirects in file
		// For JSON files, pre-validate to distinguish between invalid JSON and empty/no-redirects
		if ( $result['created'] === 0 && $result['updated'] === 0 && $extension === 'json' ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file read
			$content = file_get_contents( $upload['tmp_name'] );
			if ( $content !== false ) {
				json_decode( $content, true );
				if ( json_last_error() !== JSON_ERROR_NONE ) {
					return $this->add_error_details(
						new WP_Error( 'redirect_import_invalid_json', 'Invalid JSON file: ' . json_last_error_msg() ),
						__LINE__
					);
				}
			}
		}

		return $result;
	}

	/**
	 * @param mixed $value Parameter value.
	 * @return bool
	 */
	public function sanitize_boolean_param( $value ) {
		if ( is_bool( $value ) ) {
			return $value;
		}

		if ( is_int( $value ) || is_string( $value ) ) {
			return in_array( strtolower( (string) $value ), [ '1', 'true' ], true );
		}

		return false;
	}

	/**
	 * @param mixed $value Parameter value.
	 * @param WP_REST_Request $request Request.
	 * @param string $param Parameter name.
	 * @return bool
	 */
	public function validate_boolean_param( $value, WP_REST_Request $request, $param ) {
		unset( $request, $param );

		if ( is_bool( $value ) ) {
			return true;
		}

		if ( is_int( $value ) || is_string( $value ) ) {
			return in_array( strtolower( (string) $value ), [ '1', '0', 'true', 'false' ], true );
		}

		return false;
	}

	/**
	 * @param mixed $value Parameter value.
	 * @return 'import'|'ignore'|'update'
	 */
	public function sanitize_duplicate_mode_param( $value ) {
		if ( is_string( $value ) && in_array( $value, [ 'import', 'ignore', 'update' ], true ) ) {
			return $value;
		}

		return 'import';
	}

	/**
	 * @param mixed $value Parameter value.
	 * @param WP_REST_Request $request Request.
	 * @param string $param Parameter name.
	 * @return bool
	 */
	public function validate_duplicate_mode_param( $value, WP_REST_Request $request, $param ) {
		unset( $request, $param );

		return is_string( $value ) && in_array( $value, [ 'import', 'ignore', 'update' ], true );
	}
}
