<?php

/**
 * Redirection capabilities
 *
 * Pre 4.6
 * =======
 * Hook `redirection_role` and return a capability. This gives access to the entire plugin
 *
 * Post 4.6
 * ========
 * Hook `redirection_role` and return a capability for access to the plugin menu. For example `edit_pages` will allow an editor
 * Hook `redirection_capability` and return a different capability for each check that needs specific permissions.
 *
 * For example, if you want to give editors access to create redirects, but nothing else:
 *
 * ```php
 * add_filter( 'redirection_capability_check', function( $capability, $permission_name ) {
 *     if ( $permission_name === 'redirection_cap_redirect_manage' || $permission_name === 'redirection_cap_redirect_add' ) {
 *         return $capability;
 *     }
 *
 *     return 'manage_options';
 * } );
 * ```
 *
 * Always default to restrictive and then grant permissions. Don't default to permissive and remove permissions. This way if a new
 * capability is added your users won't automatically be granted access.
 *
 * Capabilities can be filtered with:
 * - `redirection_capability_check( $capability, $permission_name )` - override `$capability` dependant on `$permission_name`
 * - `redirection_capability_pages( $pages )` - filters the list of available pages
 * - `redirection_role( $cap )` - return the role/capability used for overall access to the plugin
 *
 * Note some capabilities may give access to data from others. For example, when viewing a page of redirects via `redirection_cap_redirect_manage`
 * the client will need to access group data.
 */
class Redirection_Capabilities {
	const FILTER_ALL = 'redirection_capability_all';
	const FILTER_PAGES = 'redirection_capability_pages';
	const FILTER_CAPABILITY = 'redirection_capability_check';

	// The default WordPress capability used for all checks
	const CAP_DEFAULT = 'manage_options';

	// The main capability used to provide access to the plugin
	const CAP_PLUGIN = 'redirection_role';

	// These capabilities are combined with `redirection_cap_` to form `redirection_cap_redirect_add` etc
	const CAP_REDIRECT_MANAGE = 'redirection_cap_redirect_manage';
	const CAP_REDIRECT_ADD = 'redirection_cap_redirect_add';
	const CAP_REDIRECT_DELETE = 'redirection_cap_redirect_delete';

	const CAP_GROUP_MANAGE = 'redirection_cap_group_manage';
	const CAP_GROUP_ADD = 'redirection_cap_group_add';
	const CAP_GROUP_DELETE = 'redirection_cap_group_delete';

	const CAP_404_MANAGE = 'redirection_cap_404_manage';
	const CAP_404_DELETE = 'redirection_cap_404_delete';

	const CAP_LOG_MANAGE = 'redirection_cap_log_manage';
	const CAP_LOG_DELETE = 'redirection_cap_log_delete';

	const CAP_IO_MANAGE = 'redirection_cap_io_manage';

	const CAP_OPTION_MANAGE = 'redirection_cap_option_manage';

	const CAP_SUPPORT_MANAGE = 'redirection_cap_support_manage';

	const CAP_SITE_MANAGE = 'redirection_cap_site_manage';

	/**
	 * Determine if the current user has access to a named capability.
	 *
	 * @param string $cap_name The capability to check for. See Redirection_Capabilities for constants.
	 * @return boolean
	 */
	public static function has_access( $cap_name ) {
		// Get the capability using the default plugin access as the base. Old sites overriding `redirection_role` will get access to everything
		$cap_to_check = apply_filters( self::FILTER_CAPABILITY, self::get_plugin_access(), $cap_name );

		// Check the capability
		return current_user_can( $cap_to_check );
	}

	/**
	 * Return the role/capability used for displaying the plugin menu. This is also the base capability for all other checks.
	 *
	 * @return string Role/capability
	 */
	public static function get_plugin_access() {
		return apply_filters( self::CAP_PLUGIN, self::CAP_DEFAULT );
	}

	/**
	 * Return all the pages the user has access to.
	 *
	 * @return array Array of pages
	 */
	public static function get_available_pages() {
		$pages = [
			self::CAP_REDIRECT_MANAGE => 'redirect',
			self::CAP_GROUP_MANAGE => 'groups',
			self::CAP_404_MANAGE => '404s',
			self::CAP_LOG_MANAGE => 'log',
			self::CAP_IO_MANAGE => 'io',
			self::CAP_OPTION_MANAGE => 'options',
			self::CAP_SUPPORT_MANAGE => 'support',
			self::CAP_SITE_MANAGE => 'site',
		];

		$available = [];
		foreach ( $pages as $key => $page ) {
			if ( self::has_access( $key ) ) {
				$available[] = $page;
			}
		}

		return array_values( apply_filters( self::FILTER_PAGES, $available ) );
	}

	/**
	 * Return all the capabilities the current user has
	 *
	 * @return array Array of capabilities
	 */
	public static function get_all_capabilities() {
		$caps = self::get_every_capability();

		$caps = array_filter( $caps, function( $cap ) {
			return self::has_access( $cap );
		} );

		return array_values( apply_filters( self::FILTER_ALL, $caps ) );
	}

	/**
	 * Unfiltered list of all the supported capabilities, without influence from the current user
	 *
	 * @return array Array of capabilities
	 */
	public static function get_every_capability() {
		return [
			self::CAP_REDIRECT_MANAGE,
			self::CAP_REDIRECT_ADD,
			self::CAP_REDIRECT_DELETE,

			self::CAP_GROUP_MANAGE,
			self::CAP_GROUP_ADD,
			self::CAP_GROUP_DELETE,

			self::CAP_404_MANAGE,
			self::CAP_404_DELETE,

			self::CAP_LOG_MANAGE,
			self::CAP_LOG_DELETE,

			self::CAP_IO_MANAGE,

			self::CAP_OPTION_MANAGE,

			self::CAP_SUPPORT_MANAGE,

			self::CAP_SITE_MANAGE,
		];
	}
}
