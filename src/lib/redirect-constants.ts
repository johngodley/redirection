/**
 * Redirect action type constants
 */
export const ACTION_URL = 'url';
export const ACTION_ERROR = 'error';
export const ACTION_RANDOM = 'random';
export const ACTION_PASS = 'pass';
export const ACTION_NOTHING = 'nothing';

/**
 * Match type constants
 */
export const MATCH_URL = 'url';
export const MATCH_LOGIN = 'login';
export const MATCH_REFERRER = 'referrer';
export const MATCH_AGENT = 'agent';
export const MATCH_COOKIE = 'cookie';
export const MATCH_HEADER = 'header';
export const MATCH_CUSTOM = 'custom';
export const MATCH_ROLE = 'role';
export const MATCH_SERVER = 'server';
export const MATCH_IP = 'ip';
export const MATCH_PAGE = 'page';
export const MATCH_LANGUAGE = 'language';

/**
 * Check if an action type has a URL target
 * @param actionType
 */
export function hasUrlTarget( actionType: string ): boolean {
	return actionType === ACTION_URL || actionType === ACTION_RANDOM;
}

/**
 * Check if match data has a target
 * @param matchType
 * @param data
 */
export function hasTargetData( matchType: string, data: any ): boolean {
	if ( ! data ) {
		return false;
	}

	if ( matchType === MATCH_URL || matchType === MATCH_PAGE ) {
		return true;
	}

	if ( matchType === MATCH_LOGIN ) {
		return !! ( data.logged_in || data.logged_out );
	}

	if ( matchType === MATCH_REFERRER ) {
		return !! data.referrer;
	}

	if ( matchType === MATCH_AGENT ) {
		return !! data.agent;
	}

	if ( matchType === MATCH_COOKIE ) {
		return !! ( data.name && data.value );
	}

	if ( matchType === MATCH_HEADER ) {
		return !! ( data.name && data.value );
	}

	if ( matchType === MATCH_CUSTOM ) {
		return !! data.filter;
	}

	if ( matchType === MATCH_ROLE ) {
		return !! data.role;
	}

	if ( matchType === MATCH_SERVER ) {
		return !! data.server;
	}

	if ( matchType === MATCH_IP ) {
		return !! data.ip;
	}

	if ( matchType === MATCH_LANGUAGE ) {
		return !! data.language;
	}

	return false;
}

/**
 * Get match state data structure
 * @param matchType
 * @param data
 */
export function getMatchState( matchType: string, data: any ): any {
	if ( matchType === MATCH_LOGIN ) {
		return {
			logged_in: data?.logged_in || '',
			logged_out: data?.logged_out || '',
		};
	}

	if ( matchType === MATCH_REFERRER ) {
		return {
			referrer: data?.referrer || '',
			regex: data?.regex || false,
			url_from: data?.url_from || '',
			url_notfrom: data?.url_notfrom || '',
		};
	}

	if ( matchType === MATCH_AGENT ) {
		return {
			agent: data?.agent || '',
			regex: data?.regex || false,
			url_from: data?.url_from || '',
			url_notfrom: data?.url_notfrom || '',
		};
	}

	if ( matchType === MATCH_COOKIE ) {
		return {
			name: data?.name || '',
			value: data?.value || '',
			regex: data?.regex || false,
			url_from: data?.url_from || '',
			url_notfrom: data?.url_notfrom || '',
		};
	}

	if ( matchType === MATCH_HEADER ) {
		return {
			name: data?.name || '',
			value: data?.value || '',
			regex: data?.regex || false,
			url_from: data?.url_from || '',
			url_notfrom: data?.url_notfrom || '',
		};
	}

	if ( matchType === MATCH_CUSTOM ) {
		return {
			filter: data?.filter || '',
			url_from: data?.url_from || '',
			url_notfrom: data?.url_notfrom || '',
		};
	}

	if ( matchType === MATCH_ROLE ) {
		return {
			role: data?.role || '',
			url_from: data?.url_from || '',
			url_notfrom: data?.url_notfrom || '',
		};
	}

	if ( matchType === MATCH_SERVER ) {
		return {
			server: data?.server || '',
			url_from: data?.url_from || '',
			url_notfrom: data?.url_notfrom || '',
		};
	}

	if ( matchType === MATCH_IP ) {
		return {
			ip: data?.ip || '',
			url_from: data?.url_from || '',
			url_notfrom: data?.url_notfrom || '',
		};
	}

	if ( matchType === MATCH_LANGUAGE ) {
		return {
			language: data?.language || '',
			url_from: data?.url_from || '',
			url_notfrom: data?.url_notfrom || '',
		};
	}

	return data || {};
}

/**
 * Get default redirect item
 * @param url
 * @param groupId
 * @param flags
 */
export function getDefaultItem( url: string, groupId: number, flags: any ): any {
	return {
		url,
		title: '',
		match_data: {
			source: {
				flag_regex: flags.flag_regex || false,
				flag_trailing: flags.flag_trailing || false,
				flag_case: flags.flag_case || false,
				flag_query: flags.flag_query || 'exact',
			},
			options: {},
		},
		match_type: MATCH_URL,
		action_type: ACTION_URL,
		action_code: 301,
		action_data: {},
		group_id: groupId,
		position: 0,
	};
}

/**
 * Get default HTTP code for an action type
 * @param actionType
 */
export function getCodeForActionType( actionType: string ): number {
	if ( actionType === ACTION_ERROR ) {
		return 404;
	}

	if ( actionType === ACTION_PASS ) {
		return 200;
	}

	return 301;
}
