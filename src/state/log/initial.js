/**
 * Internal dependencies
 */

import { STATUS_IN_PROGRESS } from '../../state/settings/type';
import { getDefaultTable, toFilter } from '../../lib/table';
import { getFilterOptions, getDisplayGroups } from '../../page/logs/constants';
import { getPageUrl } from '@wp-plugin-lib';

export function getInitialLog() {
	const extra = {
		url: true,
		ip: true,
		'url-exact': true,
		agent: true,
		referrer: true,
		target: true,
		domain: true,
	};
	const query = getPageUrl();
	let defaultOrder = 'date';

	if ( query.sub === '404s' && query.groupby ) {
		defaultOrder = 'total';
	}

	return {
		rows: [],
		saving: [],
		total: 0,
		status: STATUS_IN_PROGRESS,
		table: getDefaultTable( [ 'ip', 'url', 'total' ], toFilter( getFilterOptions(), extra ), [ 'url', 'ip', 'agent' ], defaultOrder, [ 'log' ], 'log', getDisplayGroups() ),
		requestCount: 0,
	};
}
