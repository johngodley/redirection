/**
 * Internal dependencies
 */

import { STATUS_IN_PROGRESS } from 'state/settings/type';
import { getDefaultTable, toFilter } from 'lib/table';
import { getFilterOptions, getDisplayGroups } from 'page/logs404/constants';
import { getPageUrl } from 'lib/wordpress-url';

export function getInitialError() {
	const extra = {
		url: true,
		ip: true,
		referrer: true,
		agent: true,
		'url-exact': true,
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
		table: getDefaultTable( [ 'ip', 'url', 'total' ], toFilter( getFilterOptions(), extra ), [ 'url', 'ip', 'agent' ], defaultOrder, [ '404s' ], '404s', getDisplayGroups() ),
		requestCount: 0,
	};
}
