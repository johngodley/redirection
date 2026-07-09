<?php

use Redirection\ImportExport\ImportService;
use Redirection\ImportExport\Importer\PluginRegistry;
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
 *  import_sections?: list<string>|string,
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
					'permission_callback' => [ $this, 'permission_callback_file_import' ],
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
						'import_sections' => [
							'sanitize_callback' => [ $this, 'sanitize_import_sections_param' ],
							'validate_callback' => [ $this, 'validate_import_sections_param' ],
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
	 * Permission callback for file imports.
	 *
	 * Redirect-only imports stay under IO permissions. JSON imports that include
	 * additional sections require the relevant capability for that data type.
	 *
	 * @param WP_REST_Request $request Request.
	 * @phpstan-param WP_REST_Request<array<string, mixed>> $request
	 * @return bool
	 */
	public function permission_callback_file_import( WP_REST_Request $request ) {
		if ( ! $this->permission_callback_manage( $request ) ) {
			return false;
		}

		$sections = $this->sanitize_import_sections_param( $request->get_param( 'import_sections' ) );

		return $this->has_import_section_permissions( $sections );
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
		return array( 'importers' => PluginRegistry::get_plugins() );
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
	 *   groups_updated: int,
	 *   groups_ignored: int,
	 *   logs_imported: int,
	 *   errors_imported: int,
	 *   settings_imported: int,
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
			'groups_updated' => 0,
			'groups_ignored' => 0,
			'logs_imported' => 0,
			'errors_imported' => 0,
			'settings_imported' => 0,
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
			$result = PluginRegistry::import( $plugin, $group_id, $options );
			$total['created'] += $result['created'];
			$total['updated'] += $result['updated'];
			$total['ignored'] += $result['ignored'];
			$total['groups_created'] += $result['groups_created'];
			$total['groups_updated'] += $result['groups_updated'];
			$total['groups_ignored'] += $result['groups_ignored'];
			$total['logs_imported'] += $result['logs_imported'];
			$total['errors_imported'] += $result['errors_imported'];
			$total['settings_imported'] += $result['settings_imported'];
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
	 *   groups_updated: int,
	 *   groups_ignored: int,
	 *   logs_imported: int,
	 *   errors_imported: int,
	 *   settings_imported: int,
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

		return PluginRegistry::preview( $plugin, $group_id, $options );
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
	 *   groups_updated: int,
	 *   groups_ignored: int,
	 *   logs_imported: int,
	 *   errors_imported: int,
	 *   settings_imported: int,
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
			'import_sections' => isset( $params['import_sections'] ) ? $this->sanitize_import_sections_param( $params['import_sections'] ) : [],
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

		if ( $extension === 'json' && ! $this->has_json_import_permissions( $upload['tmp_name'], $options['import_sections'] ) ) {
			return $this->get_forbidden_error();
		}

		$result = ( new ImportService() )->import( $group_id, $upload, $options );

		// Import failure returns 0, but 0 can also mean no valid redirects in file
		// For JSON files, pre-validate to distinguish between invalid JSON and empty/no-redirects
		if (
			$result['created'] === 0 &&
			$result['updated'] === 0 &&
			$result['groups_created'] === 0 &&
			$result['groups_updated'] === 0 &&
			$result['groups_ignored'] === 0 &&
			$result['logs_imported'] === 0 &&
			$result['errors_imported'] === 0 &&
			$result['settings_imported'] === 0 &&
			$extension === 'json'
		) {
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

	/**
	 * @param mixed $value Parameter value.
	 * @return list<string>
	 */
	public function sanitize_import_sections_param( $value ) {
		if ( is_string( $value ) ) {
			$values = array_map( 'trim', explode( ',', $value ) );
		} else {
			$values = is_array( $value ) ? $value : [ $value ];
		}

		$allowed = [ 'settings', 'groups', 'redirects', 'logs', 'errors_404' ];
		$sections = [];

		foreach ( $values as $section ) {
			if ( is_string( $section ) && in_array( $section, $allowed, true ) ) {
				$sections[] = $section;
			}
		}

		return array_values( array_unique( $sections ) );
	}

	/**
	 * @param mixed $value Parameter value.
	 * @param WP_REST_Request $request Request.
	 * @param string $param Parameter name.
	 * @return bool
	 */
	public function validate_import_sections_param( $value, WP_REST_Request $request, $param ) {
		unset( $request, $param );

		if ( is_string( $value ) ) {
			$values = array_filter(
				array_map( 'trim', explode( ',', $value ) ),
				static function ( $val ) {
					return strlen( $val ) > 0;
				}
			);

			if ( count( $values ) === 0 ) {
				return false;
			}

			return count( $values ) === count( $this->sanitize_import_sections_param( $value ) );
		}

		if ( ! is_array( $value ) ) {
			return false;
		}

		foreach ( $value as $section ) {
			if ( ! is_string( $section ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param list<string> $sections
	 * @return bool
	 */
	private function has_import_section_permissions( array $sections ) {
		foreach ( $sections as $section ) {
			if ( $section === 'settings' && ! $this->can_manage_settings() ) {
				return false;
			}

			if ( $section === 'groups' && ! Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_GROUP_ADD ) ) {
				return false;
			}

			if ( $section === 'logs' && ! Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_LOG_MANAGE ) ) {
				return false;
			}

			if ( $section === 'errors_404' && ! Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_404_MANAGE ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param string $filename
	 * @param list<string> $requested_sections
	 * @return bool
	 */
	private function has_json_import_permissions( $filename, array $requested_sections ) {
		$sections = $requested_sections;

		if ( count( $sections ) === 0 ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file read
			$content = file_get_contents( $filename );
			if ( $content !== false ) {
				$decoded = json_decode( $content, true );

				if ( is_array( $decoded ) ) {
					$sections = [];

					foreach ( [ 'settings', 'groups', 'redirects', 'logs', 'errors_404' ] as $section ) {
						if ( array_key_exists( $section, $decoded ) ) {
							$sections[] = $section;
						}
					}
				}
			}
		}

		return $this->has_import_section_permissions( $sections );
	}

	/**
	 * @return bool
	 */
	private function can_manage_settings() {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_OPTION_MANAGE ) ||
			Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_SITE_MANAGE );
	}

	/**
	 * @return WP_Error
	 */
	private function get_forbidden_error() {
		return new WP_Error(
			'rest_forbidden',
			__( 'Sorry, you are not allowed to do that.' ),
			[ 'status' => rest_authorization_required_code() ]
		);
	}
}
