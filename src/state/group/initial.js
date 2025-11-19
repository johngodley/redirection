/**
 * Internal dependencies
 */

import { STATUS_IN_PROGRESS } from 'state/settings/type';
import { getDefaultTable, toFilter } from 'lib/table';
import { getModules } from 'state/io/selector';
import { getFilterOptions, getDisplayGroups } from 'page/groups/constants';

export function getInitialGroup() {
	return {
		rows: [],
		saving: [],
		total: 0,
		status: STATUS_IN_PROGRESS,
		table: getDefaultTable(
			[ 'name' ],
			toFilter( getFilterOptions( getModules() ), { name: true } ),
			[],
			'name',
			[ 'groups' ],
			'group',
			getDisplayGroups()
		),
	};
}
