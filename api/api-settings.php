<?php
/**
 * @api {get} /redirection/v1/setting Get settings
 * @apiName GetSettings
 * @apiDescription Get all settings for Redirection. This includes user-configurable settings, as well as necessary WordPress settings.
 * @apiGroup Settings
 *
 * @apiUse SettingItem
 * @apiUse 401Error
 * @apiUse 404Error
 */

/**
 * @api {post} /redirection/v1/setting Update settings
 * @apiName UpdateSettings
 * @apiDescription Update Redirection settings. Note you can do partial updates, and only the values specified will be changed.
 * @apiGroup Settings
 *
 * @apiParam {Object} settings An object containing all the settings to update
 * @apiParamExample {json} settings:
 *     {
 *       "expire_redirect": 14,
 *       "https": false
 *     }
 *
 * @apiUse SettingItem
 * @apiUse 401Error
 * @apiUse 404Error
 */

/**
 * @apiDefine SettingItem Settings
 * Redirection settings
 *
 * @apiSuccess {Object[]} settings An object containing all settings
 * @apiSuccess {String} settings.expire_redirect
 * @apiSuccess {String} settings.token
 * @apiSuccess {String} settings.monitor_post
 * @apiSuccess {String} settings.monitor_types
 * @apiSuccess {String} settings.associated_redirect
 * @apiSuccess {String} settings.auto_target
 * @apiSuccess {String} settings.expire_redirect
 * @apiSuccess {String} settings.expire_404
 * @apiSuccess {String} settings.modules
 * @apiSuccess {String} settings.newsletter
 * @apiSuccess {String} settings.redirect_cache
 * @apiSuccess {String} settings.ip_logging
 * @apiSuccess {String} settings.last_group_id
 * @apiSuccess {String} settings.rest_api
 * @apiSuccess {String} settings.https
 * @apiSuccess {String} settings.headers
 * @apiSuccess {String} settings.database
 * @apiSuccess {String} settings.relcoate Relocate this site to the specified domain (and path)
 * @apiSuccess {String="www","nowww",""} settings.preferred_domain Preferred canonical domain
 * @apiSuccess {String[]} settings.aliases Array of domains that will be redirected to the current WordPress site
 * @apiSuccess {Object[]} groups An array of groups
 * @apiSuccess {String} groups.label Name of the group
 * @apiSuccess {Integer} groups.value Group ID
 * @apiSuccess {String} installed The path that WordPress is installed in
 * @apiSuccess {Boolean} canDelete True if Redirection can be deleted, false otherwise (on multisite, for example)
 * @apiSuccess {String[]} post_types Array of WordPress post types
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "settings": {
 *         "expire_redirect": 7,
 *         "https": true
 *       },
 *       "groups": [
 *          { label: 'My group', value: 5 }
 *       ],
 *       "installed": "/var/html/wordpress",
 *       "canDelete": true,
 *       "post_types": [
 *         "post",
 *         "page"
 *       ]
 *     }
 */

class Redirection_Api_Settings extends Redirection_Api_Route {
	public function __construct( $namespace ) {
		register_rest_route( $namespace, '/setting', array(
			$this->get_route( WP_REST_Server::READABLE, 'route_settings', [ $this, 'permission_callback_manage' ] ),
			$this->get_route( WP_REST_Server::EDITABLE, 'route_save_settings', [ $this, 'permission_callback_manage' ] ),
		) );
	}

	public function route_settings( WP_REST_Request $request ) {
		if ( ! function_exists( 'get_home_path' ) ) {
			include_once ABSPATH . '/wp-admin/includes/file.php';
		}

		return [
			'settings' => red_get_options(),
			'groups' => $this->groups_to_json( Red_Group::get_for_select() ),
			'installed' => get_home_path(),
			'canDelete' => ! is_multisite(),
			'post_types' => red_get_post_types(),
		];
	}

	public function permission_callback_manage( WP_REST_Request $request ) {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_OPTION_MANAGE ) || Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_SITE_MANAGE );
	}

	public function route_save_settings( WP_REST_Request $request ) {
		$params = $request->get_params();
		$result = true;

		if ( isset( $params['location'] ) && strlen( $params['location'] ) > 0 ) {
			$module = Red_Module::get( 2 );
			$result = $module->can_save( $params['location'] );
		}

		red_set_options( $params );

		$settings = $this->route_settings( $request );
		if ( is_wp_error( $result ) ) {
			$settings['warning'] = $result->get_error_message();
		}

		return $settings;
	}

	private function groups_to_json( $groups, $depth = 0 ) {
		$items = array();

		foreach ( $groups as $text => $value ) {
			if ( is_array( $value ) && $depth === 0 ) {
				$items[] = (object) array(
					'label' => $text,
					'value' => $this->groups_to_json( $value, 1 ),
				);
			} else {
				$items[] = (object) array(
					'label' => $value,
					'value' => $text,
				);
			}
		}

		return $items;
	}
}
