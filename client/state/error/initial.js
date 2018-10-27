/**
 * Internal dependencies
 */

import { STATUS_IN_PROGRESS } from 'state/settings/type';
import { getDefaultTable } from 'lib/table';
import { getPageUrl } from 'lib/wordpress-url';

export function getInitialError() {
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
		table: getDefaultTable( [ 'ip', 'url', 'total' ], [ 'ip', 'url-exact' ], [ 'url', 'ip' ], defaultOrder, [ '404s' ] ),
		requestCount: 0,
	};
}
