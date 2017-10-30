export const ACTION_URL = 'url';
export const ACTION_PASS = 'pass';
export const ACTION_ERROR = 'error';
export const ACTION_RANDOM = 'random';
export const ACTION_NOTHING = 'nothing';

export const MATCH_URL = 'url';
export const MATCH_LOGIN = 'login';
export const MATCH_REFERRER = 'referrer';
export const MATCH_AGENT = 'agent';

export const hasUrlTarget = type => type === ACTION_URL || type === ACTION_PASS;

export const getActionData = state => {
	const { agent, referrer, login, match_type, target, action_type } = state;

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

	return '';
};

export const getDefaultItem = ( url, group_id ) => ( {
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
} );
