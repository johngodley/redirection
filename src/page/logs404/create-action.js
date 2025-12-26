/**
 * Internal dependencies
 */

import { MATCH_IP, MATCH_AGENT, ACTION_URL, ACTION_ERROR, MATCH_URL, ACTION_NOTHING } from 'state/redirect/selector';

const MATCH_ALL = '^/.*$';

/**
 * Get an IP action
 * @param {string|string[]} ip
 */
function getIpAction( ip ) {
	return {
		url: MATCH_ALL,
		match_type: MATCH_IP,
		action_data: { ip },
		match_data: {
			source: { flag_regex: true },
		},
	};
}

/**
 * Get an object to create a redirect based on some parameters
 *
 * @param {string} action Action
 * @param {string|string[]} items Items to get create for
 */
export default function getCreateAction( action, items ) {
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

	return { url: items, match_type: MATCH_URL, action_type: action === 'ignore' ? ACTION_NOTHING : ACTION_URL };
}
