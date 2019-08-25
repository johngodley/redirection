/**
 * Internal dependencies
 */

import { STATUS_IN_PROGRESS } from 'state/settings/type';
import { getDefaultTable, toFilter } from 'lib/table';
import { getFilterOptions, getDisplayGroups } from 'page/logs/constants';

export function getInitialLog() {
	const extra = { url: true, ip: true, 'url-exact': true, agent: true, referrer: true, target: true };

	return {
		rows: [],
		saving: [],
		total: 0,
		status: STATUS_IN_PROGRESS,
		table: getDefaultTable( [ 'ip', 'url' ], toFilter( getFilterOptions(), extra ), [], 'date', [ 'log' ], 'log', getDisplayGroups() ),
		requestCount: 0,
	};
}
