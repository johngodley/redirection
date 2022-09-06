/**
 * Internal dependencies
 */

import { STATUS_IN_PROGRESS } from '../settings/type';
import { getDefaultTable, toFilter } from '../../lib/table';
import { getModules } from '../io/selector';
import { getFilterOptions, getDisplayGroups } from '../../page/groups/constants';

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
