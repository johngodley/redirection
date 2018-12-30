/**
 * Internal dependencies
 */

import { getOption } from 'state/settings/selector';

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

export const CODE_PASS = 'pass';
export const CODE_NOTHING = 'nothing';

export const hasUrlTarget = type => type === ACTION_URL || type === ACTION_PASS;

export const getActionData = state => {
	const { agent, referrer, login, match_type, target, action_type, header, cookie, custom, role, server, ip, page } = state;

	if ( match_type === MATCH_COOKIE ) {
		return {
			name: cookie.name,
			value: cookie.value,
			regex: cookie.regex,
			url_from: hasUrlTarget( action_type ) ? cookie.url_from : '',
			url_notfrom: hasUrlTarget( action_type ) ? cookie.url_notfrom : '',
		};
	}

	if ( match_type === MATCH_HEADER ) {
		return {
			name: header.name,
			value: header.value,
			regex: header.regex,
			url_from: hasUrlTarget( action_type ) ? header.url_from : '',
			url_notfrom: hasUrlTarget( action_type ) ? header.url_notfrom : '',
		};
	}

	if ( match_type === MATCH_CUSTOM ) {
		return {
			filter: custom.filter,
			url_from: hasUrlTarget( action_type ) ? custom.url_from : '',
			url_notfrom: hasUrlTarget( action_type ) ? custom.url_notfrom : '',
		};
	}

	if ( match_type === MATCH_AGENT ) {
		return {
			agent: agent.agent,
			regex: agent.regex,
			url_from: hasUrlTarget( action_type ) ? agent.url_from : '',
			url_notfrom: hasUrlTarget( action_type ) ? agent.url_notfrom : '',
		};
	}

	if ( match_type === MATCH_REFERRER ) {
		return {
			referrer: referrer.referrer,
			regex: referrer.regex,
			url_from: hasUrlTarget( action_type ) ? referrer.url_from : '',
			url_notfrom: hasUrlTarget( action_type ) ? referrer.url_notfrom : '',
		};
	}

	if ( match_type === MATCH_ROLE ) {
		return {
			role: role.role,
			url_from: hasUrlTarget( action_type ) ? role.url_from : '',
			url_notfrom: hasUrlTarget( action_type ) ? role.url_notfrom : '',
		};
	}

	if ( match_type === MATCH_SERVER ) {
		return {
			server: server.server,
			url_from: hasUrlTarget( action_type ) ? server.url_from : '',
			url_notfrom: hasUrlTarget( action_type ) ? server.url_notfrom : '',
		};
	}

	if ( match_type === MATCH_IP ) {
		return {
			ip: ip.ip,
			url_from: hasUrlTarget( action_type ) ? ip.url_from : '',
			url_notfrom: hasUrlTarget( action_type ) ? ip.url_notfrom : '',
		};
	}

	if ( match_type === MATCH_LOGIN && hasUrlTarget( action_type ) ) {
		return {
			logged_in: login.logged_in,
			logged_out: login.logged_out,
		};
	}

	if ( match_type === MATCH_URL && hasUrlTarget( action_type ) ) {
		return {
			url: target.url,
		};
	}

	if ( match_type === MATCH_PAGE && hasUrlTarget( action_type ) ) {
		return {
			page: page.page,
			url: hasUrlTarget( action_type ) ? page.url : '',
		};
	}

	return '';
};

export const getDefaultItem = ( url, group_id, source ) => ( {
	id: 0,
	url,
	regex: false,
	match_type: 'url',
	action_type: 'url',
	action_data: {
		url: '',
	},
	group_id,
	title: '',
	action_code: 301,
	match_data: { source },
} );
