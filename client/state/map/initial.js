/**
 * Internal dependencies
 */

import { STATUS_IN_PROGRESS } from 'state/settings/type';

export function getInitialMap() {
	return {
		status: STATUS_IN_PROGRESS,
		maps: {},
		error: '',
	};
}
