import { MATCH_IP, MATCH_AGENT, ACTION_URL, ACTION_ERROR, MATCH_URL, ACTION_NOTHING } from 'lib/redirect-constants';

const MATCH_ALL = '^/.*$';

interface RedirectAction {
	url: string;
	match_type: string;
	action_data?: {
		ip?: string | string[];
		agent?: string | string[];
	};
	match_data?: {
		source?: {
			flag_regex?: boolean;
		};
	};
	action_type?: string;
	action_code?: number;
}

function getIpAction( ip: string | string[] ): RedirectAction {
	return {
		url: MATCH_ALL,
		match_type: MATCH_IP,
		action_data: { ip },
		match_data: {
			source: { flag_regex: true },
		},
	};
}

export default function getCreateAction( action: string, items: string | string[] ): RedirectAction {
	if ( action === 'redirect-ip' || action === 'ip' ) {
		return {
			...getIpAction( items ),
			action_type: ACTION_URL,
		};
	}

	if ( action === 'block' ) {
		return {
			...getIpAction( items ),
			action_type: ACTION_ERROR,
			action_code: 403,
		};
	}

	if ( action === 'agent' ) {
		return {
			url: MATCH_ALL,
			match_type: MATCH_AGENT,
			action_data: { agent: items },
			match_data: {
				source: { flag_regex: true },
			},
		};
	}

	return {
		url: items as string,
		match_type: MATCH_URL,
		action_type: action === 'ignore' ? ACTION_NOTHING : ACTION_URL,
	};
}
