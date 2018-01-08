/**
 * Internal dependencies
 */

import { STATUS_IN_PROGRESS } from 'state/settings/type';

export function getInitialInfo() {
	return {
		status: STATUS_IN_PROGRESS,
		maps: {},
		agents: {},
		error: '',
	};
}
