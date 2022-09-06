/**
 * Internal dependencies
 */

import { STATUS_COMPLETE } from '../settings/type';

export function getInitialInfo() {
	return {
		status: STATUS_COMPLETE,
		maps: {},
		agents: {},
		http: false,
		error: '',
	};
}
