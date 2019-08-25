/**
 * Internal dependencies
 */

import { STATUS_IN_PROGRESS } from 'state/settings/type';
import { getDefaultTable, toFilter } from 'lib/table';
import { getFilterOptions, getDisplayGroups } from 'page/redirects/constants';

export function getInitialRedirect() {
	const extra = {
		url: true,
		target: true,
		title: true,
		group: true,
	};

	return {
		rows: [],
		saving: [],
		total: 0,
		addTop: false,
		status: STATUS_IN_PROGRESS,
		table: getDefaultTable( [ 'url', 'position', 'last_count', 'id', 'last_access' ], toFilter( getFilterOptions(), extra ), [], 'id', [ '' ], 'redirect', getDisplayGroups() ),
	};
}
