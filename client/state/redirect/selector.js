/**
 * Internal dependencies
 */

export const ACTION_URL = 'url';
export const ACTION_PASS = 'pass';
export const ACTION_ERROR = 'error';
export const ACTION_RANDOM = 'random';
export const ACTION_NOTHING = 'nothing';

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

export const CODE_PASS = 'pass';
export const CODE_NOTHING = 'nothing';

function getFromNotFrom( name, actionData, defaultValue = '' ) {
	const { url_from = '', url_notfrom = '' } = actionData;

	return {
		[ name ]: actionData[ name ] ? actionData[ name ] : defaultValue,
		url_from,
		url_notfrom,
	};
}

function getRegexFromNotFrom( name, actionData, defaultValue = '' ) {
	const { regex = false } = actionData;

	return {
		regex,
		... getFromNotFrom( name, actionData, defaultValue ),
	};
}

function getNameValueState( actionData ) {
	const { value = '' } = actionData;

	return {
		value,
		... getRegexFromNotFrom( 'name', actionData ),
	};
}

function getAgentState( actionData ) {
	return getRegexFromNotFrom( 'agent', actionData );
}

function getReferrerState( actionData ) {
	return getRegexFromNotFrom( 'referrer', actionData );
}

function getRoleState( actionData ) {
	return getFromNotFrom( 'role', actionData );
}

function getServerState( actionData ) {
	return getFromNotFrom( 'server', actionData );
}

function getLanguageState( actionData ) {
	return getFromNotFrom( 'language', actionData );
}

function getIpState( actionData ) {
	return getFromNotFrom( 'ip', actionData, [] );
}

function getPageState( actionData ) {
	const { page = '404', url = '' } = actionData;

	return {
		page,
		url,
	};
}

function getCustomState( actionData ) {
	return getFromNotFrom( 'filter', actionData );
}

function getUrlState( actionData ) {
	const { url = '' } = actionData;

	return {
		url,
	};
}

function getLoginState( actionData ) {
	const { logged_in = '', logged_out = '' } = actionData;

	return {
		logged_in,
		logged_out,
	};
}

const MATCH_MAP = {
	[ MATCH_URL ]: getUrlState,
	[ MATCH_LOGIN ]: getLoginState,
	[ MATCH_REFERRER ]: getReferrerState,
	[ MATCH_AGENT ]: getAgentState,
	[ MATCH_COOKIE ]: getNameValueState,
	[ MATCH_HEADER ]: getNameValueState,
	[ MATCH_CUSTOM ]: getCustomState,
	[ MATCH_ROLE ]: getRoleState,
	[ MATCH_SERVER ]: getServerState,
	[ MATCH_IP ]: getIpState,
	[ MATCH_PAGE ]: getPageState,
	[ MATCH_LANGUAGE ]: getLanguageState,
};

export const hasUrlTarget = type => type === ACTION_URL || type === ACTION_PASS;

export const getDefaultItem = ( url, group_id, source ) => ( {
	id: 0,
	url,
	match_type: MATCH_URL,
	action_type: ACTION_URL,
	action_data: {
		url: '',
	},
	group_id,
	title: '',
	action_code: 301,
	position: 0,
	match_data: { source },
} );

export function getMatchState( matchType, actionData ) {
	if ( MATCH_MAP[ matchType ] && actionData ) {
		return MATCH_MAP[ matchType ]( actionData );
	}

	return null;
}

export function hasTargetData( matchType, actionData ) {
	if ( matchType === MATCH_URL || matchType === MATCH_PAGE ) {
		return actionData.url !== '';
	}

	if ( matchType === MATCH_LOGIN ) {
		return actionData.logged_in !== '' || actionData.logged_out !== '';
	}

	return actionData.url_from !== '' || actionData.url_notfrom !== '';
}

export function getCodeForActionType( type ) {
	if ( type === ACTION_URL || type === ACTION_PASS ) {
		return 301;
	} else if ( type === ACTION_ERROR ) {
		return 404;
	}

	return 0;
}
